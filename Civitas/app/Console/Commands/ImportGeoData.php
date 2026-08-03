<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('geo:import {--fresh}')]
#[Description('Import Palestinian governorates and cities from CSV files into the database')]
class ImportGeoData extends Command
{
    public function handle(): int
    {
        $base = database_path('data');

        $governoratesFile = "{$base}/governorates.csv";
        $citiesFile = "{$base}/cities.csv";

        if (!file_exists($governoratesFile) || !file_exists($citiesFile)) {
            $this->error("CSV files not found in {$base}. Expected governorates.csv and cities.csv.");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->clearExistingData();
        }

        $existingCount = DB::table('Governorates')->count();
        if ($existingCount > 0) {
            $this->warn("Governorates table already has {$existingCount} rows. Use --fresh to replace them.");
            return self::FAILURE;
        }

        $govMap = [];
        $governorates = $this->readCsv($governoratesFile);

        DB::transaction(function () use ($governorates, $citiesFile, &$govMap) {
            foreach ($governorates as $row) {
                $uuid = (string) Str::uuid();
                DB::table('Governorates')->insert([
                    'GovernorateID' => $uuid,
                    'GovernorateName' => trim((string) $row['GovernorateName']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $govMap[$row['GovernorateID']] = $uuid;
            }

            $cities = $this->readCsv($citiesFile);
            foreach ($cities as $row) {
                DB::table('Cities')->insert([
                    'CityID' => (string) Str::uuid(),
                    'CityName' => trim((string) $row['CityName']),
                    'GovernorateID' => $govMap[$row['GovernorateID']] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("Imported " . count($governorates) . " governorates and " . count($cities) . " cities.");
        });

        return self::SUCCESS;
    }

    private function clearExistingData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('Persons')->update(['CityID' => null, 'GovernorateID' => null]);
        DB::table('Cities')->truncate();
        DB::table('Governorates')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->warn('Cleared existing governorates, cities, and unlinked persons.');
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if (!$handle) {
            return $rows;
        }

        $headers = fgetcsv($handle, 0, ',', '"', '');

        if ($headers) {
            $headers = array_map(fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)), $headers);

            while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($line) === count($headers)) {
                    $rows[] = array_combine($headers, $line);
                }
            }
        }

        fclose($handle);

        return $rows;
    }
}
