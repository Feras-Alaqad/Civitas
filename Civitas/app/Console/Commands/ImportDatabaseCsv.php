<?php

namespace App\Console\Commands;

use App\Jobs\ImportDatabaseCsvJob;
use App\Services\DatabaseCsvImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-database-csv {--path=* : Path to a CSV file (repeatable, comma-separated values allowed)} {--all-parts : Also include all database.csv.NN part files found in the project root} {--sync : Run synchronously with a progress bar instead of dispatching to the queue} {--limit= : Maximum number of records to import} {--truncate : Delete all existing persons before importing}')]
#[Description('Import database.csv files into the Persons table. CityID/GovernorateID/NationalityID are resolved from the CSV source_code values; unmatched codes become NULL (never random).')]
class ImportDatabaseCsv extends Command
{
    public function handle(): int
    {
        $paths = $this->resolvePaths();

        if (empty($paths)) {
            $this->error('No CSV files provided. Pass --path=file.csv (repeatable) or use --all-parts.');
            return self::FAILURE;
        }

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                $this->error("CSV file not found: {$path}");
                return self::FAILURE;
            }
        }

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $truncate = (bool) $this->option('truncate');

        if ($this->option('sync')) {
            return $this->importSync($paths, $limit, $truncate);
        }

        $this->info('Dispatching ImportDatabaseCsvJob...');
        ImportDatabaseCsvJob::dispatch($paths, $limit, $truncate);
        $this->info('Job dispatched to the queue. Track progress with: tail -f /tmp/import_database.log');
        return self::SUCCESS;
    }

    private function importSync(array $paths, ?int $limit, bool $truncate): int
    {
        $importer = new DatabaseCsvImporter();

        if ($truncate) {
            $importer->truncatePersons();
            $this->warn('Truncated all existing persons.');
        }

        $bar = null;
        $lastLog = microtime(true);

        $this->info('Importing ' . count($paths) . ' file(s) synchronously...');

        $count = $importer->importFiles($paths, function ($processed, $total, $fileIndex, $fileCount, $path) use (&$bar, &$lastLog) {
            if ($bar === null) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }

            $bar->setProgress($processed);

            if (microtime(true) - $lastLog > 10) {
                $this->newLine();
                $this->line("{$processed}/{$total} rows inserted [{$path}]");
                $lastLog = microtime(true);
            }
        }, $limit);

        $bar?->finish();
        $this->newLine(2);
        $this->info("Done. Imported {$count} persons.");

        return self::SUCCESS;
    }

    private function resolvePaths(): array
    {
        $paths = $this->option('path') ?: [];
        $paths = collect($paths)
            ->flatMap(fn ($value) => explode(',', (string) $value))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        if ($this->option('all-parts')) {
            $parts = glob(base_path('database.csv.*')) ?: [];
            sort($parts);

            if (!empty($parts)) {
                $paths = array_values(array_unique(array_merge($paths, $parts)));
            }
        }

        if (empty($paths)) {
            $default = base_path('database.csv');

            if (file_exists($default)) {
                $paths = [$default];
            }
        }

        return $paths;
    }
}