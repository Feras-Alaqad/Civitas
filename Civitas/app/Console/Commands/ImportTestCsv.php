<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-test-csv')]
#[Description('Import test.csv into the Persons table')]
class ImportTestCsv extends Command
{
    public function handle()
    {
        $this->info('Dispatching ImportTestCsvJob...');
        \App\Jobs\ImportTestCsvJob::dispatch();
        $this->info('Job dispatched to the queue.');
    }
}
