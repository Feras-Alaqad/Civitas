<?php

namespace App\Jobs;

use App\Events\PaymentCompleted;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Services\CitizensCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinalizeSuccessfulPayment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public string $paymentId) {}

    public function handle(): void
    {
        $payment = Payment::find($this->paymentId);

        if (!$payment || $payment->Status !== 'succeeded') {
            return;
        }

        $serviceRequest = $payment->serviceRequest;

        if (!$serviceRequest || $serviceRequest->Status === 'Completed') {
            return;
        }

        $serviceRequest->update(['Status' => 'Completed']);

        $cacheService = app(CitizensCacheService::class);
        Cache::forget($cacheService->buildRequestsCacheKey($serviceRequest->PersonID));
        Cache::forget($cacheService->buildPersonCacheKey($serviceRequest->PersonID));

        DB::transaction(function () use ($payment, $serviceRequest) {
            AuditLog::create([
                'UserID' => $serviceRequest->UserID,
                'ActionType' => 'Payment Completed',
                'Description' => "Payment of {$payment->Amount} {$payment->Currency} completed via Stripe for {$payment->RequestID}",
                'ReferenceID' => $serviceRequest->RequestID,
                'Timestamp' => now(),
                'IPAddress' => null,
            ]);
        });

        event(new PaymentCompleted($payment));
    }
}
