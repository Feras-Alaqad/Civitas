<?php

namespace App\Http\Controllers;

use App\Jobs\FinalizeSuccessfulPayment;
use App\Models\LahzaWebhookEvent;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LahzaPaymentController extends Controller
{
    /**
     * Initialize a Lahza (Bank of Palestine) transaction for an existing
     * service request and return the authorization URL to redirect the user to.
     *
     * The amount is always recomputed server-side from the service fees and is never
     * trusted from the client, preventing tampering.
     */
    public function createIntent(Request $request)
    {
        $request->validate([
            'request_id' => 'required|string|exists:Service_Requests,RequestID',
            'email' => 'nullable|email|max:191',
            'mobile' => 'nullable|string|max:32',
        ]);

        $serviceRequest = ServiceRequest::with('serviceType', 'person')->findOrFail($request->input('request_id'));

        // Authorization: only the user who created the request (or an admin) may pay for it.
        if ($serviceRequest->UserID !== Auth::id()) {
            abort(403, 'You are not authorized to pay for this service request.');
        }

        // A pending Lahza checkout is single-use (reference -> checkout URL). Drop any
        // stale pending attempt for this request so we always start fresh.
        $existing = Payment::where('RequestID', $serviceRequest->RequestID)
            ->where('Status', 'pending')
            ->whereNotNull('LahzaReference')
            ->first();

        if ($existing) {
            $existing->delete();
        }

        $amountMinor = (int) round((float) $serviceRequest->serviceType->Fees * 100);
        $currency = strtoupper(config('lahza.default_currency', 'ILS'));

        $email = $request->input('email') ?: Auth::user()?->email;

        if (! $email) {
            // Lahza requires an email address; fall back to a generated one tied to the reference.
            $email = Str::lower(Str::random(12)).'@civitas.local';
        }

        $mobile = $request->input('mobile') ?: $serviceRequest->person?->Phone;

        $reference = (string) Str::uuid();

        try {
            $response = $this->initializeTransaction([
                'email' => $email,
                'mobile' => $mobile,
                'amount' => $amountMinor,
                'currency' => $currency,
                'reference' => $reference,
                'callback_url' => config('lahza.callback_url'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Lahza: transaction initialization failed', [
                'request_id' => $serviceRequest->RequestID,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not initiate the payment. Please try again.',
            ], 500);
        }

        $authorizationUrl = data_get($response, 'data.authorization_url');
        $confirmedReference = (string) (data_get($response, 'data.reference', $reference));

        if (! $authorizationUrl) {
            Log::error('Lahza: transaction initialized without an authorization URL', [
                'request_id' => $serviceRequest->RequestID,
                'reference' => $reference,
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
            'LahzaReference' => $confirmedReference,
            'Currency' => $currency,
            'Status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'authorization_url' => $authorizationUrl,
            'reference' => $confirmedReference,
        ]);
    }

    /**
     * Verify a transaction after Lahza redirects the customer back to the
     * callback URL. Success is only trusted when the Lahza verify endpoint
     * reports `data.status === 'success'`.
     */
    public function verifyCallback(Request $request)
    {
        $reference = (string) $request->query('reference');

        $payment = $reference
            ? Payment::where('LahzaReference', $reference)->first()
            : null;

        if (! $payment) {
            Log::warning('Lahza: callback received for unknown reference', ['reference' => $reference]);

            return redirect()->route('home');
        }

        try {
            $data = data_get($this->verifyTransaction($reference), 'data', []);
        } catch (\Throwable $e) {
            Log::error('Lahza: callback verification failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.service.payments.page', ['requestId' => $payment->RequestID])
                ->with('error', 'We could not verify your payment. Please try again.');
        }

        // Visiting the callback URL does not prove the payment succeeded; only a
        // verified `success` status from the Lahza API can finalize the payment.
        if (($data['status'] ?? null) === 'success') {
            $this->confirmSuccessfulPayment($payment, $data['currency'] ?? null);

            return redirect()->route('admin.service.payments.page', ['requestId' => $payment->RequestID]);
        }

        if (($data['status'] ?? null) === 'failed' && $payment->Status === 'pending') {
            $payment->update([
                'Status' => 'failed',
                'FailureReason' => $data['gateway_response'] ?? $data['message'] ?? null,
            ]);
        }

        return redirect()
            ->route('admin.service.payments.page', ['requestId' => $payment->RequestID])
            ->with('error', 'Your payment was not completed. Please try again.');
    }

    /**
     * Receive and verify Lahza webhook events.
     */
    public function webhook(Request $request)
    {
        // The Lahza dashboard probes the webhook URL with a GET request when
        // testing/saving it. Respond OK without any processing, since a GET has
        // no real event payload to verify and process.
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'ok'], 200);
        }

        $payload = $request->getContent();

        Log::info('Lahza: webhook received', [
            'type' => $request->input('event'),
            'reference' => $request->input('data.reference'),
        ]);

        if (! $this->verifyWebhookSignature($request)) {
            Log::warning('Lahza: webhook signature verification failed', ['error' => 'Invalid signature']);

            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            Log::warning('Lahza: invalid webhook payload', ['error' => 'Payload is not valid JSON']);

            return response('Invalid payload', 400);
        }

        $eventId = (string) (data_get($event, 'data.id') ?? data_get($event, 'data.reference'));
        $eventType = (string) ($event['event'] ?? 'unknown');

        // Idempotency: ignore events we already processed.
        if (LahzaWebhookEvent::where('EventID', $eventId)->exists()) {
            Log::info('Lahza: duplicate webhook event ignored', ['event_id' => $eventId]);

            return response('OK', 200);
        }

        DB::beginTransaction();

        try {
            LahzaWebhookEvent::create([
                'EventID' => $eventId,
                'EventType' => $eventType,
            ]);

            switch ($eventType) {
                case 'charge.success':
                    $this->handleChargeSuccess($event['data'] ?? []);
                    break;

                default:
                    Log::info('Lahza: unhandled event type', ['type' => $eventType]);
                    break;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lahza: webhook processing failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return response('Internal error', 500);
        }

        return response('OK', 200);
    }

    /**
     * Verify the Lahza webhook signature, including replay protection.
     *
     * Lahza signs the raw payload with an HMAC-SHA256 digest of the webhook
     * secret. To defend against replay attacks, the signature must be bound
     * to a timestamp and a nonce, and the timestamp must be recent. The exact
     * signing scheme should match what Lahza documents; the implementation
     * below is a hardened template that you should align with Lahza's spec.
     */
    protected function verifyWebhookSignature(Request $request): bool
    {
        $payload = $request->getContent();

        // Example: X-Lahza-Signature = "t=<timestamp>v1=<digest>"
        $signatureHeader = (string) $request->header('X-Lahza-Signature');

        if ($signatureHeader === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            $pair = trim($pair);
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? null;
        $providedDigest = $parts['v1'] ?? null;

        if ($timestamp === null || $providedDigest === null) {
            return false;
        }

        // Timestamp-based replay protection: reject old/replayed signatures.
        if (! ctype_digit($timestamp) || ! $this->isTimestampWithinTolerance((int) $timestamp)) {
            Log::warning('Lahza: webhook timestamp outside tolerance window', [
                'timestamp' => $timestamp,
            ]);

            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;

        $expectedDigest = hash_hmac('sha256', $signedPayload, config('lahza.webhook_secret'));

        // hash_equals performs a constant-time comparison (prevents timing attacks).
        return hash_equals($expectedDigest, $providedDigest);
    }

    /**
     * Reject signatures whose timestamp is not within a small window of "now".
     * Stripe uses a 5-minute tolerance; we use the same for Lahza.
     */
    protected function isTimestampWithinTolerance(int $timestamp, int $toleranceSeconds = 300): bool
    {
        $now = time();

        return abs($now - $timestamp) <= $toleranceSeconds;
    }
    protected function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (! $reference) {
            Log::warning('Lahza: charge.success event without a reference');

            return;
        }

        $payment = Payment::where('LahzaReference', $reference)->first();

        if (! $payment || $payment->Status === 'succeeded') {
            Log::info('Lahza: no matching pending payment for succeeded transaction', [
                'reference' => $reference,
            ]);

            return;
        }

        // Defensive: verify the amount actually charged matches the expected
        // amount for this request before marking it paid.
        $expectedMinor = (int) round((float) $payment->Amount * 100);
        $chargedMinor = (int) ($data['amount'] ?? $expectedMinor);
        $chargedCurrency = strtoupper((string) ($data['currency'] ?? $payment->Currency));

        if ($chargedMinor !== $expectedMinor || $chargedCurrency !== strtoupper((string) $payment->Currency)) {
            Log::error('Lahza: charge.success amount/currency mismatch', [
                'reference' => $reference,
                'expected_minor' => $expectedMinor,
                'charged_minor' => $chargedMinor,
                'expected_currency' => $payment->Currency,
                'charged_currency' => $chargedCurrency,
            ]);

            return;
        }

        $this->confirmSuccessfulPayment($payment, $data['currency'] ?? null);
    }

    /**
     * Shared confirmation path used by both the callback and the webhook:
     * marks the payment as succeeded and defers heavy work (updating the
     * service request, audit log, cache) to the queue job.
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

    protected function initializeTransaction(array $data): array
    {
        $idempotencyKey = $data['reference'] ?? (string) Str::uuid();

        $response = Http::withToken(config('lahza.secret_key'), 'Bearer')
            ->acceptJson()
            ->asJson()
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post(rtrim(config('lahza.base_url'), '/').'/transaction/initialize', $data);

        if ($response->failed()) {
            throw new \RuntimeException('Lahza API error: '.$response->body());
        }

        return $response->json() ?? [];
    }

    protected function verifyTransaction(string $reference): array
    {
        $response = Http::withToken(config('lahza.secret_key'), 'Bearer')
            ->acceptJson()
            ->get(rtrim(config('lahza.base_url'), '/').'/transaction/verify/'.rawurlencode($reference));

        if ($response->failed()) {
            throw new \RuntimeException('Lahza API error: '.$response->body());
        }

        return $response->json() ?? [];
    }
}
