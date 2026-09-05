<?php

namespace App\Http\Controllers;

use App\Jobs\FinalizeSuccessfulPayment;
use App\Models\NowPaymentsWebhookEvent;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NowPaymentsController extends Controller
{
    /**
     * Initialize a NOWPayments invoice for an existing service request.
     *
     * The amount is always recomputed server-side from the service fees and is never
     * trusted from the client, preventing tampering.
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'request_id' => 'required|string|exists:Service_Requests,RequestID',
        ]);

        $serviceRequest = ServiceRequest::with('serviceType')->findOrFail($request->input('request_id'));

        if ($serviceRequest->UserID !== Auth::id()) {
            abort(403, 'You are not authorized to pay for this service request.');
        }

        // Drop any stale pending NOWPayments attempt for this request.
        Payment::where('RequestID', $serviceRequest->RequestID)
            ->where('Status', 'pending')
            ->whereNotNull('NowPaymentsPaymentID')
            ->delete();

        $amount = (float) $serviceRequest->serviceType->Fees;
        $currency = strtoupper(config('services.stripe.currency', 'USD'));
        $orderId = (string) $serviceRequest->RequestID;

        $payload = [
            'price_amount' => $amount,
            'price_currency' => strtolower($currency),
            'order_id' => $orderId,
            'order_description' => 'Payment for: '.$serviceRequest->serviceType->ServiceName,
            'ipn_callback_url' => config('nowpayments.ipn_callback_url') ?: route('webhooks.nowpayments'),
            'success_url' => route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]),
            'cancel_url' => route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]),
        ];

        // Optional: let the customer choose the crypto on the NOWPayments page.
        // Only send pay_currency if explicitly configured (must be lowercase,
        // e.g. usdterc20 / usdttrc20 / btc / eth). "USDT" alone is rejected.
        $payCurrency = config('nowpayments.default_pay_currency');

        if ($payCurrency) {
            $payload['pay_currency'] = strtolower($payCurrency);
        }

        try {
            $response = $this->createInvoice($payload);
        } catch (\Throwable $e) {
            Log::error('NOWPayments: invoice creation failed', [
                'request_id' => $serviceRequest->RequestID,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not initiate the payment. Please try again.',
            ], 500);
        }

        $paymentId = (string) data_get($response, 'id');
        $invoiceUrl = data_get($response, 'invoice_url');

        if (! $paymentId || ! $invoiceUrl) {
            Log::error('NOWPayments: invoice created without payment_id or invoice_url', [
                'request_id' => $serviceRequest->RequestID,
                'response' => $response,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not initiate the payment. Please try again.',
            ], 502);
        }

        Payment::create([
            'RequestID' => $serviceRequest->RequestID,
            'Amount' => $serviceRequest->serviceType->Fees,
            'PaymentDate' => now(),
            'ReceiptNumber' => null,
            'NowPaymentsPaymentID' => $paymentId,
            'Currency' => $currency,
            'Status' => 'pending',
            'Metadata' => ['invoice_url' => $invoiceUrl],
        ]);

        return response()->json([
            'success' => true,
            'invoice_url' => $invoiceUrl,
            'payment_id' => $paymentId,
        ]);
    }

    /**
     * Resolve the payment gateway that a pending service request started with.
     */
    public static function gatewayFor(ServiceRequest $serviceRequest): ?string
    {
        $payment = Payment::where('RequestID', $serviceRequest->RequestID)
            ->where('Status', 'pending')
            ->first();

        if (! $payment) {
            return null;
        }

        if ($payment->StripePaymentIntentID) {
            return 'stripe';
        }

        if ($payment->LahzaReference) {
            return 'lahza';
        }

        if ($payment->NowPaymentsPaymentID) {
            return 'nowpayments';
        }

        return null;
    }

    /**
     * Manually verify a NOWPayments transaction (e.g. from "Continue with Crypto" page).
     */
    public function verify(string $paymentId)
    {
        $payment = Payment::where('NowPaymentsPaymentID', $paymentId)->first();

        if (! $payment) {
            Log::warning('NOWPayments: verify called for unknown payment_id', ['payment_id' => $paymentId]);

            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        $serviceRequest = $payment->serviceRequest;

        if (! $serviceRequest || $serviceRequest->UserID !== Auth::id()) {
            abort(403, 'You are not authorized to view this payment.');
        }

        try {
            $data = $this->getPaymentStatus($paymentId);
        } catch (\Throwable $e) {
            Log::error('NOWPayments: verify API call failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not verify your payment. Please try again.',
            ], 500);
        }

        $status = data_get($data, 'payment_status');

        if (in_array($status, ['finished', 'confirmed']) && $payment->Status === 'pending') {
            $this->confirmSuccessfulPayment($payment);
        } elseif (in_array($status, ['failed', 'expired', 'refunded']) && $payment->Status === 'pending') {
            $payment->update([
                'Status' => 'failed',
                'FailureReason' => 'Payment '.$status.' according to NOWPayments.',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'payment_id' => $paymentId,
            'invoice_url' => $payment->Metadata['invoice_url'] ?? null,
            'is_expired' => in_array($status, ['failed', 'expired', 'refunded']),
        ]);
    }

    /**
     * Show the "Continue with Crypto" resume page.
     *
     * If the payment is still valid (not expired/failed), redirect the user to the
     * stored invoice_url. If expired, create a fresh invoice instead.
     */
    public function resumePage(string $requestId)
    {
        $serviceRequest = ServiceRequest::with('serviceType', 'person')->findOrFail($requestId);

        if ($serviceRequest->UserID !== Auth::id()) {
            abort(403, 'You are not authorized to pay for this service request.');
        }

        if (self::gatewayFor($serviceRequest) !== 'nowpayments') {
            return redirect()->route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]);
        }

        $payment = Payment::where('RequestID', $serviceRequest->RequestID)
            ->where('Status', 'pending')
            ->whereNotNull('NowPaymentsPaymentID')
            ->first();

        if (! $payment) {
            return redirect()->route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]);
        }

        // Check if the payment is still active by querying NOWPayments.
        try {
            $data = $this->getPaymentStatus($payment->NowPaymentsPaymentID);
            $status = data_get($data, 'payment_status');

            if (in_array($status, ['finished', 'confirmed'])) {
                $this->confirmSuccessfulPayment($payment);

                return redirect()->route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]);
            }

            if (in_array($status, ['failed', 'expired', 'refunded'])) {
                // Payment expired — redirect to payment page to start fresh.
                return redirect()->route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID])
                    ->with('error', 'Your previous crypto payment has expired. Please start a new payment.');
            }

            // Still waiting — redirect to the stored invoice URL.
            $invoiceUrl = data_get($data, 'invoice_url') ?? $payment->Metadata['invoice_url'] ?? null;

            if ($invoiceUrl) {
                return redirect()->away($invoiceUrl);
            }
        } catch (\Throwable $e) {
            Log::error('NOWPayments: resume page verification failed', [
                'payment_id' => $payment->NowPaymentsPaymentID,
                'error' => $e->getMessage(),
            ]);

            // Fall through to show the confirmation page.
        }

        return view('admin.continue-nowpayments', [
            'serviceRequest' => $serviceRequest,
            'serviceType' => $serviceRequest->serviceType,
            'amount' => (float) $serviceRequest->serviceType?->Fees,
            'payment' => $payment,
        ]);
    }

    /**
     * Receive and verify NOWPayments IPN (Instant Payment Notification) webhooks.
     *
     * Signature: HMAC-SHA512 of the sorted JSON payload, compared against the
     * x-nowpayments-sig header.
     */
    public function ipnWebhook(Request $request)
    {
        $payload = $request->getContent();

        Log::info('NOWPayments: IPN webhook received', [
            'payment_id' => $request->input('payment_id'),
            'status' => $request->input('payment_status'),
        ]);

        if (! $this->verifyIpnSignature($request)) {
            Log::warning('NOWPayments: IPN signature verification failed');

            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            Log::warning('NOWPayments: invalid IPN payload');

            return response('Invalid payload', 400);
        }

        $paymentId = (string) ($event['payment_id'] ?? '');
        $orderId = (string) ($event['order_id'] ?? '');
        $paymentStatus = (string) ($event['payment_status'] ?? '');

        if ($paymentId === '') {
            Log::warning('NOWPayments: IPN without payment_id');

            return response('Missing payment_id', 400);
        }

        // Idempotency: ignore events we already processed.
        if (NowPaymentsWebhookEvent::where('PaymentID', $paymentId)->exists()) {
            Log::info('NOWPayments: duplicate IPN event ignored', ['payment_id' => $paymentId]);

            return response('OK', 200);
        }

        DB::beginTransaction();

        try {
            NowPaymentsWebhookEvent::create([
                'PaymentID' => $paymentId,
                'OrderID' => $orderId,
                'Payload' => $event,
                'processed_at' => now(),
            ]);

            $payment = Payment::where('NowPaymentsPaymentID', $paymentId)->first();

            if (! $payment) {
                Log::warning('NOWPayments: IPN for unknown payment', ['payment_id' => $paymentId]);

                DB::commit();

                return response('OK', 200);
            }

            switch ($paymentStatus) {
                case 'finished':
                case 'confirmed':
                    $this->handleSuccessfulPayment($payment, $event);
                    break;

                case 'partially_paid':
                    $this->handlePartiallyPaid($payment, $event);
                    break;

                case 'failed':
                case 'expired':
                case 'refunded':
                    $this->handleFailedPayment($payment, $event);
                    break;

                default:
                    Log::info('NOWPayments: unhandled IPN status', [
                        'status' => $paymentStatus,
                        'payment_id' => $paymentId,
                    ]);
                    break;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('NOWPayments: IPN processing failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response('Internal error', 500);
        }

        return response('OK', 200);
    }

    // ─────────────────────────── Private Helpers ───────────────────────────

    /**
     * Verify the NOWPayments IPN signature (HMAC-SHA512 of sorted JSON).
     */
    protected function verifyIpnSignature(Request $request): bool
    {
        $signatureHeader = (string) $request->header('x-nowpayments-sig');

        if ($signatureHeader === '') {
            return false;
        }

        $payload = $request->getContent();
        $data = json_decode($payload, true);

        if (! is_array($data)) {
            return false;
        }

        // NOWPayments sorts the JSON payload keys alphabetically, then HMAC-SHA512
        // hashes the resulting JSON string with the IPN secret.
        $sortedJson = json_encode($this->sortArrayRecursive($data));

        $expectedHash = hash_hmac('sha512', $sortedJson, config('nowpayments.ipn_secret'));

        return hash_equals($expectedHash, $signatureHeader);
    }

    /**
     * Recursively sort an array by its keys (required by NOWPayments IPN spec).
     */
    protected function sortArrayRecursive(array $data): array
    {
        ksort($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sortArrayRecursive($value);
            }
        }

        return $data;
    }

    /**
     * Handle a successful NOWPayments payment (finished/confirmed).
     */
    protected function handleSuccessfulPayment(Payment $payment, array $event): void
    {
        if ($payment->Status === 'succeeded') {
            Log::info('NOWPayments: payment already succeeded', ['payment_id' => $payment->NowPaymentsPaymentID]);

            return;
        }

        // Defensive: verify amounts match (NOWPayments sends price_amount and actually_paid).
        $priceAmount = (float) ($event['price_amount'] ?? 0);
        $actuallyPaid = (float) ($event['actually_paid'] ?? 0);
        $expectedAmount = (float) $payment->Amount;

        if ($priceAmount > 0 && abs($priceAmount - $expectedAmount) > 0.01) {
            Log::error('NOWPayments: IPN amount mismatch', [
                'payment_id' => $payment->NowPaymentsPaymentID,
                'expected' => $expectedAmount,
                'price_amount' => $priceAmount,
            ]);

            return;
        }

        $paidCurrency = strtoupper((string) ($event['pay_currency'] ?? $payment->Currency));

        $this->confirmSuccessfulPayment($payment, $paidCurrency);
    }

    /**
     * Handle a partially_paid NOWPayments payment.
     */
    protected function handlePartiallyPaid(Payment $payment, array $event): void
    {
        if ($payment->Status !== 'pending') {
            return;
        }

        $payment->update([
            'Status' => 'partially_paid',
            'Metadata' => array_merge($payment->Metadata ?? [], [
                'actually_paid' => $event['actually_paid'] ?? null,
                'partially_paid_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::warning('NOWPayments: partially_paid detected', [
            'payment_id' => $payment->NowPaymentsPaymentID,
            'actually_paid' => $event['actually_paid'] ?? null,
            'price_amount' => $event['price_amount'] ?? null,
        ]);
    }

    /**
     * Handle a failed/expired/refunded NOWPayments payment.
     */
    protected function handleFailedPayment(Payment $payment, array $event): void
    {
        if (! in_array($payment->Status, ['pending', 'partially_paid'])) {
            return;
        }

        $payment->update([
            'Status' => 'failed',
            'FailureReason' => 'Payment '.$event['payment_status'].' according to NOWPayments.',
        ]);
    }

    /**
     * Shared confirmation path used by both the verify endpoint and the webhook.
     */
    protected function confirmSuccessfulPayment(Payment $payment, ?string $currency = null): void
    {
        $payment->update([
            'Status' => 'succeeded',
            'PaidAt' => now(),
            'ReceiptNumber' => $payment->ReceiptNumber ?: 'RCPT-'.strtoupper(Str::random(10)),
            'Currency' => strtoupper($currency ?: $payment->Currency),
        ]);

        dispatch(new FinalizeSuccessfulPayment($payment->PaymentID));
    }

    /**
     * Create a NOWPayments invoice via their API.
     *
     * NOWPayments authenticates with the `x-api-key` header, NOT Bearer auth.
     */
    protected function createInvoice(array $data): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('nowpayments.api_key'),
        ])
            ->acceptJson()
            ->asJson()
            ->post(rtrim(config('nowpayments.base_url'), '/').'/invoice', $data);

        if ($response->failed()) {
            throw new \RuntimeException('NOWPayments API error: '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Get the status of a NOWPayments payment.
     */
    protected function getPaymentStatus(string $paymentId): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('nowpayments.api_key'),
        ])
            ->acceptJson()
            ->get(rtrim(config('nowpayments.base_url'), '/').'/payment/'.$paymentId);

        if ($response->failed()) {
            throw new \RuntimeException('NOWPayments API error: '.$response->body());
        }

        return $response->json() ?? [];
    }
}
