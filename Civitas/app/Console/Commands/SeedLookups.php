<?php

namespace App\Console\Commands;

use Database\Seeders\DepartmentSeeder;
use Database\Seeders\NationalitySeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('lookups:seed {--backfill : Assign a random nationality to persons with a null NationalityID}')]
#[Description('Seed departments, Arabic service types, and nationalities')]
class SeedLookups extends Command
{
    public function handle(): int
    {
        if (DB::table('Departments')->exists()) {
            $this->warn('Departments already seeded. Skipping.');
        } else {
            $this->call('db:seed', ['--class' => DepartmentSeeder::class, '--force' => true]);
        }

        if (DB::table('Service_Types')->exists()) {
            $this->warn('Service_Types already seeded. Skipping.');
        } else {
            $this->call('db:seed', ['--class' => ServiceSeeder::class, '--force' => true]);
        }

        if (DB::table('Nationalities')->exists()) {
            $this->warn('Nationalities already seeded. Skipping.');
        } else {
            $this->call('db:seed', ['--class' => NationalitySeeder::class, '--force' => true]);
        }

        if ($this->option('backfill')) {
            $this->backfillNationalities();
        }

        return self::SUCCESS;
    }

    private function backfillNationalities(): void
    {
        $nationalityIds = DB::table('Nationalities')->pluck('NationalityID')->toArray();
        $count = count($nationalityIds);

        if ($count === 0) {
            $this->warn('No nationalities to assign. Skipping backfill.');
            return;
        }

        $quotedIds = array_map(fn ($id) => "'" . addslashes($id) . "'", $nationalityIds);
        $idList = implode(',', $quotedIds);

        $affected = DB::statement("UPDATE Persons SET NationalityID = ELT(1 + FLOOR(RAND() * {$count}), {$idList}) WHERE NationalityID IS NULL");

        $this->info("Assigned random nationalities to persons with null NationalityID.");
    }
}
