<?php

namespace App\Console\Commands;

use App\Jobs\ImportPersonsCsvJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('imports:resume-stalled')]
#[Description('Resume CSV imports that have been stalled without a queued job running')]
class ResumeStalledImports extends Command
{
    public function handle(): int
    {
        $ids = Cache::get('imports:active_ids', []);

        if (empty($ids)) {
            return self::SUCCESS;
        }

        $now = now()->timestamp;
        $stalled = [];

        foreach ($ids as $id) {
            $state = Cache::get("import.{$id}");

            if (!$state || ($state['status'] ?? '') !== 'processing') {
                continue;
            }

            $updatedAt = (int) ($state['updated_at'] ?? 0);

            if ($now - $updatedAt >= 180) {
                $stalled[] = $id;
            }
        }

        foreach ($stalled as $id) {
            $state = Cache::get("import.{$id}");
            $total = (int) ($state['total'] ?? 0);
            $processed = (int) ($state['processed'] ?? 0);

            if ($processed < $total) {
                ImportPersonsCsvJob::dispatch($id, (int) ($state['offset'] ?? 0), $processed);
            }
        }

        return self::SUCCESS;
    }
}
