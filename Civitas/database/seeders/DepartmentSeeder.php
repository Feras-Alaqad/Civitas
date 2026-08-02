<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['DepartmentName' => 'قسم الجوازات', 'Description' => 'إدارة شؤون جوازات السفر وتأشيرات السفر'],
            ['DepartmentName' => 'قسم المالية', 'Description' => 'إدارة الشؤون المالية والميزانية'],
            ['DepartmentName' => 'الشؤون القانونية', 'Description' => 'إدارة المسائل القانونية والاستشارات'],
        ];

        foreach ($departments as $dept) {
            DB::table('Departments')->insert([
                'DepartmentID' => Str::uuid(),
                'DepartmentName' => $dept['DepartmentName'],
                'Description' => $dept['Description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Departments seeded successfully!');
    }
}
