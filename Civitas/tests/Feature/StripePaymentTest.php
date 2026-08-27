<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\WebhookSignature;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => 'sk_test_dummy_secret']);
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
        config(['services.stripe.currency' => 'usd']);
    }

    protected function makeServiceType(float $fees = 25.00): ServiceType
    {
        return ServiceType::create([
            'ServiceName' => 'Test Service',
            'Fees' => $fees,
            'RequiredDocuments' => null,
        ]);
    }

    protected function makeServiceRequest(User $user, ServiceType $type): ServiceRequest
    {
        return ServiceRequest::create([
            'PersonID' => null,
            'UserID' => $user->id,
            'ServiceTypeID' => $type->ServiceTypeID,
            'RequestDate' => now(),
            'Status' => 'Pending',
        ]);
    }

    protected function makePendingPayment(ServiceRequest $sr, string $intentId): Payment
    {
        return Payment::create([
            'RequestID' => $sr->RequestID,
            'Amount' => 25.00,
            'PaymentDate' => now(),
            'StripePaymentIntentID' => $intentId,
            'Currency' => 'USD',
            'Status' => 'pending',
        ]);
    }

    protected function stubStripeClient(PaymentIntent $intent): void
    {
        $intentService = \Mockery::mock();
        $intentService->shouldReceive('create')->once()->andReturn($intent);

        $client = \Mockery::mock(StripeClient::class);
        $client->shouldReceive('getService')->with('paymentIntents')->andReturn($intentService);

        $this->app->instance(StripeClient::class, $client);
    }

    protected function buildWebhookEvent(string $type, array $object, ?string $eventId = null): array
    {
        $eventId = $eventId ?: 'evt_' . Str::uuid();

        $payload = json_encode([
            'id' => $eventId,
            'object' => 'event',
            'type' => $type,
            'data' => [
                'object' => array_merge([
                    'id' => $object['id'],
                    'object' => 'payment_intent',
                ], $object),
            ],
        ]);

        $signature = WebhookSignature::generateSignatureHeader(
            $payload,
            config('services.stripe.webhook_secret')
        );

        return [
            'id' => $eventId,
            'payload' => $payload,
            'signature' => $signature,
        ];
    }

    protected function postRaw(string $uri, string $body, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->call('POST', $uri, [], [], [], $this->transformHeadersToServerVars($headers), $body);
    }

    /*
     * ---------------------------------------------------------------------
     * PaymentIntent creation
     * ---------------------------------------------------------------------
     */

    public function test_create_intent_returns_client_secret_and_creates_pending_payment(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);

        $intent = new PaymentIntent('pi_test_123');
        $intent->client_secret = 'pi_test_123_secret_xyz';
        $intent->status = 'requires_payment_method';

        $this->stubStripeClient($intent);

        $this->actingAs($user)
            ->postJson(route('admin.service.payments.create-intent'), ['request_id' => $request->RequestID])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'client_secret' => 'pi_test_123_secret_xyz',
            ]);

        $this->assertDatabaseHas('Payments', [
            'RequestID' => $request->RequestID,
            'StripePaymentIntentID' => 'pi_test_123',
            'Currency' => 'USD',
            'Status' => 'pending',
        ]);

        $this->assertSame(25.00, (float) Payment::where('RequestID', $request->RequestID)->first()->Amount);
    }

    public function test_create_intent_is_rejected_when_not_authorized(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($owner, $type);

        $this->actingAs($other)
            ->postJson(route('admin.service.payments.create-intent'), ['request_id' => $request->RequestID])
            ->assertForbidden();
    }

    public function test_create_intent_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('admin.service.payments.create-intent'), [])
            ->assertStatus(422);

        // A request_id that doesn't exist fails the 'exists' validation rule (422),
        // so it never reaches the findOrFail (404) branch.
        $this->actingAs($user)
            ->postJson(route('admin.service.payments.create-intent'), ['request_id' => Str::uuid()])
            ->assertStatus(422);
    }

    /*
     * ---------------------------------------------------------------------
     * Webhook handling
     * ---------------------------------------------------------------------
     */

    public function test_webhook_rejects_invalid_signature(): void
    {
        $event = $this->buildWebhookEvent('payment_intent.succeeded', [
            'id' => 'pi_test_123',
            'currency' => 'usd',
            'status' => 'succeeded',
        ]);

        $this->postRaw('/api/stripe/webhook', $event['payload'], [
            'Stripe-Signature' => 't=123,v1=invalid_signature',
        ])->assertStatus(400);
    }

    public function test_webhook_succeeded_marks_payment_as_succeeded(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $payment = $this->makePendingPayment($request, 'pi_test_123');

        $event = $this->buildWebhookEvent('payment_intent.succeeded', [
            'id' => 'pi_test_123',
            'currency' => 'usd',
            'status' => 'succeeded',
        ]);

        $this->postRaw('/api/stripe/webhook', $event['payload'], [
            'Stripe-Signature' => $event['signature'],
        ])->assertOk();

        $this->assertDatabaseHas('Payments', [
            'StripePaymentIntentID' => 'pi_test_123',
            'Status' => 'succeeded',
        ]);

        $this->assertNotNull(Payment::find($payment->PaymentID)->PaidAt);

        // The queued job (sync driver in tests) finalizes the service request.
        $this->assertDatabaseHas('Service_Requests', [
            'RequestID' => $request->RequestID,
            'Status' => 'Completed',
        ]);
    }

    public function test_webhook_payment_failed_marks_payment_as_failed(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'pi_test_456');

        $event = $this->buildWebhookEvent('payment_intent.payment_failed', [
            'id' => 'pi_test_456',
            'currency' => 'usd',
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'code' => 'card_declined',
                'message' => 'Your card was declined.',
            ],
        ]);

        $this->postRaw('/api/stripe/webhook', $event['payload'], [
            'Stripe-Signature' => $event['signature'],
        ])->assertOk();

        $this->assertDatabaseHas('Payments', [
            'StripePaymentIntentID' => 'pi_test_456',
            'Status' => 'failed',
            'FailureReason' => 'Your card was declined.',
        ]);
    }

    public function test_webhook_is_idempotent_for_duplicate_event_id(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'pi_test_789');

        $event = $this->buildWebhookEvent('payment_intent.succeeded', [
            'id' => 'pi_test_789',
            'currency' => 'usd',
            'status' => 'succeeded',
        ], 'evt_duplicate');

        $headers = ['Stripe-Signature' => $event['signature']];

        $this->postRaw('/api/stripe/webhook', $event['payload'], $headers)->assertOk();
        $this->assertSame(1, StripeWebhookEvent::count());

        // Reset the payment so re-processing (if any) would visibly change it again.
        Payment::where('StripePaymentIntentID', 'pi_test_789')->update(['Status' => 'pending']);

        $this->postRaw('/api/stripe/webhook', $event['payload'], $headers)->assertOk();

        // The duplicate event was ignored: still a single stored event,
        // and the payment was not re-processed.
        $this->assertSame(1, StripeWebhookEvent::count());
        $this->assertDatabaseHas('Payments', [
            'StripePaymentIntentID' => 'pi_test_789',
            'Status' => 'pending',
        ]);
    }
}
