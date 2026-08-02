<?php

namespace App\Console\Commands;

use App\Services\CitizensCacheService;
use Illuminate\Console\Command;

class WarmCitizensCache extends Command
{
    protected $signature = 'cache:warm-citizens {--pages=100 : Number of listing pages to warm} {--search= : Comma-separated search terms to warm} {--search-pages=5 : Number of pages per search term}';

    protected $description = 'Warm citizens cache: listing pages + Meilisearch search pages';

    public function handle(CitizensCacheService $cacheService): int
    {
        $pagesToWarm = (int) $this->option('pages');
        $searchTerms = $this->option('search') ? explode(',', $this->option('search')) : [];
        $searchPages = (int) $this->option('search-pages');

        $start = microtime(true);

        $this->info('=== Warming Citizens Cache ===');
        $this->newLine();

        $this->info('[1/3] Warming total count...');
        $cacheService->getCachedTotalCount();
        $this->line('  Done.');

        $this->info("[2/3] Warming listing pages ({$pagesToWarm} pages)...");
        for ($page = 0; $page < $pagesToWarm; $page++) {
            $offset = $page * CitizensCacheService::PER_PAGE;
            $cacheService->warmListPage('', $offset);
            $this->line("  Page " . ($page + 1) . "/{$pagesToWarm} (offset: {$offset})");
        }

        if (!empty($searchTerms)) {
            $this->info("[3/3] Warming Meilisearch search pages ({$searchPages} pages per term)...");
            foreach ($searchTerms as $term) {
                $term = trim($term);
                $this->line("  Search: \"{$term}\"");
                for ($page = 1; $page <= $searchPages; $page++) {
                    $cacheService->warmMeilisearchPage($term, $page);
                    $this->line("    Page {$page}/{$searchPages}");
                }
            }
        } else {
            $this->info('[3/3] No search terms to warm (use --search=term1,term2)');
        }

        $elapsed = round((microtime(true) - $start) * 1000, 2);
        $this->newLine();
        $this->info("Cache warming completed in {$elapsed}ms.");

        return self::SUCCESS;
    }
}
