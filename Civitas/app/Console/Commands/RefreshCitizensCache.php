<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\DashboardController;
use App\Services\CitizensCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefreshCitizensCache extends Command
{
    protected $signature = 'citizens:refresh-cache';

    protected $description = 'Refresh citizens cache when new records are added to the database';

    public function handle(CitizensCacheService $cacheService): int
    {
        $dbCount = DB::table('Persons')->count();
        $cachedCount = (int) Cache::get('citizens:total_count', 0);

        if ($dbCount === $cachedCount) {
            return self::SUCCESS;
        }

        CitizensCacheService::flushCitizensCache();

        Cache::forever('citizens:total_count', $dbCount);
        $cacheService->warmListPage('', 0);
        $cacheService->warmListPage('', CitizensCacheService::PER_PAGE);

        DashboardController::clearCache();

        $this->info("Citizens cache refreshed: {$cachedCount} -> {$dbCount}");

        return self::SUCCESS;
    }
}
