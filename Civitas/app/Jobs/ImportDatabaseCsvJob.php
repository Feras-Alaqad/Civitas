<?php

namespace App\Jobs;

use App\Services\CitizensCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportDatabaseCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;

    private const FIRST_BATCH_SIZE = 10000;
    private const DB_CHUNK_SIZE = 500;
    private const MAX_RECORDS = 4000000;

    private array $cityIds = [];
    private array $governorateIds = [];
    private array $nationalityIds = [];

    public function __construct()
    {
    }

    public function handle(): void
    {
        $path = base_path('database.csv');

        if (!file_exists($path)) {
            return;
        }

        $this->cityIds = DB::table('Cities')->pluck('CityID')->toArray();
        $this->governorateIds = DB::table('Governorates')->pluck('GovernorateID')->toArray();
        $this->nationalityIds = DB::table('Nationalities')->pluck('NationalityID')->toArray();

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            fclose($handle);
            return;
        }

        $headers = array_map('trim', $headers);

        $batch = [];
        $rowIndex = 0;

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($rowIndex >= self::MAX_RECORDS) {
                break;
            }

            $record = array_combine($headers, $line);
            $batch[] = $this->buildRow($record);
            $rowIndex++;

            if (count($batch) >= self::DB_CHUNK_SIZE) {
                $this->flushBatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->flushBatch($batch);
        }

        fclose($handle);

        $this->clearCitizensCache();
    }

    private function buildRow(array $record): array
    {
        $fullName = trim(
            ($record['FirstName'] ?? '') . ' ' .
            ($record['FatherName'] ?? '') . ' ' .
            ($record['FamilyName'] ?? '')
        );

        return [
            'PersonID'       => (string) Str::uuid(),
            'FullName'       => $fullName ?: '',
            'FullNameSearch' => \App\Models\Person::normalizeName($fullName),
            'DateOfBirth'    => null,
            'NationalID'     => $record['ID'] ?? null,
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

    private function flushBatch(array &$batch): void
    {
        DB::table('Persons')->insertOrIgnore($batch);
        $batch = [];
    }

    private function clearCitizensCache(): void
    {
        CitizensCacheService::flushCitizensCache();
        \App\Http\Controllers\Admin\DashboardController::clearCache();
    }
}
