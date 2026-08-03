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

    public $timeout = 7200;

    public $tries = 1;

    public function handle(): void
    {
        $path = base_path('database.csv');

        $this->log("Starting import from {$path}");

        try {
            $lastLogged = 0;

            $count = (new DatabaseCsvImporter())->import($path, function ($processed, $total) use (&$lastLogged) {
                if ($processed - $lastLogged >= 50000) {
                    $this->log("{$processed}/{$total} rows inserted");
                    $lastLogged = $processed;
                }
            });

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
