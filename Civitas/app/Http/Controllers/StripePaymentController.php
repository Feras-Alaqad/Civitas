<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\StripeWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    /**
     * Create (or reuse) a Stripe PaymentIntent for a given service request.
     *
     * The amount is always recomputed server-side from the service fees and is never
     * trusted from the client, preventing tampering.
     */
    public function createIntent(Request $request)
    {
        $request->validate([
            'request_id' => 'required|string|exists:Service_Requests,RequestID',
        ]);

        $serviceRequest = ServiceRequest::with('serviceType')->findOrFail($request->input('request_id'));

        // Authorization: only the user who created the request (or an admin) may pay for it.
        if ($serviceRequest->UserID !== Auth::id()) {
            abort(403, 'You are not authorized to pay for this service request.');
        }

        // An existing pending intent for this request is reused instead of creating duplicates.
        $existing = Payment::where('RequestID', $serviceRequest->RequestID)
            ->where('Status', 'pending')
            ->whereNotNull('StripePaymentIntentID')
            ->first();

        if ($existing) {
            try {
                $intent = $this->client()->paymentIntents->retrieve($existing->StripePaymentIntentID);

                if ($intent->status === 'requires_payment_method' || $intent->status === 'requires_confirmation') {
                    return response()->json(['client_secret' => $intent->client_secret]);
                }
            } catch (ApiErrorException $e) {
                Log::warning('Stripe: failed to retrieve existing PaymentIntent', [
                    'intent_id' => $existing->StripePaymentIntentID,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $amountMinor = (int) round((float) $serviceRequest->serviceType->Fees * 100);
        $currency = strtolower(config('services.stripe.currency', 'usd'));

        try {
            $intent = $this->client()->paymentIntents->create([
                'amount' => $amountMinor,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'request_id' => $serviceRequest->RequestID,
                    'person_id' => $serviceRequest->PersonID,
                ],
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe: PaymentIntent creation failed', [
                'request_id' => $serviceRequest->RequestID,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not initiate the payment. Please try again.',
            ], 500);
        }

        $payment = Payment::create([
            'RequestID' => $serviceRequest->RequestID,
            'Amount' => $serviceRequest->serviceType->Fees,
            'PaymentDate' => now(),
            'ReceiptNumber' => null,
            'StripePaymentIntentID' => $intent->id,
            'Currency' => strtoupper($currency),
            'Status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'client_secret' => $intent->client_secret,
        ]);
    }

    /**
     * Receive and verify Stripe webhook events.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        Log::info('Stripe: webhook received', [
            'type' => $request->input('type'),
            'event_id' => $request->input('id'),
        ]);

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe: webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe: invalid webhook payload', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        }

        // Idempotency: ignore events we already processed.
        if (StripeWebhookEvent::where('EventID', $event->id)->exists()) {
            Log::info('Stripe: duplicate webhook event ignored', ['event_id' => $event->id]);

            return response('OK', 200);
        }

        DB::beginTransaction();

        try {
            StripeWebhookEvent::create([
                'EventID' => $event->id,
                'EventType' => $event->type,
            ]);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event);
                    break;

                default:
                    Log::info('Stripe: unhandled event type', ['type' => $event->type]);
                    break;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Stripe: webhook processing failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response('Internal error', 500);
        }

        return response('OK', 200);
    }

    protected function handlePaymentSucceeded($event)
    {
        $intent = $event->data->object;
        $payment = Payment::where('StripePaymentIntentID', $intent->id)->first();

        if (!$payment || $payment->Status === 'succeeded') {
            Log::info('Stripe: no matching pending payment for succeeded intent', [
                'intent_id' => $intent->id,
            ]);

            return;
        }

        $payment->update([
            'Status' => 'succeeded',
            'PaidAt' => now(),
            'ReceiptNumber' => $payment->ReceiptNumber ?: 'RCPT-' . strtoupper(Str::random(10)),
            'Currency' => strtoupper($intent->currency),
        ]);

        // Heavy async work (updating the service request, audit log, cache) is deferred to a queue job.
        dispatch(new \App\Jobs\FinalizeSuccessfulPayment($payment->PaymentID));
    }

    protected function handlePaymentFailed($event)
    {
        $intent = $event->data->object;
        $payment = Payment::where('StripePaymentIntentID', $intent->id)->first();

        if (!$payment) {
            Log::info('Stripe: no matching payment for failed intent', ['intent_id' => $intent->id]);

            return;
        }

        $payment->update([
            'Status' => 'failed',
            'FailureReason' => $event->data->object->last_payment_error->message
                ?? $event->data->object->last_payment_error->code
                ?? null,
        ]);
    }

    protected function client(): StripeClient
    {
        return app(StripeClient::class);
    }
}
