<?php

namespace App\Jobs;

use App\Services\DatabaseCsvImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ImportDatabaseCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 21600;

    public $tries = 3;

    public function __construct(public ?int $limit = null, public bool $truncate = false)
    {
    }

    public function handle(): void
    {
        $path = base_path('database.csv');

        if (!file_exists($path)) {
            $this->log("CSV file not found: {$path}");
            return;
        }

        $this->log("Starting import from {$path}");

        try {
            $importer = new DatabaseCsvImporter();

            if ($this->truncate) {
                $importer->truncatePersons();
                $this->log('Truncated all existing persons.');
            }

            $lastLogged = 0;

            $count = $importer->import($path, function ($processed, $total) use (&$lastLogged) {
                if ($processed - $lastLogged >= 50000) {
                    $this->log("{$processed}/{$total} rows inserted");
                    $lastLogged = $processed;
                }
            }, $this->limit);

            $this->log("Import finished. {$count} persons inserted.");
        } catch (Throwable $e) {
            $this->log("Import failed: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->log("Job failed: {$exception->getMessage()}");
    }

    private function log(string $message): void
    {
        @file_put_contents('/tmp/import_database.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
    }
}
