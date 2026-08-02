<?php

namespace App\Jobs;

use App\Models\Person;
use App\Services\CitizensCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ImportPersonsCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;

    public $tries = 1;

    private const CHUNK_SIZE = 500;

    public function __construct(public string $importId)
    {
    }

    public function handle(): void
    {
        $storagePath = storage_path("app/imports/{$this->importId}.csv");

        if (!file_exists($storagePath)) {
            $this->setStatus('error', 0, 0);
            return;
        }

        $handle = fopen($storagePath, 'r');

        if (!$handle) {
            $this->setStatus('error', 0, 0);
            return;
        }

        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            fclose($handle);
            $this->setStatus('error', 0, 0);
            return;
        }

        $headers = array_map('trim', $headers);

        $total = $this->countDataLines($handle);
        rewind($handle);
        fgetcsv($handle);

        Cache::put("import.{$this->importId}", [
            'percent' => 0,
            'processed' => 0,
            'total' => $total,
            'status' => 'processing',
        ], 3600);

        $batch = [];
        $batchPersonIds = [];
        $processed = 0;

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (count($headers) !== count($line)) {
                continue;
            }

            $record = array_combine($headers, $line);
            $row = $this->buildRow($record);

            $batch[] = $row;
            $batchPersonIds[] = $row['PersonID'];

            if (count($batch) >= self::CHUNK_SIZE) {
                $this->flushBatch($batch, $batchPersonIds);
                $batch = [];
                $batchPersonIds = [];
                $processed += self::CHUNK_SIZE;
                $this->updateProgress($processed, $total);
            }
        }

        if (!empty($batch)) {
            $this->flushBatch($batch, $batchPersonIds);
            $processed += count($batch);
        }

        fclose($handle);

        $this->finishImport($processed, $total);
    }

    private function countDataLines($handle): int
    {
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            if (trim($line) !== '') {
                $count++;
            }
        }

        return $count;
    }

    private function buildRow(array $record): array
    {
        $fullName = trim(
            ($record['FirstName'] ?? '') . ' ' .
            ($record['FatherName'] ?? '') . ' ' .
            ($record['MotherName'] ?? '') . ' ' .
            ($record['FamilyName'] ?? '')
        );

        if ($fullName === '') {
            $fullName = trim((string) ($record['FullName'] ?? ''));
        }

        return [
            'PersonID'       => trim((string) ($record['PersonID'] ?? '')) ?: (string) Str::uuid(),
            'FullName'       => $fullName,
            'FullNameSearch' => Person::normalizeName($fullName),
            'CityID'         => $record['CityID'] ?? null,
            'GovernorateID'  => $record['GovernorateID'] ?? null,
            'NationalityID'  => $record['NationalityID'] ?? null,
            'Phone'          => $record['Phone'] ?? null,
            'Email'          => $record['Email'] ?? null,
            'DateOfBirth'    => $record['DateOfBirth'] ?? null,
            'NationalID'     => $record['NationalID'] ?? null,
            'Address'        => $record['Address'] ?? null,
            'Gender'         => $record['Gender'] ?? null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    private function flushBatch(array $batch, array $personIds): void
    {
        DB::table('Persons')->insertOrIgnore($batch);

        if ($personIds && config('scout.driver') === 'meilisearch') {
            Person::whereIn('PersonID', $personIds)->searchable();
        }
    }

    private function updateProgress(int $processed, int $total): void
    {
        $percent = $total > 0 ? (int) round(($processed / $total) * 100) : 100;

        Cache::put("import.{$this->importId}", [
            'percent' => $percent,
            'processed' => $processed,
            'total' => $total,
            'status' => 'processing',
        ], 3600);
    }

    private function finishImport(int $processed, int $total): void
    {
        Cache::put("import.{$this->importId}", [
            'percent' => 100,
            'processed' => $processed,
            'total' => $total,
            'status' => 'completed',
        ], 3600);

        CitizensCacheService::flushCitizensCache();
        Cache::forever('citizens:total_count', DB::table('Persons')->count());
        \App\Http\Controllers\Admin\DashboardController::clearCache();
    }

    private function setStatus(string $status, int $processed, int $total): void
    {
        Cache::put("import.{$this->importId}", [
            'percent' => $status === 'completed' ? 100 : 0,
            'processed' => $processed,
            'total' => $total,
            'status' => $status,
        ], 3600);
    }

    public function failed(Throwable $exception): void
    {
        $this->setStatus('error', 0, 0);
    }
}
