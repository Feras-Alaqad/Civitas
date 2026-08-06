<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;

class ImportMeilisearch extends Command
{
    protected $signature = 'meilisearch:import {--chunk=10000 : Batch size} {--wait=0 : Wait ms between batches} {--offset=0 : Start from this PersonID offset}';
    protected $description = 'Import all Person records into Meilisearch index in batches, isolating any record Meilisearch rejects';

    public function handle(): int
    {
        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );

        $index = $client->index('persons_index');

        $chunkSize = max(1, (int) $this->option('chunk'));
        $offset = trim((string) $this->option('offset'));
        $total = Person::count();
        $remaining = $total;

        if ($remaining <= 0) {
            $this->info("Nothing to import. Total: {$total}, Offset: {$offset}");
            return self::SUCCESS;
        }

        $batches = (int) ceil($remaining / $chunkSize);

        $this->info("Persons: {$total} | Offset: {$offset} | Remaining: {$remaining} | Chunk: {$chunkSize} | Batches: {$batches}");
        $this->newLine();

        $start = microtime(true);
        $sent = 0;
        $skipped = [];
        $bar = $this->output->createProgressBar($remaining);
        $bar->start();

        $query = Person::query()->orderBy('PersonID');
        if ($offset !== '') {
            $query->where('PersonID', '>', $offset);
        }

        $query->chunk($chunkSize, function ($people) use ($index, &$sent, &$skipped, $bar, $remaining) {
            $docs = $people->map(fn($p) => $p->toSearchableArray())->toArray();

            try {
                $index->addDocuments($docs);
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
            }

            $bar->advance(count($docs));
        });

        $bar->finish();
        $this->newLine(2);

        $time = round((microtime(true) - $start), 1);
        $this->info("Done. {$sent} documents imported from offset {$offset} in {$time}s");

        if (!empty($skipped)) {
            $this->warn("Skipped " . count($skipped) . " bad records: " . implode(', ', array_slice($skipped, 0, 20)));
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

        while (!empty($stack)) {
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
     * @param  array<string, mixed>  $doc
     */
    private function printRecordDiagnostic(array $doc): void
    {
        foreach ($doc as $key => $value) {
            $value = (string) $value;
            $valid = mb_check_encoding($value, 'UTF-8') ? 'ok' : 'INVALID';
            $preview = mb_substr($value, 0, 60);
            $hex = bin2hex(substr($value, 0, 20));
            $this->line("  {$key}: bytes=" . strlen($value) . " utf8={$valid} => " . json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->line("    hex: {$hex}");
        }
    }
}
