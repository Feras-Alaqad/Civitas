<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $cityIds = DB::table('Cities')->pluck('CityID')->toArray();
        if (empty($cityIds)) return;

        $firstNames = [
            'أحمد', 'محمد', 'علي', 'خالد', 'عمر', 'حسن', 'حسين', 'محمود',
            'عبد الله', 'مصطفى', 'نور', 'سامر', 'فادي', 'بسام', 'غسان',
            'ليلى', 'سارة', 'مريم', 'فاطمة', 'ناديا', 'هند', 'رنا', 'سلوى',
            'منى', 'نوال', 'لينا', 'هدى', 'سميرة', 'صباح', 'وفاء',
        ];

        $lastNames = [
            'الأسعد', 'الخطيب', 'الحسن', 'الشامي', 'الصالح', 'العلي',
            'الدباغ', 'المصري', 'الحريري', 'الرفاعي', 'الحلبي', 'الدمشقي',
            'البيطار', 'خزعل', 'مقداد', 'نصور', 'سلطان', 'مراد',
        ];

        $genders = ['male', 'female'];

        $persons = [];
        for ($i = 0; $i < 200; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $gender = $genders[array_rand($genders)];

            $persons[] = [
                'PersonID' => Str::uuid(),
                'FullName' => $firstName . ' ' . $lastName,
                'Gender' => $gender,
                'CityID' => $cityIds[array_rand($cityIds)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($persons, 50) as $chunk) {
            DB::table('Persons')->insert($chunk);
        }

        $this->command->info('Persons seeded successfully!');
    }
}
