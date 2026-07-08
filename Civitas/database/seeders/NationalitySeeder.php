<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('../nationalities.csv');

        if (! file_exists($csvPath)) {
            $this->command->error('nationalities.csv not found!');
            return;
        }

        $rows = array_map('str_getcsv', file($csvPath));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (count($row) < 2) continue;

            DB::table('Nationalities')->insert([
                'NationalityID' => Str::uuid(),
                'NationalityName' => trim($row[1]),
            ]);
        }

        $this->command->info('Nationalities seeded successfully!');
    }
}
