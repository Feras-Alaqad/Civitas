<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $departmentIds = DB::table('Departments')->pluck('DepartmentID')->toArray();
        if (empty($departmentIds)) return;

        $serviceTypes = [
            ['name' => 'New Passport', 'fees' => 15000, 'dept_idx' => 0, 'docs' => 'National ID Copy, Personal Photo, Birth Certificate'],
            ['name' => 'Passport Renewal', 'fees' => 10000, 'dept_idx' => 0, 'docs' => 'Old Passport, National ID Copy, Personal Photo'],
            ['name' => 'Lost Passport Replacement', 'fees' => 25000, 'dept_idx' => 0, 'docs' => 'Police Report, National ID Copy, Personal Photo, Affidavit'],
            ['name' => 'Exit Visa', 'fees' => 5000, 'dept_idx' => 0, 'docs' => 'Passport Copy, Sponsor Letter, National ID Copy'],
            ['name' => 'Service Fee Payment', 'fees' => 2000, 'dept_idx' => 1, 'docs' => 'Payment Receipt, National ID Copy'],
            ['name' => 'Late Penalty', 'fees' => 5000, 'dept_idx' => 1, 'docs' => 'National ID Copy, Original Document'],
            ['name' => 'Financial Settlement', 'fees' => 30000, 'dept_idx' => 1, 'docs' => 'Financial Statement, National ID Copy, Bank Letter'],
            ['name' => 'Legal Consultation', 'fees' => 10000, 'dept_idx' => 2, 'docs' => 'Case Summary, National ID Copy, Power of Attorney'],
            ['name' => 'Contract Notarization', 'fees' => 15000, 'dept_idx' => 2, 'docs' => 'Original Contract, National ID Copies of All Parties'],
            ['name' => 'Court Case Filing', 'fees' => 50000, 'dept_idx' => 2, 'docs' => 'Court Documents, National ID Copy, Evidence Files, Power of Attorney'],
        ];

        DB::table('Service_Types')->delete();

        foreach ($serviceTypes as $st) {
            DB::table('Service_Types')->insert([
                'ServiceTypeID' => Str::uuid(),
                'ServiceName' => $st['name'],
                'DepartmentID' => $departmentIds[$st['dept_idx']],
                'Fees' => $st['fees'],
                'RequiredDocuments' => $st['docs'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Service types seeded successfully!');
    }
}
