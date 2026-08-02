<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            'دمشق',
            'حلب',
            'حمص',
            'اللاذقية',
            'طرطوس',
            'حماه',
            'إدلب',
            'الرقة',
            'دير الزور',
            'الحسكة',
            'درعا',
            'السويداء',
            'القنيطرة',
            'ريف دمشق',
        ];

        foreach ($governorates as $name) {
            DB::table('Governorates')->insert([
                'GovernorateID' => Str::uuid(),
                'GovernorateName' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Governorates seeded successfully!');
    }
}
