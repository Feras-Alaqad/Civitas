<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    
    public function run(): void
{
    $this->call([
        RoleSeeder::class,
        NationalitySeeder::class,
        DepartmentSeeder::class,
        GovernorateSeeder::class,
        CitySeeder::class,
        PersonSeeder::class,
        ServiceSeeder::class,
    ]);
}
}
