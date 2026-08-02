<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;
use Meilisearch\Client;

class ImportMeilisearch extends Command
{
    protected $signature = 'meilisearch:import {--chunk=100000 : Batch size} {--wait=0 : Wait ms between batches} {--offset=0 : Start from this PersonID offset}';
    protected $description = 'Import all Person records into Meilisearch index in batches';

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
        $bar = $this->output->createProgressBar($remaining);
        $bar->start();

        $query = Person::query()->orderBy('PersonID');
        if ($offset !== '') {
            $query->where('PersonID', '>', $offset);
        }

        $query->chunk($chunkSize, function ($people) use ($index, &$sent, $bar, $remaining) {
            $docs = $people->map(fn($p) => $p->toSearchableArray())->toArray();

            $task = $index->addDocuments($docs);

            $sent += count($docs);
            $bar->setProgress($sent);
        });

        $bar->finish();
        $this->newLine(2);

        $time = round((microtime(true) - $start), 1);
        $this->info("Done. {$sent} documents imported from offset {$offset} in {$time}s");

        $stats = $index->stats();
        $this->info("Index documents: {$stats['numberOfDocuments']}");

        return self::SUCCESS;
    }
}
