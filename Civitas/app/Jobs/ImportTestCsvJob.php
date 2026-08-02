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

class ImportTestCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $path = base_path('test.csv');

        if (!file_exists($path)) {
            return;
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '');

        if (!$headers) {
            fclose($handle);
            return;
        }

        $headers = array_map('trim', $headers);
        $rows = [];

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rows[] = array_combine($headers, $line);
        }

        fclose($handle);

        $batchSize = 50;
        $batches = array_chunk($rows, $batchSize);

        foreach ($batches as $batch) {
            $inserts = [];

            foreach ($batch as $row) {
                $fullName = trim(
                    ($row['FirstName'] ?? '') . ' ' .
                    ($row['FatherName'] ?? '') . ' ' .
                    ($row['MotherName'] ?? '') . ' ' .
                    ($row['FamilyName'] ?? '')
                );

                $inserts[] = [
                    'PersonID' => (string) Str::uuid(),
                    'FullName' => $fullName ?: '',
                    'Phone' => $row['PhoneNumber'] ?? null,
                    'Email' => $row['Email'] ?? null,
                    'NationalID' => $row['ID'] ?? null,
                    'DateOfBirth' => null,
                    'Address' => null,
                    'Gender' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($inserts)) {
                DB::table('Persons')->insertOrIgnore($inserts);
            }
        }

        $this->clearCitizensCache();
    }

    private function clearCitizensCache(): void
    {
        CitizensCacheService::flushCitizensCache();
    }
}
