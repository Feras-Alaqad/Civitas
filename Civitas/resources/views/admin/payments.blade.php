@extends('layouts.admin')

@php $page = 'payments'; $pageName = 'Payments'; @endphp

@section('title', 'Payments')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
            <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12.75 3.03222C16.7762 3.42447 19.9999 6.66679 20.0107 10.6667H8.46182L12.75 3.03222ZM3.75 12C3.75 10.0888 4.42032 8.32128 5.52241 6.93183L9.46012 12.6597C9.46282 12.6637 9.4656 12.6678 9.46847 12.6718C9.48777 12.7011 9.50785 12.731 9.52897 12.7605C9.66874 12.9604 9.82265 13.1532 9.99082 13.3356C11.4697 14.9056 13.3113 15.7614 15.4967 15.9956C15.7951 16.0269 16.1022 16.0434 16.4166 16.0434C18.4904 16.0434 20.4728 15.1393 22.1987 13.4134C22.4068 13.2036 22.6512 12.9656 22.8944 12.7051C22.7344 16.1706 20.8718 19.1788 18.0563 20.9006C15.1167 22.6967 11.3914 22.5414 8.46757 20.7326C5.80506 19.0811 4.05555 16.0011 4.05555 12.0001C4.05555 11.6138 4.06745 11.2266 4.09081 10.8412C4.09632 10.7576 4.13063 10.6769 4.18854 10.6156C4.21426 10.5888 4.24111 10.5637 4.26821 10.5403C4.27518 10.5338 4.28188 10.5274 4.28844 10.5208C4.35671 10.4564 4.43863 10.4088 4.52829 10.3827C5.60958 10.0552 6.26887 8.97746 6.23976 7.79492C6.23195 7.46508 6.18162 7.13736 6.09172 6.82089C6.03551 6.62179 5.95953 6.42928 5.86543 6.24563C9.06958 4.77242 13.136 3.8725 16.58 4.42179C15.9164 4.66488 15.7029 5.5281 16.1799 6.04185C17.2172 7.16132 18.6251 7.85991 20.2601 8.14934C21.1512 8.31089 22.0554 8.39916 22.9626 8.41135C21.9301 5.05528 18.9498 2.35866 15.2055 1.40713C13.7792 1.02985 12.2822 0.841747 10.7584 0.849148C10.7741 0.884324 10.7919 0.918846 10.8105 0.953031C10.8972 1.13845 10.9518 1.33771 10.9712 1.5416C11.0045 1.88785 11.125 2.48267 11.25 2.4833C11.765 2.48677 12.2799 2.48768 12.7949 2.48959C12.9284 2.48994 13.05 2.48498 13.1595 2.47701C13.0743 2.02899 12.9401 1.58998 12.75 1.16866C12.7228 1.11021 12.6974 1.05459 12.671 1.00342V3.03222ZM4.47952 8.60021C3.31246 10.1657 2.70136 12.0836 2.70525 14.1294C2.70525 17.1874 4.42585 19.9558 7.32216 21.2211C10.3475 22.5413 13.8646 21.9569 16.3167 19.6865C18.8252 17.3642 19.8023 13.8349 18.4032 10.997C18.2448 10.6597 17.8718 10.4036 17.3155 10.248C17.389 10.1734 17.4524 10.0909 17.5022 10.0009C17.6122 9.77464 17.5389 9.49967 17.3362 9.35756C16.6875 8.89283 15.7459 8.43946 14.6112 8.00695C14.5701 7.99131 14.5293 7.97599 14.4888 7.961C14.4524 7.9477 14.4133 7.93668 14.3743 7.92566C14.3643 7.92308 14.3542 7.92016 14.3438 7.91722C12.53 8.1534 10.9137 8.9696 9.76664 10.2182C9.48654 10.5219 8.61505 11.709 8.5365 12.2473C8.48419 12.6049 8.42055 12.9433 8.34655 13.2661C7.99319 13.0275 7.6685 12.7666 7.36211 12.4812L4.47952 8.60021ZM21.5194 12.2848C21.7573 12.3824 21.9916 12.4326 22.2224 12.4364C21.9073 13.8606 21.1635 15.1753 20.1262 16.2067C20.6831 16.6765 21.1071 16.9584 21.5194 17.1006C22.0568 15.6012 22.1711 13.9816 21.5194 12.2848Z" fill=""/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Payments</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $stats->total_count ?? '—' }}</h4>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">All</span>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 dark:bg-green-500/10">
            <svg class="fill-green-500 dark:fill-green-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M17 9V7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7V9H6C4.34315 9 3 10.3431 3 12V20C3 21.6569 4.34315 23 6 23H18C19.6569 23 21 21.6569 21 20V12C21 10.3431 19.6569 9 18 9H17ZM9 7C9 5.34315 10.3431 4 12 4C13.6569 4 15 5.34315 15 7V9H9V7ZM5.5 12C5.5 11.7239 5.72386 11.5 6 11.5H18C18.2761 11.5 18.5 11.7239 18.5 12V20C18.5 20.2761 18.2761 20.5 18 20.5H6C5.72386 20.5 5.5 20.2761 5.5 20V12ZM12 13C10.8954 13 10 13.8954 10 15C10 16.1046 10.8954 17 12 17C13.1046 17 14 16.1046 14 15C14 13.8954 13.1046 13 12 13Z" fill="currentColor"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Successful Amount</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ number_format($stats->successful_amount ?? 0, 2) }} {{ $payments->first()?->Currency ?? '' }}</h4>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/10 dark:text-green-400">Collected</span>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10">
            <svg class="fill-amber-500 dark:fill-amber-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12.75 7C12.75 6.58579 12.4142 6.25 12 6.25C11.5858 6.25 11.25 6.58579 11.25 7V12C11.25 12.3038 11.4327 12.5768 11.713 12.6929L15.463 14.1929C15.8461 14.3526 16.2898 14.1738 16.4495 13.7907C16.6092 13.4077 16.4304 12.9639 16.0474 12.8042L12.75 11.4824V7Z" fill="currentColor"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Pending</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $stats->pending_count ?? '—' }}</h4>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">Waiting</span>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 dark:bg-red-500/10">
            <svg class="fill-red-500 dark:fill-red-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12.75 7C12.75 6.58579 12.4142 6.25 12 6.25C11.5858 6.25 11.25 6.58579 11.25 7V12C11.25 12.3038 11.4327 12.5768 11.713 12.6929L15.463 14.1929C15.8461 14.3526 16.2898 14.1738 16.4495 13.7907C16.6092 13.4077 16.4304 12.9639 16.0474 12.8042L12.75 11.4824V7Z" fill="currentColor"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Failed</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $stats->failed_count ?? '—' }}</h4>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400">Rejected</span>
        </div>
    </div>
</div>

{{-- Stripe balance & withdrawals --}}
@php
    $stripeError = $stripeBalance['error'] ?? null;
    $stripeAvailable = $stripeBalance['available'] ?? [];
    $stripePending = $stripeBalance['pending'] ?? [];
    $availMinor = $stripeAvailable[0]['amount'] ?? 0;
    $availCurrency = $stripeAvailable[0]['currency'] ?? ($stripePending[0]['currency'] ?? 'USD');
    $pendMinor = $stripePending[0]['amount'] ?? 0;
    $pendCurrency = $stripePending[0]['currency'] ?? ($stripeAvailable[0]['currency'] ?? 'USD');
    $extAccountsOk = ($stripeExternalAccounts['available'] ?? false) === true;
@endphp

<div x-data="{
    showWithdraw: false,
    showBankAccounts: false,
    submitting: false,
    error: '',
    success: '',
    amount: '',
    currency: '{{ $availCurrency }}',
    destination: '',
    description: '',
    bankError: '',
    bankSuccess: '',
    bankAction: '',
    bankFormMode: {{ empty($stripeAccountsAttached) ? 'true' : 'false' }},
    newBank: {
        country: 'US',
        currency: 'usd',
        account_holder_name: '',
        account_holder_type: 'individual',
        routing_number: '',
        account_number: '',
        iban: '',
        set_default: true,
    },
    post(path, body, onOk, errorTarget) {
        var self = this;
        var et = errorTarget || 'error';
        var token = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
        fetch(path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: new URLSearchParams(body).toString(),
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, d: d }; }); })
        .then(function(res) {
            if (res.ok) {
                onOk(res.d);
            } else {
                self[et] = res.d.message || 'Operation failed. Please try again.';
            }
        })
        .catch(function() { self[et] = 'Network error. Please try again.'; })
        .finally(function() { self.submitting = false; });
    },
    openBankModal() {
        this.bankError = '';
        this.bankSuccess = '';
        this.bankAction = '';
        this.newBank = {
            country: 'US',
            currency: 'usd',
            account_holder_name: '',
            account_holder_type: 'individual',
            routing_number: '',
            account_number: '',
            iban: '',
            set_default: true,
        };
        this.showBankAccounts = true;
    },
    attachBank() {
        this.bankError = '';
        this.bankSuccess = '';
        this.bankAction = 'Attaching bank account...';
        var self = this;
        this.post('{{ route('admin.payments.bank-accounts.attach') }}', {
            country: this.newBank.country,
            currency: this.newBank.currency,
            account_holder_name: this.newBank.account_holder_name,
            account_holder_type: this.newBank.account_holder_type,
            routing_number: this.newBank.routing_number,
            account_number: this.newBank.account_number,
            iban: this.newBank.iban,
            set_default: this.newBank.set_default ? '1' : '0',
        }, function(d) {
            self.bankAction = '';
            self.bankSuccess = d.message || 'Bank account attached successfully.';
            setTimeout(function() { location.reload(); }, 1200);
        }, 'bankError');
    },
    setDefaultBank(id) {
        this.bankError = '';
        this.bankSuccess = '';
        this.bankAction = 'Updating default...';
        var self = this;
        this.post('{{ route('admin.payments.bank-accounts.default') }}', {
            external_account: id,
        }, function(d) {
            self.bankAction = '';
            self.bankSuccess = d.message || 'Default bank account updated.';
            setTimeout(function() { location.reload(); }, 1200);
        }, 'bankError');
    },
    deleteBank(id) {
        if (!confirm('Remove this bank account from Stripe?')) return;
        this.bankError = '';
        this.bankSuccess = '';
        this.bankAction = 'Removing...';
        var self = this;
        this.post('{{ route('admin.payments.bank-accounts.delete') }}', {
            external_account: id,
        }, function(d) {
            self.bankAction = '';
            self.bankSuccess = d.message || 'Bank account removed.';
            setTimeout(function() { location.reload(); }, 1200);
        }, 'bankError');
    },
    submit() {
        this.submitting = true;
        this.error = '';
        this.success = '';
        var self = this;
        var withdraw = function(dest) {
            self.post('{{ route('admin.payments.withdraw') }}', {
                amount: self.amount,
                currency: self.currency,
                destination: dest || '',
                description: self.description,
            }, function(d) {
                self.success = d.message || 'Payout submitted successfully.';
                setTimeout(function() { location.reload(); }, 1200);
            }, 'error');
        };
        if (this.bankFormMode) {
            if (!this.newBank.iban && !this.newBank.account_number) {
                this.submitting = false;
                this.error = 'Enter the bank IBAN or account number first.';
                return;
            }
            this.post('{{ route('admin.payments.bank-accounts.attach') }}', {
                country: this.newBank.country,
                currency: this.newBank.currency,
                account_holder_name: this.newBank.account_holder_name,
                account_holder_type: this.newBank.account_holder_type,
                routing_number: this.newBank.routing_number,
                account_number: this.newBank.account_number,
                iban: this.newBank.iban,
                set_default: this.newBank.set_default ? '1' : '0',
            }, function(d) {
                self.bankFormMode = false;
                withdraw(d.account.id);
            }, 'error');
        } else {
            withdraw(this.destination);
        }
    }
}">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 md:gap-6 mt-4">
        {{-- Balance --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Stripe Balance</h3>
                    @if($stripeTestMode)
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">Test Mode</span>
                    @endif
                </div>
                                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openBankModal()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-4 9 4M4 10v11m16-11v11M8 10v9m4-9v9m4-9v9M9 21h6"/>
                        </svg>
                        Bank Accounts
                    </button>
                    <button type="button" @click="showWithdraw = true"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 active:bg-brand-700 transition-colors shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Withdraw Funds
                    </button>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/40">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Available</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $stripeError ? '—' : number_format($availMinor / 100, 2) }}
                        <span class="text-base font-semibold text-gray-400">{{ $availCurrency }}</span>
                    </h4>
                    <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">Withdrawable via Stripe Payouts</span>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/40">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Pending</span>
                    <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $stripeError ? '—' : number_format($pendMinor / 100, 2) }}
                        <span class="text-base font-semibold text-gray-400">{{ $pendCurrency }}</span>
                    </h4>
                    <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">Arriving after captures complete</span>
                </div>
            </div>

            @if($stripeError)
            <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
                Could not load the Stripe balance: {{ $stripeError }}
            </div>
            @endif

            @if(!$stripeError && !$stripeAccountsAttached)
            <div class="mt-4 flex items-start gap-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>No bank account attached yet. Click <strong>Withdraw Funds</strong> and enter the bank details (IBAN) to choose where the money goes, or use the
                    <button type="button" @click="openBankModal()" class="font-semibold underline underline-offset-2 hover:text-blue-900 dark:hover:text-blue-100">Bank Accounts</button>
                    button to manage accounts.</span>
            </div>
            @endif
        </div>

        {{-- Withdrawal history --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] lg:col-span-1">
            <div class="px-5 pt-5 pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Withdrawal History</h3>
                <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">Most recent 10 payouts via Stripe</span>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40">
                            <th class="py-2.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</span></th>
                            <th class="py-2.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</span></th>
                            <th class="py-2.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Destination</span></th>
                            <th class="py-2.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($withdrawals as $wd)
                        @php
                            $wdBadge = match($wd->Status) {
                                'paid' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                                'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                                'in_transit' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                                'canceled' => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                                'failed' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                                default => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($wd->created_at)->format('d M Y, h:i A') }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($wd->Amount, 2) }} {{ $wd->Currency }}</span>
                                @if($wd->StripePayoutID)
                                <span class="block text-[11px] font-mono text-gray-400 dark:text-gray-500 truncate max-w-[120px]" title="{{ $wd->StripePayoutID }}">{{ $wd->StripePayoutID }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $wd->DestinationName ?? ($wd->Destination ?? '—') }}</span>
                                @if($wd->Description)
                                <span class="block text-xs text-gray-400 dark:text-gray-500 truncate max-w-[160px]" title="{{ $wd->Description }}">{{ $wd->Description }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $wdBadge }}">{{ ucfirst($wd->Status) }}</span>
                                @if($wd->Status === 'failed' && $wd->FailureReason)
                                <span class="block text-xs text-red-400 mt-1 max-w-[140px] truncate" title="{{ $wd->FailureReason }}">{{ $wd->FailureReason }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    <span class="text-sm text-gray-400 dark:text-gray-500">No withdrawals yet.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Withdraw modal --}}
    <div x-show="showWithdraw" x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
        <div class="fixed inset-0 bg-gray-900/50" @click="showWithdraw = false"></div>
        <div class="relative mx-auto mb-6 max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
            <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Withdraw Funds</h3>
                    <p class="mt-0.5 text-sm text-gray-400 dark:text-gray-500">Payout to the bank account attached to your Stripe account</p>
                </div>
                <button type="button" @click="showWithdraw = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form class="px-5 py-4" @submit.prevent="submit()">
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Amount</label>
                        <div class="flex gap-2">
                            <input type="number" min="0.01" step="0.01" x-model="amount" :max="'{{ $stripeError ? 0 : $availMinor / 100 }}'"
                                   placeholder="0.00"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                            <select x-model="currency"
                                    class="w-28 rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all">
                                @foreach($stripeAvailable as $av)
                                <option value="{{ $av['currency'] }}">{{ $av['currency'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">Available: {{ $stripeError ? '—' : number_format($availMinor / 100, 2) }} {{ $availCurrency }}</span>
                    </div>

                    <div x-show="bankFormMode" x-cloak>
                        <div class="mb-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
                            Choose the bank account you want to receive the withdrawal by entering its details below.
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Country (ISO-2)</label>
                                    <input type="text" x-model="newBank.country" maxlength="2" placeholder="US"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Currency</label>
                                    <input type="text" x-model="newBank.currency" maxlength="3" placeholder="usd"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Account holder name</label>
                                <input type="text" x-model="newBank.account_holder_name" maxlength="160" placeholder="Jane Doe"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Account holder type</label>
                                <select x-model="newBank.account_holder_type"
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all">
                                    <option value="individual">Individual</option>
                                    <option value="company">Company</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">IBAN or account number</label>
                                <input type="text" x-model="newBank.iban" maxlength="34" placeholder="e.g. 000123456789"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">
                                    For US ACH accounts also provide routing + account number. Test values: routing <code>110000000</code>, account <code>000123456789</code>.
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Routing number (US)</label>
                                    <input type="text" x-model="newBank.routing_number" maxlength="50" placeholder="110000000"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Account number (US)</label>
                                    <input type="text" x-model="newBank.account_number" maxlength="100" placeholder="000123456789"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="!bankFormMode" x-cloak>
                        @if($extAccountsOk && count($stripeExternalAccounts['data']) > 0)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Destination account</label>
                            <select x-model="destination" id="withdraw-destinations"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all">
                                <option value="">Default account for {{ $availCurrency }}</option>
                                @foreach($stripeExternalAccounts['data'] as $ext)
                                <option value="{{ $ext['id'] }}">{{ $ext['label'] }}{{ $ext['default_for_currency'] ? ' (default)' : '' }}</option>
                                @endforeach
                            </select>
                            <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">
                                Need to change the receiving bank account? Use the
                                <button type="button" @click="showWithdraw = false; openBankModal()" class="font-semibold underline underline-offset-2">Bank Accounts</button> modal.
                            </span>
                        </div>
                        @else
                        <div>
                            <span class="block text-xs text-gray-400 dark:text-gray-500">
                                No destination selected. Attach a bank account to choose where payouts are sent.
                            </span>
                        </div>
                        @endif
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Description (optional)</label>
                        <input type="text" x-model="description" maxlength="255"
                               placeholder="e.g. Monthly revenue transfer"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                    </div>

                    <div x-show="error" class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
                        <span x-text="error"></span>
                    </div>
                    <div x-show="success" class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm text-green-600 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-400">
                        <span x-text="success"></span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button type="button" @click="showWithdraw = false"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="submitting || !amount || parseFloat(amount) <= 0 || (bankFormMode && !newBank.iban && !newBank.account_number)"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 active:bg-brand-700 transition-colors shadow-sm disabled:cursor-not-allowed disabled:opacity-60">
                        <svg x-show="submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-show="!submitting" x-text="bankFormMode ? 'Withdraw to this bank' : 'Withdraw Now'"></span>
                        <span x-show="submitting">Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bank accounts modal --}}
    <div x-show="showBankAccounts" x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
        <div class="fixed inset-0 bg-gray-900/50" @click="showBankAccounts = false"></div>
        <div class="relative mx-auto mb-6 max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
            <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Bank Accounts</h3>
                    <p class="mt-0.5 text-sm text-gray-400 dark:text-gray-500">Attach a bank account to receive Stripe payouts</p>
                </div>
                <button type="button" @click="showBankAccounts = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4">
                <div x-show="bankAction" class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
                    <span x-text="bankAction"></span>
                </div>
                <div x-show="bankError" class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
                    <span x-text="bankError"></span>
                </div>
                <div x-show="bankSuccess" class="mb-4 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm text-green-600 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-400">
                    <span x-text="bankSuccess"></span>
                </div>

                {{-- Attached accounts --}}
                @php $attached = ($stripeExternalAccounts['data'] ?? []); @endphp
                <div class="mb-6">
                    <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-white/90">Attached accounts</h4>
                    @if(empty($attached))
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-center text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800/40 dark:text-gray-500">
                        No bank accounts attached yet. Add one below to choose where payouts are sent.
                    </div>
                    @else
                    <div class="space-y-2">
                        @foreach($attached as $acc)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/40">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $acc['label'] }}</span>
                                    @if($acc['default_for_currency'])
                                    <span class="inline-flex shrink-0 items-center rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">Default</span>
                                    @endif
                                </div>
                                <span class="mt-0.5 block truncate text-xs text-gray-400 dark:text-gray-500">
                                    {{ $acc['currency'] ?: '—' }} · {{ $acc['bank_name'] ?? 'Bank' }}
                                </span>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @unless($acc['default_for_currency'])
                                <button type="button" @click="setDefaultBank('{{ $acc['id'] }}')"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    Set default
                                </button>
                                @endunless
                                <button type="button" @click="deleteBank('{{ $acc['id'] }}')"
                                        class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-500/20 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors">
                                    Remove
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Attach new account --}}
                <div>
                    <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-white/90">Attach a new bank account</h4>
                    <form @submit.prevent="attachBank()">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Country (ISO-2)</label>
                                    <input type="text" x-model="newBank.country" maxlength="2" placeholder="US"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Currency</label>
                                    <input type="text" x-model="newBank.currency" maxlength="3" placeholder="usd"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Account holder name</label>
                                <input type="text" x-model="newBank.account_holder_name" maxlength="160" placeholder="Jane Doe"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Account holder type</label>
                                <select x-model="newBank.account_holder_type"
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all">
                                    <option value="individual">Individual</option>
                                    <option value="company">Company</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">IBAN or account number</label>
                                <input type="text" x-model="newBank.iban" maxlength="34" placeholder="e.g. US IBAN or account number: 000123456789"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                <span class="mt-1 block text-xs text-gray-400 dark:text-gray-500">
                                    For US ACH accounts also provide routing + account number below. Test values: routing <code>110000000</code>, account <code>000123456789</code>.
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Routing number (US)</label>
                                    <input type="text" x-model="newBank.routing_number" maxlength="50" placeholder="110000000"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Account number (US)</label>
                                    <input type="text" x-model="newBank.account_number" maxlength="100" placeholder="000123456789"
                                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 transition-all"/>
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input type="checkbox" x-model="newBank.set_default"
                                       class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"/>
                                Set as default destination
                            </label>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-2">
                            <button type="button" @click="showBankAccounts = false"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                Close
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 active:bg-brand-700 transition-colors shadow-sm">
                                Attach Bank Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    {{-- Title --}}
    <div class="px-5 pt-5 pb-4 border-b border-gray-100 dark:border-gray-800">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex shrink-0 items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Payments</h3>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-500 dark:bg-brand-500/15 dark:text-brand-500">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ $payments->total() }}
                </span>
                @isset($loadTimeMs)
                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $loadTimeMs }} ms
                </span>
                @endisset
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="px-5 pt-4 pb-4">
        <form method="GET" action="{{ route('admin.payments') }}">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                {{-- Search --}}
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search person, national ID, receipt, reference..."
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all"/>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all">
                        <option value="">All Statuses</option>
                        @foreach(['succeeded', 'pending', 'failed'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Gateway --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Gateway</label>
                    <select name="gateway" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all">
                        <option value="">All Gateways</option>
                        <option value="stripe" {{ request('gateway') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                        <option value="lahza" {{ request('gateway') === 'lahza' ? 'selected' : '' }}>Lahza</option>
                        <option value="nowpayments" {{ request('gateway') === 'nowpayments' ? 'selected' : '' }}>NOWPayments</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all"/>
                </div>

                {{-- Date To --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all"/>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="mt-3 flex items-center gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 active:bg-brand-700 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'gateway', 'date_from', 'date_to']))
                <a href="{{ route('admin.payments') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear
                </a>
                @endif
            </div>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateFrom = document.querySelector('input[name="date_from"]');
            var dateTo = document.querySelector('input[name="date_to"]');
            var form = dateFrom.closest('form');

            dateFrom.addEventListener('change', function() {
                if (dateFrom.value) {
                    dateTo.min = dateFrom.value;
                    if (dateTo.value && dateTo.value < dateFrom.value) {
                        dateTo.value = dateFrom.value;
                    }
                } else {
                    dateTo.removeAttribute('min');
                }
            });

            if (dateFrom.value) {
                dateTo.min = dateFrom.value;
            }

            form.addEventListener('submit', function(e) {
                if (dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
                    e.preventDefault();
                    dateTo.focus();
                    dateTo.style.borderColor = '#ef4444';
                    setTimeout(function() { dateTo.style.borderColor = ''; }, 2000);
                }
            });
        });
        </script>
    </div>

    {{-- Data Grid --}}
    <div class="border-t border-gray-100 dark:border-gray-800">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/40">
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Person</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Service</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gateway</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Receipt</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($payments as $payment)
                    @php
                        $gateway = $payment->NowPaymentsPaymentID ? 'NOWPayments' : ($payment->LahzaReference ? 'Lahza' : ($payment->StripePaymentIntentID ? 'Stripe' : 'Unknown'));
                        $gatewayBadge = $payment->NowPaymentsPaymentID
                            ? 'bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400'
                            : ($payment->LahzaReference
                                ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'
                                : 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400');
                        $statusBadge = match($payment->Status) {
                            'succeeded' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                            'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                            'failed' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                            default => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->FullName ?? '—' }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $payment->NationalID ?? '' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->ServiceName ?? '—' }}</span>
                            @if($payment->DepartmentName)
                            <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $payment->DepartmentName }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($payment->Amount, 2) }} {{ $payment->Currency ?? '' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $gatewayBadge }}">{{ $gateway }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusBadge }}">{{ ucfirst($payment->Status ?? '—') }}</span>
                            @if($payment->Status === 'failed' && $payment->FailureReason)
                            <span class="block text-xs text-red-400 dark:text-red-400/80 mt-1 max-w-[160px] truncate" title="{{ $payment->FailureReason }}">{{ $payment->FailureReason }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($payment->PaymentDate ?? $payment->PaidAt)->format('d M Y, h:i A') }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-xs font-mono text-gray-400 dark:text-gray-500">{{ $payment->ReceiptNumber ?? '—' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm text-gray-400 dark:text-gray-500">No payments found.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $payments->onEachSide(1)->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>
@endsection
