<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $governorates = DB::table('Governorates')->pluck('GovernorateID', 'GovernorateName');

        $cities = [
            'دمشق' => ['دمشق', 'المزة', 'باب شرقي', 'المهاجرين', 'القدم'],
            'حلب' => ['حلب', 'منبج', 'عفرين', 'الباب', 'جرابلس'],
            'حمص' => ['حمص', 'تدمر', 'المخرم', 'تلكلخ', 'القصير'],
            'اللاذقية' => ['اللاذقية', 'جبلة', 'الحفة', 'القرداحة'],
            'طرطوس' => ['طرطوس', 'بانياس', 'صافيتا', 'الشيخ بدر'],
            'حماه' => ['حماه', 'سلمية', 'مصياف', 'محردة'],
            'إدلب' => ['إدلب', 'معرة النعمان', 'جسر الشغور', 'حارم'],
            'الرقة' => ['الرقة', 'الطبقة', 'تل أبيض'],
            'دير الزور' => ['دير الزور', 'الميادين', 'البوكمال'],
            'الحسكة' => ['الحسكة', 'القامشلي', 'المالكية', 'رأس العين'],
            'درعا' => ['درعا', 'ازرع', 'نوى', 'الشيخ مسكين'],
            'السويداء' => ['السويداء', 'شهبا', 'صلخد'],
            'القنيطرة' => ['القنيطرة', 'فيق'],
            'ريف دمشق' => ['دوما', 'حرستا', 'قطنا', 'النبك', 'يبرود', 'زبداني'],
        ];

        foreach ($cities as $govName => $cityList) {
            $govId = $governorates[$govName] ?? null;
            if (!$govId) continue;

            foreach ($cityList as $cityName) {
                DB::table('Cities')->insert([
                    'CityID' => Str::uuid(),
                    'CityName' => $cityName,
                    'GovernorateID' => $govId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Cities seeded successfully!');
    }
}
