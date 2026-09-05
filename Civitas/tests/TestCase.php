<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function createAdminUser(): User
    {
        $roleId = (string) Str::uuid();

        DB::table('roles')->insert([
            'RoleID'      => $roleId,
            'RoleName'    => 'Admin',
            'Permissions' => 'admin',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        Cache::forget('roles:admin_id');

        $user = User::factory()->create();
        $user->forceFill(['RoleID' => $roleId])->save();

        return $user;
    }
}