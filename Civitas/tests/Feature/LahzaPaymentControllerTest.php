<?php

namespace Tests\Feature;

use App\Models\LahzaWebhookEvent;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class LahzaPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['lahza.public_key' => 'pk_test_dummy']);
        config(['lahza.secret_key' => 'sk_test_dummy_secret']);
        config(['lahza.webhook_secret' => 'whsec_test_secret']);
        config(['lahza.callback_url' => 'http://localhost/payment/lahza/callback']);
        config(['lahza.base_url' => 'https://api.lahza.io']);
        config(['lahza.default_currency' => 'ILS']);
    }

    protected function makeServiceType(float $fees = 25.00): ServiceType
    {
        return ServiceType::create([
            'ServiceName' => 'Test Service',
            'Fees' => $fees,
            'RequiredDocuments' => null,
        ]);
    }

    protected function makePerson(string $personId = 'aaaaaaaa-0000-0000-0000-000000000001'): string
    {
        DB::table('Persons')->insertOrIgnore([
            'PersonID' => $personId,
            'FullName' => 'Test Person',
            'DateOfBirth' => null,
            'NationalID' => null,
            'Address' => null,
            'Gender' => 'M',
            'NationalityID' => null,
            'CityID' => null,
            'Phone' => '0599000000',
            'Email' => 'testperson@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $personId;
    }

    protected function makeServiceRequest(User $user, ServiceType $type): ServiceRequest
    {
        $personId = $this->makePerson(Str::uuid()->toString());

        return ServiceRequest::create([
            'PersonID' => $personId,
            'UserID' => $user->id,
            'ServiceTypeID' => $type->ServiceTypeID,
            'RequestDate' => now(),
            'Status' => 'Pending',
        ]);
    }

    protected function makePendingPayment(ServiceRequest $sr, string $reference): Payment
    {
        return Payment::create([
            'RequestID' => $sr->RequestID,
            'Amount' => 25.00,
            'PaymentDate' => now(),
            'LahzaReference' => $reference,
            'Currency' => 'ILS',
            'Status' => 'pending',
        ]);
    }

    protected function fakeInitializeSuccess(string $reference, string $url = 'https://checkout.lahza.io/dummy_checkout'): void
    {
        Http::fake([
            'api.lahza.io/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => $url,
                    'access_code' => 'access_dummy',
                    'reference' => $reference,
                ],
            ], 200),
        ]);
    }

    protected function fakeVerify(array $data): void
    {
        Http::fake([
            'api.lahza.io/transaction/verify/*' => Http::response([
                'status' => true,
                'message' => 'Verification successful',
                'data' => $data,
            ], 200),
        ]);
    }

    protected function buildWebhookEvent(string $type, array $data, ?int $eventId = null): array
    {
        $eventId = $eventId ?: random_int(100000, 999999);

        $payload = json_encode([
            'event' => $type,
            'data' => array_merge([
                'id' => $eventId,
                'domain' => 'test',
            ], $data),
        ]);

        $timestamp = time();
        $signedPayload = $timestamp.'.'.$payload;
        $digest = hash_hmac('sha256', $signedPayload, config('lahza.webhook_secret'));

        // Matches the controller's expected X-Lahza-Signature format: "t=<ts>,v1=<digest>"
        $signature = 't='.$timestamp.',v1='.$digest;

        return [
            'id' => $eventId,
            'payload' => $payload,
            'signature' => $signature,
            'timestamp' => $timestamp,
        ];
    }

    protected function postRaw(string $uri, string $body, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->call('POST', $uri, [], [], [], $this->transformHeadersToServerVars($headers), $body);
    }

    /*
     * ---------------------------------------------------------------------
     * Transaction initialization
     * ---------------------------------------------------------------------
     */

    public function test_create_intent_returns_authorization_url_and_creates_pending_payment(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);

        $this->fakeInitializeSuccess('lahza_ref_123');

        $this->actingAs($user)
            ->postJson(route('payment.lahza.initialize'), [
                'request_id' => $request->RequestID,
                'email' => 'customer@example.com',
                'mobile' => '0599123456',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'authorization_url' => 'https://checkout.lahza.io/dummy_checkout',
                'reference' => 'lahza_ref_123',
            ]);

        $this->assertDatabaseHas('Payments', [
            'RequestID' => $request->RequestID,
            'LahzaReference' => 'lahza_ref_123',
            'Currency' => 'ILS',
            'Status' => 'pending',
        ]);

        $this->assertSame(25.00, (float) Payment::where('RequestID', $request->RequestID)->first()->Amount);
    }

    public function test_create_intent_ignores_client_supplied_amount(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);

        $this->fakeInitializeSuccess('lahza_ref_tamper');

        // A malicious client passes a tampered (much cheaper) amount; the
        // server must always price from ServiceType->Fees, never from the request.
        $this->actingAs($user)
            ->postJson(route('payment.lahza.initialize'), [
                'request_id' => $request->RequestID,
                'amount' => 0.01,
                'email' => 'customer@example.com',
            ])
            ->assertOk();

        $this->assertSame(25.00, (float) Payment::where('RequestID', $request->RequestID)->first()->Amount);
    }

    public function test_create_intent_is_rejected_when_not_authorized(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($owner, $type);

        $this->actingAs($other)
            ->postJson(route('payment.lahza.initialize'), ['request_id' => $request->RequestID])
            ->assertForbidden();
    }

    public function test_create_intent_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('payment.lahza.initialize'), [])
            ->assertStatus(422);

        // A request_id that doesn't exist fails the 'exists' validation rule (422),
        // so it never reaches the findOrFail (404) branch.
        $this->actingAs($user)
            ->postJson(route('payment.lahza.initialize'), ['request_id' => Str::uuid()])
            ->assertStatus(422);
    }

    /*
     * ---------------------------------------------------------------------
     * Callback verification (server-side confirmation)
     * ---------------------------------------------------------------------
     */

    public function test_callback_verifies_and_finalizes_payment(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $payment = $this->makePendingPayment($request, 'lahza_cb_ok');

        $this->fakeVerify([
            'status' => 'success',
            'reference' => 'lahza_cb_ok',
            'amount' => 2500,
            'currency' => 'ILS',
            'gateway_response' => 'Successful',
        ]);

        $this->actingAs($user)
            ->get(route('payment.lahza.callback', ['reference' => 'lahza_cb_ok']))
            ->assertRedirect(route('admin.service.payments.page', ['requestId' => $request->RequestID]));

        // Success is only trusted after the server verified it via the Lahza API.
        $this->assertDatabaseHas('Payments', [
            'RequestID' => $request->RequestID,
            'LahzaReference' => 'lahza_cb_ok',
            'Status' => 'succeeded',
        ]);

        $this->assertNotNull(Payment::find($payment->PaymentID)->PaidAt);

        // The queued job (sync driver in tests) finalizes the service request.
        $this->assertDatabaseHas('Service_Requests', [
            'RequestID' => $request->RequestID,
            'Status' => 'Completed',
        ]);
    }

    public function test_callback_does_not_finalize_when_transaction_is_not_success(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'lahza_cb_abandoned');

        $this->fakeVerify([
            'status' => 'abandoned',
            'reference' => 'lahza_cb_abandoned',
            'gateway_response' => 'Transaction abandoned',
        ]);

        $this->actingAs($user)
            ->get(route('payment.lahza.callback', ['reference' => 'lahza_cb_abandoned']))
            ->assertRedirect(route('admin.service.payments.page', ['requestId' => $request->RequestID]));

        // Visiting the callback URL alone must never finalize the payment.
        $this->assertDatabaseHas('Payments', [
            'RequestID' => $request->RequestID,
            'LahzaReference' => 'lahza_cb_abandoned',
            'Status' => 'pending',
        ]);

        $this->assertDatabaseHas('Service_Requests', [
            'RequestID' => $request->RequestID,
            'Status' => 'Pending',
        ]);
    }

    /*
     * ---------------------------------------------------------------------
     * Webhook handling
     * ---------------------------------------------------------------------
     */

    public function test_webhook_get_health_check_returns_ok_without_processing(): void
    {
        $this->get('/webhooks/lahza')
            ->assertOk()
            ->assertSee('ok');

        // Nothing was stored or processed by the GET health check.
        $this->assertSame(0, LahzaWebhookEvent::count());
        $this->assertDatabaseCount('Payments', 0);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $event = $this->buildWebhookEvent('charge.success', [
            'reference' => 'pi_test_123',
            'currency' => 'ILS',
            'status' => 'success',
        ]);

        $this->postRaw('/webhooks/lahza', $event['payload'], [
            'X-Lahza-Signature' => 'invalid_signature',
        ])->assertStatus(400);
    }

    public function test_webhook_charge_success_marks_payment_as_succeeded(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $payment = $this->makePendingPayment($request, 'lahza_test_123');

        $event = $this->buildWebhookEvent('charge.success', [
            'reference' => 'lahza_test_123',
            'currency' => 'ILS',
            'status' => 'success',
            'amount' => 2500,
        ]);

        $this->postRaw('/webhooks/lahza', $event['payload'], [
            'X-Lahza-Signature' => $event['signature'],
        ])->assertOk();

        $this->assertDatabaseHas('Payments', [
            'LahzaReference' => 'lahza_test_123',
            'Status' => 'succeeded',
        ]);

        $this->assertNotNull(Payment::find($payment->PaymentID)->PaidAt);

        // The queued job (sync driver in tests) finalizes the service request.
        $this->assertDatabaseHas('Service_Requests', [
            'RequestID' => $request->RequestID,
            'Status' => 'Completed',
        ]);
    }

    public function test_webhook_rejects_stale_timestamp_to_prevent_replay(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'lahza_replay');

        $event = $this->buildWebhookEvent('charge.success', [
            'reference' => 'lahza_replay',
            'currency' => 'ILS',
            'status' => 'success',
            'amount' => 2500,
        ]);

        // Simulate a replay: rewrite the signature with an old timestamp (1 hour ago).
        $timestamp = time() - 3600;
        $signedPayload = $timestamp.'.'.$event['payload'];
        $digest = hash_hmac('sha256', $signedPayload, config('lahza.webhook_secret'));
        $replaySignature = 't='.$timestamp.',v1='.$digest;

        $this->postRaw('/webhooks/lahza', $event['payload'], [
            'X-Lahza-Signature' => $replaySignature,
        ])->assertStatus(400);

        // The payment must NOT be marked succeeded on a replayed event.
        $this->assertDatabaseHas('Payments', [
            'LahzaReference' => 'lahza_replay',
            'Status' => 'pending',
        ]);
        $this->assertSame(0, LahzaWebhookEvent::count());
    }

    public function test_webhook_does_not_finalize_on_amount_mismatch(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'lahza_mismatch');

        $event = $this->buildWebhookEvent('charge.success', [
            'reference' => 'lahza_mismatch',
            'currency' => 'ILS',
            'status' => 'success',
            'amount' => 1000, // Expected 2500
        ]);

        $this->postRaw('/webhooks/lahza', $event['payload'], [
            'X-Lahza-Signature' => $event['signature'],
        ])->assertOk();

        // The payment must NOT be marked succeeded on a mismatched amount.
        $this->assertDatabaseHas('Payments', [
            'LahzaReference' => 'lahza_mismatch',
            'Status' => 'pending',
        ]);
        $this->assertDatabaseHas('Service_Requests', [
            'RequestID' => $request->RequestID,
            'Status' => 'Pending',
        ]);
    }

    public function test_webhook_is_idempotent_for_duplicate_event_id(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'lahza_test_789');

        $event = $this->buildWebhookEvent('charge.success', [
            'reference' => 'lahza_test_789',
            'currency' => 'ILS',
            'status' => 'success',
            'amount' => 2500,
        ], 777777);

        $headers = ['X-Lahza-Signature' => $event['signature']];

        $this->postRaw('/webhooks/lahza', $event['payload'], $headers)->assertOk();
        $this->assertSame(1, LahzaWebhookEvent::count());

        // Reset the payment so re-processing (if any) would visibly change it again.
        Payment::where('LahzaReference', 'lahza_test_789')->update(['Status' => 'pending']);

        $this->postRaw('/webhooks/lahza', $event['payload'], $headers)->assertOk();

        // The duplicate event was ignored: still a single stored event,
        // and the payment was not re-processed.
        $this->assertSame(1, LahzaWebhookEvent::count());
        $this->assertDatabaseHas('Payments', [
            'LahzaReference' => 'lahza_test_789',
            'Status' => 'pending',
        ]);
    }

    /*
     * ---------------------------------------------------------------------
     * Continue payment (resume with the correct gateway)
     * ---------------------------------------------------------------------
     */

    public function test_resume_page_shows_confirmation_for_lahza_pending_payment(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'lahza_resume');

        $this->actingAs($user)
            ->get(route('payment.lahza.page', ['requestId' => $request->RequestID]))
            ->assertOk()
            ->assertSee('Continue with Lahza')
            ->assertSee('25.00');
    }

    public function test_resume_page_for_stripe_payment_redirects_to_stripe_page(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);

        // This payment was started via Stripe.
        Payment::create([
            'RequestID' => $request->RequestID,
            'Amount' => 25.00,
            'PaymentDate' => now(),
            'StripePaymentIntentID' => 'pi_resume_stripe',
            'Currency' => 'USD',
            'Status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('payment.lahza.page', ['requestId' => $request->RequestID]))
            ->assertRedirect(route('admin.service.payments.page', ['requestId' => $request->RequestID]));
    }

    public function test_resume_page_is_rejected_when_not_authorized(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($owner, $type);
        $this->makePendingPayment($request, 'lahza_resume_auth');

        $this->actingAs($other)
            ->get(route('payment.lahza.page', ['requestId' => $request->RequestID]))
            ->assertForbidden();
    }

    public function test_continue_payment_redirects_to_lahza_authorization_url(): void
    {
        $user = User::factory()->create();
        $type = $this->makeServiceType(25.00);
        $request = $this->makeServiceRequest($user, $type);
        $this->makePendingPayment($request, 'lahza_resume_cont');

        $this->fakeInitializeSuccess('lahza_resume_cont');

        $this->actingAs($user)
            ->post(route('payment.lahza.continue'), ['request_id' => $request->RequestID])
            ->assertRedirect('https://checkout.lahza.io/dummy_checkout');

        // A fresh Lahza payment row was created for the resumed attempt.
        $this->assertDatabaseHas('Payments', [
            'RequestID' => $request->RequestID,
            'LahzaReference' => 'lahza_resume_cont',
            'Status' => 'pending',
        ]);
        $this->assertSame(25.00, (float) Payment::where('RequestID', $request->RequestID)->first()->Amount);
    }
}
