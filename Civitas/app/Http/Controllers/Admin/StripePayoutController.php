<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StripePayout;
use App\Services\StripePayoutsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class StripePayoutController extends Controller
{
    public function __construct(protected StripePayoutsService $payments) {}

    /**
     * Request a withdrawal against the available Stripe balance.
     *
     * The payout goes to the external account attached to the Stripe account
     * (default for the currency), exactly as the official Standard account flow.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'alpha', 'size:3'],
            'destination' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = round((float) $request->input('amount'), 2);
        $amountMinor = (int) round($amount * 100);
        $currency = strtoupper($request->input('currency'));

        if ($amountMinor <= 0) {
            return response()->json(['success' => false, 'message' => 'Amount must be greater than zero.'], 422);
        }

        $balance = $this->payments->getBalance();

        if (isset($balance['error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve the Stripe balance. '.$balance['error'],
            ], 502);
        }

        $availableMinor = $this->payments->availableMinorFor($balance, $currency);

        if ($availableMinor === null) {
            return response()->json([
                'success' => false,
                'message' => 'There is no available balance in '.$currency.'.',
            ], 422);
        }

        if ($amountMinor > $availableMinor) {
            return response()->json([
                'success' => false,
                'message' => 'The requested amount exceeds the available balance ('
                    .number_format($availableMinor / 100, 2).' '.$currency.').',
            ], 422);
        }

        try {
            $payout = $this->payments->createPayout(
                $amountMinor,
                $currency,
                $request->input('destination') ?: null,
                $request->input('description') ?: null,
            );
        } catch (ApiErrorException $e) {
            Log::warning('Stripe: payout creation rejected', [
                'message' => $e->getMessage(),
                'stripe_code' => method_exists($e, 'getStripeCode') ? $e->getStripeCode() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Stripe rejected the payout request: '.$e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Stripe: payout creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'The payout could not be created. Please try again.',
            ], 500);
        }

        $record = StripePayout::create([
            'StripePayoutID' => $payout->id,
            'Amount' => $amount,
            'Currency' => $currency,
            'Status' => $this->mapPayoutStatus($payout->status ?? 'pending'),
            'Destination' => is_string($payout->destination) ? $payout->destination : ($payout->destination->id ?? null),
            'DestinationName' => $this->payments->destinationLabel($payout->destination ?? null),
            'ArrivalDate' => $payout->arrival_date ? Carbon::createFromTimestamp($payout->arrival_date) : null,
            'FailureReason' => $payout->failure_message ?? null,
            'FailureCode' => $payout->failure_code ?? null,
            'Description' => $request->input('description') ?: null,
            'RequestedBy' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted successfully.',
            'payout' => $record->only(['PayoutID', 'StripePayoutID', 'Amount', 'Currency', 'Status', 'DestinationName']),
        ]);
    }

    /**
     * Attach a bank account to the Stripe account from details entered in the
     * application (IBAN or routing/account numbers).
     */
    public function attachBankAccount(Request $request)
    {
        $request->validate([
            'country' => ['required', 'string', 'alpha', 'size:2'],
            'currency' => ['required', 'string', 'alpha', 'size:3'],
            'account_holder_name' => ['required', 'string', 'max:160'],
            'account_holder_type' => ['required', 'string', 'in:individual,company'],
            'routing_number' => ['nullable', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'iban' => ['nullable', 'string', 'max:34'],
            'set_default' => ['sometimes', 'boolean'],
        ]);

        $params = $request->only([
            'country',
            'currency',
            'account_holder_name',
            'account_holder_type',
            'routing_number',
            'account_number',
            'iban',
        ]);

        $params['set_default'] = $request->boolean('set_default');

        if (empty($params['iban']) && empty($params['account_number'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provide either an IBAN or an account number for the bank account.',
            ], 422);
        }

        $result = $this->payments->attachBankAccount($params);

        if (! ($result['ok'] ?? false)) {
            Log::warning('Stripe: bank account attach rejected', ['error' => $result['error'] ?? null]);

            return response()->json([
                'success' => false,
                'message' => 'Stripe rejected the bank account: '.($result['error'] ?? 'Unknown error.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank account attached successfully.',
            'account' => $result['account'],
        ]);
    }

    /**
     * Set an attached bank account as the default for its currency.
     */
    public function setDefaultBankAccount(Request $request)
    {
        $request->validate([
            'external_account' => ['required', 'string'],
        ]);

        $result = $this->payments->setDefaultExternalAccount($request->input('external_account'));

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Could not set this account as default: '.($result['error'] ?? 'Unknown error.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Default bank account updated.',
        ]);
    }

    /**
     * Detach a bank account from the Stripe account.
     */
    public function deleteBankAccount(Request $request)
    {
        $request->validate([
            'external_account' => ['required', 'string'],
        ]);

        $result = $this->payments->deleteExternalAccount($request->input('external_account'));

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Could not remove the bank account: '.($result['error'] ?? 'Unknown error.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank account removed.',
        ]);
    }

    protected function mapPayoutStatus(string $status): string
    {
        return match ($status) {
            'paid' => 'paid',
            'in_transit' => 'in_transit',
            'canceled' => 'canceled',
            'failed' => 'failed',
            default => 'pending',
        };
    }
}
