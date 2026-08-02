<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:614400',
        ]);

        $file = $request->file('csv_file');
        $importId = (string) Str::uuid();

        $path = $file->storeAs('imports', $importId . '.csv');

        if (!$path) {
            return response()->json(['message' => 'Failed to store file.'], 500);
        }

        $this->beginProcessing($importId);

        return response()->json(['import_id' => $importId]);
    }

    public function progress($importId)
    {
        $data = Cache::get("import.{$importId}", [
            'percent' => 0,
            'processed' => 0,
            'total' => 0,
            'status' => 'processing',
        ]);

        return response()->json($data);
    }

    private function beginProcessing($importId)
    {
        $storagePath = storage_path("app/imports/{$importId}.csv");

        if (!file_exists($storagePath)) {
            Cache::put("import.{$importId}", [
                'percent' => 0,
                'processed' => 0,
                'total' => 0,
                'status' => 'error',
            ], 3600);
            return;
        }

        $handle = fopen($storagePath, 'r');
        $rows = [];
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return;
        }

        $headers = array_map('trim', $headers);

        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $line);
        }
        fclose($handle);

        $total = count($rows);
        $processed = 0;
        $batchSize = 50;
        $batches = array_chunk($rows, $batchSize);

        Cache::put("import.{$importId}", [
            'percent' => 0,
            'processed' => 0,
            'total' => $total,
            'status' => 'processing',
        ], 3600);

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
                    'PersonID' => $row['PersonID'] ?? (string) Str::uuid(),
                    'FullName' => $fullName ?: ($row['FullName'] ?? ''),
                    'CityID' => $row['CityID'] ?? null,
                    'GovernorateID' => $row['GovernorateID'] ?? null,
                    'NationalityID' => $row['NationalityID'] ?? null,
                    'Phone' => $row['Phone'] ?? null,
                    'Email' => $row['Email'] ?? null,
                    'DateOfBirth' => $row['DateOfBirth'] ?? null,
                    'NationalID' => $row['NationalID'] ?? null,
                    'Address' => $row['Address'] ?? null,
                    'Gender' => $row['Gender'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($inserts)) {
                DB::table('Persons')->insertOrIgnore($inserts);
            }

            $processed += count($batch);
            $percent = (int) round(($processed / $total) * 100);

            Cache::put("import.{$importId}", [
                'percent' => $percent,
                'processed' => $processed,
                'total' => $total,
                'status' => 'processing',
            ], 3600);
        }

        Cache::put("import.{$importId}", [
            'percent' => 100,
            'processed' => $processed,
            'total' => $total,
            'status' => 'completed',
        ], 3600);

        \App\Services\CitizensCacheService::flushCitizensCache();
    }
}
