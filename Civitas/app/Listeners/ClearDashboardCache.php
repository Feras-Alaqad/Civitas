<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Http\Controllers\Admin\DashboardController;
use App\Services\CitizensCacheService;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache
{
    public function handle(PaymentCompleted $event): void
    {
        DashboardController::clearCache();

        $requestId = $event->payment->RequestID;
        $sr = \App\Models\ServiceRequest::where('RequestID', $requestId)->first();
        if ($sr) {
            $cacheService = app(CitizensCacheService::class);
            Cache::forget($cacheService->buildRequestsCacheKey($sr->PersonID));
        }
    }
}
