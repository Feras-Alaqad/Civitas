<?php

namespace App\Console\Commands;

use App\Models\Person;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;

class ImportMeilisearch extends Command
{
    protected $signature = 'meilisearch:import '
        .'{--chunk=10000 : Batch size} '
        .'{--wait=0 : Wait ms between batches} '
        .'{--offset=0 : Start from this PersonID offset} '
        .'{--resume : Continue from last saved progress} '
        .'{--reset : Clear saved progress and start from the beginning} '
        .'{--timeout=120 : HTTP timeout in seconds per batch}';

    protected $description = 'Import all Person records into Meilisearch index in batches, resumable, isolating any record Meilisearch rejects';

    private const STATE_FILE = 'meilisearch-import.state';

    private const STATE_KEY = 'meilisearch:import:state';

    public function handle(): int
    {
        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key'),
            new GuzzleHttpClient([
                'timeout' => max(1, (int) $this->option('timeout')),
                'connect_timeout' => 30,
            ])
        );

        $index = $client->index('persons_index');
        $statePath = storage_path('app/'.self::STATE_FILE);

        if ($this->option('reset')) {
            @unlink($statePath);
            Cache::forget(self::STATE_KEY);
            $this->info('Saved progress cleared.');
        }

        $lastPersonId = null;
        if ($this->option('resume')) {
            $state = $this->loadProgress($statePath);
            $lastPersonId = $state['lastPersonId'] ?? null;
            if ($lastPersonId) {
                $this->info("Resuming from PersonID: {$lastPersonId}");
            } else {
                $this->warn('No saved progress found. Starting from the beginning.');
            }
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $waitMs = max(0, (int) $this->option('wait'));

        $query = Person::query()->orderBy('PersonID');

        $offset = trim((string) $this->option('offset'));
        if ($offset !== '') {
            $query->where('PersonID', '>', $offset);
        } elseif ($lastPersonId !== null) {
            $query->where('PersonID', '>', $lastPersonId);
        }

        $total = (clone $query)->count();
        if ($total <= 0) {
            $this->info('Nothing to import (after filter).');

            return self::SUCCESS;
        }

        $batches = (int) ceil($total / $chunkSize);
        $this->info("Remaining: {$total} | Chunk: {$chunkSize} | Batches: {$batches}");
        $this->newLine();

        $start = microtime(true);
        $sent = 0;
        $skipped = [];
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk($chunkSize, function ($people) use ($index, &$sent, &$skipped, $bar, $waitMs, $statePath) {
            $docs = $people->map(fn ($p) => $p->toSearchableArray())->toArray();
            $lastInChunk = (string) $people->last()->PersonID;

            try {
                $this->sendWithRetry($index, $docs);
                $sent += count($docs);
            } catch (ApiException $e) {
                $this->warn("Batch rejected ({$e->getMessage()}), isolating bad records...");
                $bad = $this->isolateBadRecords($docs, $index);

                foreach ($bad as $badDoc) {
                    $skipped[] = $badDoc['PersonID'] ?? '(unknown)';
                    $this->error('Skipping bad record:');
                    $this->printRecordDiagnostic($badDoc);
                }

                $sent += count($docs) - count($bad);
            } catch (\Throwable $e) {
                $this->error("Batch ending at {$lastInChunk} failed after retries: {$e->getMessage()}");
                $this->warn('Progress kept at the last successful batch. Re-run with --resume to continue from there.');

                return false;
            }

            $this->saveProgress($statePath, $lastInChunk);
            $bar->advance(count($docs));

            if ($waitMs > 0) {
                usleep($waitMs * 1000);
            }
        });

        $bar->finish();
        $this->newLine(2);

        $time = round((microtime(true) - $start), 1);
        $this->info("Done. {$sent} documents imported in {$time}s");

        if (! empty($skipped)) {
            $this->warn('Skipped '.count($skipped).' bad records: '.implode(', ', array_slice($skipped, 0, 20)));
        }

        $stats = $index->stats();
        $this->info("Index documents: {$stats['numberOfDocuments']}");

        return self::SUCCESS;
    }

    /**
     * Recursively split a rejected batch until the individual bad records are found.
     *
     * @param  array<int, array<string, mixed>>  $docs
     * @return array<int, array<string, mixed>>
     */
    private function isolateBadRecords(array $docs, $index): array
    {
        $stack = [$docs];
        $bad = [];

        while (! empty($stack)) {
            $slice = array_pop($stack);

            if (count($slice) === 1) {
                $bad[] = $slice[0];

                continue;
            }

            try {
                $index->addDocuments($slice);

                continue;
            } catch (ApiException) {
                $mid = intdiv(count($slice), 2);
                array_push(
                    $stack,
                    array_slice($slice, 0, $mid),
                    array_slice($slice, $mid)
                );
            }
        }

        return $bad;
    }

    /**
     * Send a batch to Meilisearch, retrying transient network/HTTP failures.
     *
     * @param  array<int, array<string, mixed>>  $docs
     */
    private function sendWithRetry($index, array $docs, int $retries = 4): void
    {
        $attempt = 0;

        while (true) {
            try {
                $index->addDocuments($docs);

                return;
            } catch (\Throwable $e) {
                if ($e instanceof ApiException) {
                    throw $e;
                }

                $attempt++;
                if ($attempt > $retries) {
                    throw $e;
                }

                $this->warn("Transient error (attempt {$attempt}/{$retries}): {$e->getMessage()} — retrying in 5s...");
                sleep(5);
            }
        }
    }

    private function saveProgress(string $statePath, string $lastPersonId): void
    {
        $state = [
            'lastPersonId' => $lastPersonId,
            'updated_at' => now()->toIso8601String(),
        ];

        try {
            Cache::forever(self::STATE_KEY, $state);
        } catch (\Throwable) {
            // Cache unavailable (e.g. Redis down); the file fallback still applies.
        }

        file_put_contents($statePath, json_encode($state, JSON_THROW_ON_ERROR));
    }

    private function loadProgress(string $statePath): ?array
    {
        $state = Cache::get(self::STATE_KEY);

        if (is_array($state)) {
            return $state;
        }

        if (file_exists($statePath)) {
            $decoded = json_decode((string) file_get_contents($statePath), true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    private function printRecordDiagnostic(array $doc): void
    {
        foreach ($doc as $key => $value) {
            $value = (string) $value;
            $valid = mb_check_encoding($value, 'UTF-8') ? 'ok' : 'INVALID';
            $preview = mb_substr($value, 0, 60);
            $hex = bin2hex(substr($value, 0, 20));
            $this->line("  {$key}: bytes=".strlen($value)." utf8={$valid} => ".json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->line("    hex: {$hex}");
        }
    }
}
