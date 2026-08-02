<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-database-csv')]
#[Description('Import database.csv into the Persons table')]
class ImportDatabaseCsv extends Command
{
    public function handle()
    {
        $this->info('Dispatching ImportDatabaseCsvJob...');
        \App\Jobs\ImportDatabaseCsvJob::dispatch();
        $this->info('Job dispatched to the queue.');
    }
}
