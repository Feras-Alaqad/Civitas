<?php

namespace App\Console\Commands;

use App\Jobs\ImportDatabaseCsvJob;
use App\Services\DatabaseCsvImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-database-csv {--path= : Path to the CSV file (default: project root database.csv)} {--sync : Run synchronously with a progress bar instead of dispatching to the queue}')]
#[Description('Import database.csv into the Persons table with random CityID/GovernorateID assignment')]
class ImportDatabaseCsv extends Command
{
    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('database.csv');

        if (!file_exists($path)) {
            $this->error("CSV file not found: {$path}");
            return self::FAILURE;
        }

        if ($this->option('sync')) {
            return $this->importSync($path);
        }

        $this->info('Dispatching ImportDatabaseCsvJob...');
        ImportDatabaseCsvJob::dispatch();
        $this->info('Job dispatched to the queue. Track progress with: tail -f /tmp/import_database.log');
        return self::SUCCESS;
    }

    private function importSync(string $path): int
    {
        $importer = new DatabaseCsvImporter();
        $bar = null;
        $lastLog = microtime(true);

        $this->info("Importing {$path} synchronously...");

        $count = $importer->import($path, function ($processed, $total) use (&$bar, &$lastLog) {
            if ($bar === null) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }

            $bar->setProgress($processed);

            if (microtime(true) - $lastLog > 10) {
                $this->newLine();
                $this->line("{$processed}/{$total} rows inserted");
                $lastLog = microtime(true);
            }
        });

        $bar?->finish();
        $this->newLine(2);
        $this->info("Done. Imported {$count} persons.");

        return self::SUCCESS;
    }
}
