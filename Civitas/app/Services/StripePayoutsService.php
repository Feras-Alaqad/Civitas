<?php

namespace App\Services;

use Stripe\Payout;
use Stripe\StripeClient;

class StripePayoutsService
{
    public function __construct(protected StripeClient $stripe) {}

    /**
     * Retrieve the Stripe account balance (Platform Balance for a standard account).
     */
    public function getBalance(): array
    {
        try {
            $balance = $this->stripe->balance->retrieve();
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        return [
            'available' => $this->formatBalanceList($balance->available),
            'pending' => $this->formatBalanceList($balance->pending),
            'connect_reserved' => $this->formatBalanceList($balance->connect_reserved ?? []),
        ];
    }

    /**
     * Available (withdrawable) balance in minor units for the given currency, or null.
     */
    public function availableMinorFor(array $balance, string $currency): ?int
    {
        if (isset($balance['error'])) {
            return null;
        }

        $currency = strtoupper($currency);

        foreach ($balance['available'] as $item) {
            if ($item['currency'] === $currency) {
                return (int) $item['amount'];
            }
        }

        return null;
    }

    /**
     * Attempt to list external accounts attached to this Stripe account.
     *
     * For standard accounts in test mode Stripe blocks this endpoint, so the
     * result gracefully reports that instead of failing.
     */
    public function getExternalAccounts(): array
    {
        try {
            $accounts = $this->stripe->accounts->allExternalAccounts('me', ['limit' => 25]);
        } catch (\Throwable $e) {
            return ['available' => false, 'reason' => $e->getMessage()];
        }

        $items = [];

        foreach ($accounts->data as $account) {
            $items[] = $this->externalAccountItem($account);
        }

        return ['available' => true, 'data' => $items];
    }

    /**
     * Attach a bank account to this Stripe account using the details entered
     * in the application (e.g. an IBAN or routing/account numbers).
     *
     * The bank account object returned by Stripe is stored (id + safe label),
     * never the raw account number or full IBAN.
     */
    public function attachBankAccount(array $params): array
    {
        $bankAccount = array_filter([
            'object' => 'bank_account',
            'country' => strtoupper((string) ($params['country'] ?? '')),
            'currency' => strtolower((string) ($params['currency'] ?? '')),
            'account_holder_name' => $params['account_holder_name'] ?? null,
            'account_holder_type' => $params['account_holder_type'] ?? 'individual',
            'routing_number' => $params['routing_number'] ?? null,
            'account_number' => $params['account_number'] ?? null,
            'iban' => $params['iban'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $account = $this->stripe->accounts->createExternalAccount('me', ['external_account' => $bankAccount]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $item = $this->externalAccountItem($account);

        if (! empty($params['set_default'])) {
            $this->setDefaultExternalAccount($item['id']);
        }

        return ['ok' => true, 'account' => $item];
    }

    /**
     * Set an external account as the default for its currency.
     */
    public function setDefaultExternalAccount(string $externalAccountId): array
    {
        try {
            $this->stripe->accounts->updateExternalAccount('me', $externalAccountId, [
                'default_for_currency' => true,
            ]);

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Detach an external account from this Stripe account.
     */
    public function deleteExternalAccount(string $externalAccountId): array
    {
        try {
            $this->stripe->accounts->deleteExternalAccount('me', $externalAccountId);

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a payout through the official Stripe Payouts API.
     *
     * The destination defaults to the external account attached to the Stripe
     * account for the given currency (standard account behaviour).
     */
    public function createPayout(
        int $amountMinor,
        string $currency,
        ?string $destination = null,
        ?string $description = null
    ): Payout {
        $params = [
            'amount' => $amountMinor,
            'currency' => strtolower($currency),
            'expand' => ['destination'],
        ];

        if ($destination !== null && $destination !== '') {
            $params['destination'] = $destination;
        }

        if ($description !== null && trim((string) $description) !== '') {
            $params['description'] = trim((string) $description);
        }

        return $this->stripe->payouts->create($params);
    }

    /**
     * Human-friendly label for a payout destination (never stores raw bank numbers).
     */
    public function destinationLabel($destination): ?string
    {
        if ($destination === null) {
            return null;
        }

        if (is_string($destination)) {
            return $destination;
        }

        if (($destination->object ?? null) === 'bank_account') {
            $label = trim((string) ($destination->bank_name ?? ''));
            if (isset($destination->last4)) {
                $label = trim($label.' •••• '.$destination->last4);
            }

            return $label !== '' ? $label : $destination->id;
        }

        if (($destination->object ?? null) === 'card') {
            return 'Card •••• '.($destination->last4 ?? $destination->id ?? '');
        }

        return $destination->id ?? null;
    }

    public function isTestMode(): bool
    {
        return str_starts_with((string) $this->stripe->getApiKey(), 'sk_test_');
    }

    protected function formatBalanceList($list): array
    {
        $items = [];

        foreach ($list as $item) {
            $items[] = [
                'amount' => (int) $item->amount,
                'currency' => strtoupper($item->currency),
            ];
        }

        return $items;
    }

    protected function externalAccountLabel($account): string
    {
        switch ($account->object ?? '') {
            case 'bank_account':
                $label = trim((string) ($account->bank_name ?? ''));
                if (isset($account->last4)) {
                    $label = trim($label.' •••• '.$account->last4);
                }

                return $label !== '' ? $label : $account->id;

            case 'card':
                return 'Card •••• '.($account->last4 ?? $account->id);

            default:
                return $account->id ?? '';
        }
    }

    protected function externalAccountItem($account): array
    {
        return [
            'id' => $account->id,
            'object' => $account->object ?? '',
            'label' => $this->externalAccountLabel($account),
            'default_for_currency' => $account->default_for_currency ?? false,
            'currency' => strtoupper((string) ($account->currency ?? '')),
            'country' => strtoupper((string) ($account->country ?? '')),
            'bank_name' => $account->bank_name ?? null,
            'routing_number' => $account->routing_number ?? null,
            'last4' => $account->last4 ?? null,
            'account_holder_name' => $account->account_holder_name ?? null,
            'account_holder_type' => $account->account_holder_type ?? null,
        ];
    }
}
