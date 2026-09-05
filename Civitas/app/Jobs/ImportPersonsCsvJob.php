<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\DashboardController;
use App\Services\CitizensCacheService;
use App\Services\PersonCsvMapper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportPersonsCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $tries = 3;

    private const INSERT_BATCH = 500;

    private const ROWS_PER_JOB = 10000;

    private const PROGRESS_TTL = 172800;

    private ?PersonCsvMapper $mapper = null;

    public function __construct(
        public string $importId,
        public int $offset = 0,
        public int $processed = 0,
    ) {
    }

    public function handle(): void
    {
        $storagePath = Storage::disk('local')->path("imports/{$this->importId}.csv");

        if (!file_exists($storagePath)) {
            $this->failImport("CSV file not found: {$storagePath}");
            return;
        }

        $state = $this->currentState();
        $startOffset = (int) ($state['offset'] ?? $this->offset);
        $baseProcessed = (int) ($state['processed'] ?? $this->processed);
        $total = (int) ($state['total'] ?? 0);

        $handle = fopen($storagePath, 'r');

        if (!$handle) {
            $this->failImport('Could not open CSV file for reading.');
            return;
        }

        $this->mapper ??= new PersonCsvMapper();

        $headers = $this->mapper->readHeaders($handle);

        if (!$headers) {
            fclose($handle);
            $this->failImport('Could not read CSV headers.');
            return;
        }

        $fileSize = filesize($storagePath) ?: 0;

        if ($total === 0) {
            $total = $this->countDataLines($storagePath);
        }

        if ($startOffset > 0) {
            fseek($handle, $startOffset);
        }

        $batch = [];
        $processedInChunk = 0;
        $read = 0;

        while ($read < self::ROWS_PER_JOB && ($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            $read++;

            if ($line === '') {
                continue;
            }

            $parsed = str_getcsv($line, ',', '"', '');

            if (count($parsed) !== count($headers)) {
                continue;
            }

            $batch[] = $this->mapper->buildRow(array_combine($headers, $parsed));
            $processedInChunk++;

            if (count($batch) >= self::INSERT_BATCH) {
                DB::table('Persons')->insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('Persons')->insertOrIgnore($batch);
        }

        $newOffset = ftell($handle);
        fclose($handle);

        $newProcessed = $baseProcessed + $processedInChunk;

        if ($read < self::ROWS_PER_JOB || $newOffset >= $fileSize) {
            $this->finalize($total, $newProcessed);
            return;
        }

        $this->saveProgress([
            'status'    => 'processing',
            'processed' => $newProcessed,
            'total'     => $total,
            'remaining' => max(0, $total - $newProcessed),
            'percent'   => $total > 0 ? (int) round(($newProcessed / $total) * 100) : 0,
            'offset'    => $newOffset,
        ]);

        ImportPersonsCsvJob::dispatch($this->importId, $newOffset, $newProcessed);
    }

    public function failed(Throwable $exception): void
    {
        $this->failImport('Import failed: ' . $exception->getMessage());
    }

    private function countDataLines(string $path): int
    {
        $count = 0;
        $handle = fopen($path, 'r');

        if (!$handle) {
            return 0;
        }

        while (fgets($handle) !== false) {
            $count++;
        }

        fclose($handle);

        return max(0, $count - 1);
    }

    private function currentState(): array
    {
        return Cache::get("import.{$this->importId}", []);
    }

    private function saveProgress(array $state): void
    {
        $state['updated_at'] = now()->timestamp;

        Cache::put("import.{$this->importId}", $state, self::PROGRESS_TTL);
    }

    private function finalize(int $total, int $processed): void
    {
        $this->saveProgress([
            'status'    => 'completed',
            'percent'   => 100,
            'processed' => $processed,
            'total'     => $total,
            'remaining' => 0,
            'offset'    => 0,
        ]);

        CitizensCacheService::flushCitizensCache();
        Cache::forever('citizens:total_count', DB::table('Persons')->count());
        DashboardController::clearCache();

        Storage::disk('local')->delete("imports/{$this->importId}.csv");

        $this->removeFromActiveList();
    }

    private function failImport(string $message): void
    {
        $this->saveProgress([
            'status'    => 'error',
            'percent'   => 0,
            'processed' => 0,
            'total'     => 0,
            'remaining' => 0,
            'offset'    => 0,
            'message'   => $message,
        ]);

        $this->removeFromActiveList();
    }

    private function removeFromActiveList(): void
    {
        $ids = Cache::get('imports:active_ids', []);
        $ids = array_values(array_diff($ids, [$this->importId]));

        if (empty($ids)) {
            Cache::forget('imports:active_ids');
            return;
        }

        Cache::forever('imports:active_ids', $ids);
    }
}
