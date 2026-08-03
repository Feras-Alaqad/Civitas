<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('Nationalities')->exists()) {
            $this->command->warn('Nationalities already seeded. Skipping.');
            return;
        }

        $csvPath = database_path('data/nationalities.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("nationalities.csv not found at: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '');

        if ($headers) {
            $headers = array_map(fn ($header) => trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)), $headers);

            while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($line) !== count($headers)) {
                    continue;
                }

                $record = array_combine($headers, $line);

                DB::table('Nationalities')->insert([
                    'NationalityID' => (string) Str::uuid(),
                    'NationalityName' => trim((string) ($record['NationalityName'] ?? '')),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        fclose($handle);

        $this->command->info('Nationalities seeded successfully!');
    }
}
