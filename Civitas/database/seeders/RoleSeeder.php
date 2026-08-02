<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['RoleName' => 'DataEntry', 'Permissions' => 'import,view_persons,create_service'],
            ['RoleName' => 'FinancialReviewer', 'Permissions' => 'view_reports,review_services,export_data'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['RoleName' => $role['RoleName']],
                array_merge($role, ['RoleID' => Str::uuid()])
            );
        }
    }
}
