<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('Service_Types')->exists()) {
            $this->command->warn('Service_Types already seeded. Skipping.');
            return;
        }

        $departmentIds = DB::table('Departments')->pluck('DepartmentID', 'DepartmentName');
        if ($departmentIds->isEmpty()) {
            $this->command->error('No departments found. Run DepartmentSeeder first.');
            return;
        }

        $serviceTypes = [
            ['name' => 'جواز سفر جديد', 'fees' => 15000, 'dept' => 'قسم الجوازات', 'docs' => 'صورة عن الهوية، صورة شخصية، شهادة ميلاد'],
            ['name' => 'تجديد جواز السفر', 'fees' => 10000, 'dept' => 'قسم الجوازات', 'docs' => 'الجواز القديم، صورة عن الهوية، صورة شخصية'],
            ['name' => 'بدل فاقد لجواز السفر', 'fees' => 25000, 'dept' => 'قسم الجوازات', 'docs' => 'محضر شرطة، صورة عن الهوية، صورة شخصية، إقرار'],
            ['name' => 'تأشيرة خروج', 'fees' => 5000, 'dept' => 'قسم الجوازات', 'docs' => 'صورة الجواز، كتاب الكفيل، صورة عن الهوية'],
            ['name' => 'دفع رسوم الخدمة', 'fees' => 2000, 'dept' => 'قسم المالية', 'docs' => 'إيصال الدفع، صورة عن الهوية'],
            ['name' => 'غرامة التأخير', 'fees' => 5000, 'dept' => 'قسم المالية', 'docs' => 'صورة عن الهوية، المستند الأصلي'],
            ['name' => 'تسوية مالية', 'fees' => 30000, 'dept' => 'قسم المالية', 'docs' => 'كشف حساب، صورة عن الهوية، كتاب من البنك'],
            ['name' => 'استشارة قانونية', 'fees' => 10000, 'dept' => 'الشؤون القانونية', 'docs' => 'ملخص القضية، صورة عن الهوية، توكيل'],
            ['name' => 'توثيق عقد', 'fees' => 15000, 'dept' => 'الشؤون القانونية', 'docs' => 'العقد الأصلي، صور هويات جميع الأطراف'],
            ['name' => 'رفع دعوى قضائية', 'fees' => 50000, 'dept' => 'الشؤون القانونية', 'docs' => 'مستندات المحكمة، صورة عن الهوية، ملف الأدلة، توكيل'],
        ];

        foreach ($serviceTypes as $st) {
            $deptId = $departmentIds[$st['dept']] ?? null;
            if (!$deptId) {
                continue;
            }

            DB::table('Service_Types')->insert([
                'ServiceTypeID' => (string) Str::uuid(),
                'ServiceName' => $st['name'],
                'DepartmentID' => $deptId,
                'Fees' => $st['fees'],
                'RequiredDocuments' => $st['docs'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Service types seeded successfully!');
    }
}
