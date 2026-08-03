<?php

namespace App\Services;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Person;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseCsvImporter
{
    private const CHUNK_SIZE = 500;
    private const MAX_RECORDS = 4000000;

    private array $cityIds = [];
    private array $governorateIds = [];
    private array $nationalityIds = [];

    public function import(string $path, ?callable $onProgress = null): int
    {
        if (!file_exists($path)) {
            throw new RuntimeException("CSV file not found: {$path}");
        }

        $this->loadRandomIds();

        $handle = fopen($path, 'r');
        $headers = $this->readHeaders($handle);

        if (!$headers) {
            fclose($handle);
            throw new RuntimeException("Could not read CSV headers from: {$path}");
        }

        $total = $this->countDataLines($path);
        $batch = [];
        $processed = 0;

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($processed >= self::MAX_RECORDS) {
                break;
            }

            if (count($line) !== count($headers)) {
                continue;
            }

            $batch[] = $this->buildRow(array_combine($headers, $line));
            $processed++;

            if (count($batch) >= self::CHUNK_SIZE) {
                DB::table('Persons')->insertOrIgnore($batch);
                $batch = [];

                if ($onProgress) {
                    $onProgress($processed, $total);
                }
            }
        }

        if (!empty($batch)) {
            DB::table('Persons')->insertOrIgnore($batch);
        }

        fclose($handle);

        if ($onProgress) {
            $onProgress($processed, $total);
        }

        $this->clearCaches();

        return $processed;
    }

    public function countDataLines(string $path): int
    {
        $count = 0;
        $handle = fopen($path, 'r');

        if (!$handle) {
            return 0;
        }

        while (($line = fgets($handle)) !== false) {
            if (trim($line) !== '') {
                $count++;
            }
        }

        fclose($handle);

        return max(0, $count - 1);
    }

    private function loadRandomIds(): void
    {
        $this->cityIds = DB::table('Cities')->pluck('CityID')->toArray();
        $this->governorateIds = DB::table('Governorates')->pluck('GovernorateID')->toArray();
        $this->nationalityIds = DB::table('Nationalities')->pluck('NationalityID')->toArray();
    }

    private function readHeaders($handle): ?array
    {
        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            return null;
        }

        return array_map(
            fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)),
            $headers
        );
    }

    private function buildRow(array $record): array
    {
        $fullName = trim(implode(' ', [
            $record['FirstName'] ?? '',
            $record['FatherName'] ?? '',
            $record['MotherName'] ?? '',
            $record['FamilyName'] ?? '',
        ]));

        return [
            'PersonID'       => (string) Str::uuid(),
            'FullName'       => $fullName ?: '',
            'FullNameSearch' => Person::normalizeName($fullName),
            'DateOfBirth'    => null,
            'NationalID'     => ($record['ID'] ?? '') !== '' ? $record['ID'] : null,
            'Address'        => null,
            'Gender'         => null,
            'NationalityID'  => $this->nationalityIds ? $this->nationalityIds[array_rand($this->nationalityIds)] : null,
            'CityID'         => $this->cityIds ? $this->cityIds[array_rand($this->cityIds)] : null,
            'GovernorateID'  => $this->governorateIds ? $this->governorateIds[array_rand($this->governorateIds)] : null,
            'Phone'          => $record['PhoneNumber'] ?? null,
            'Email'          => $record['Email'] ?? null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    private function clearCaches(): void
    {
        CitizensCacheService::flushCitizensCache();
        Cache::forget('citizens:total_count');
        DashboardController::clearCache();
    }
}
