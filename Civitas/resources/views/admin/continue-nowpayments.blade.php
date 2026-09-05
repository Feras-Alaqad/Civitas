@php
    $amount = (float) $serviceType?->Fees;
    $currency = strtoupper(config('services.stripe.currency', 'USD'));
    $symbol = $currency === 'USD' ? '$' : $currency.' ';
    $backUrl = route('admin.citizens', ['person_id' => $serviceRequest->person?->PersonID ?? '']);
    $cancelUrl = route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]);
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Continue Payment | Civitas</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .brand-bg { background: radial-gradient(1200px 600px at top -100px left -100px, #1e293b 0%, #0f172a 45%, #020617 100%); }
    </style>
</head>
<body class="h-full min-h-screen brand-bg antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Continue with Crypto</h1>
                    <p class="text-xs text-gray-400">NOWPayments</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm">
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Service</span>
                    <span class="font-semibold text-gray-800">{{ $serviceType?->ServiceName ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Person</span>
                    <span class="font-semibold text-gray-800">{{ $serviceRequest->person?->FullName ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Amount</span>
                    <span class="font-semibold text-gray-900">{{ $symbol }}{{ number_format($amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Request</span>
                    <span class="font-mono text-xs font-semibold text-gray-800">{{ $serviceRequest->RequestID }}</span>
                </div>
            </div>

            <button type="button" id="continueBtn" onclick="resumeCryptoPayment()"
                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-purple-700 transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-60">
                <span id="continueBtnLabel">Resume Crypto Payment</span>
            </button>

            <p id="continueError" class="mt-4 hidden rounded-lg bg-red-50 px-4 py-2.5 text-sm font-medium text-red-600"></p>

            <div class="mt-4 flex items-center justify-between text-xs">
                <a href="{{ $backUrl }}" class="text-gray-400 hover:text-gray-600">Back to Citizen</a>
                <a href="{{ $cancelUrl }}" class="text-gray-400 hover:text-gray-600">Cancel</a>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function resumeCryptoPayment() {
            const btn = document.getElementById('continueBtn');
            const label = document.getElementById('continueBtnLabel');
            const errorEl = document.getElementById('continueError');

            btn.disabled = true;
            label.textContent = 'Creating new invoice\u2026';
            errorEl.classList.add('hidden');

            try {
                const res = await fetch('{{ route("payment.nowpayments.initialize") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        request_id: '{{ $serviceRequest->RequestID }}',
                    }),
                });

                const data = await res.json();

                if (!data.success || !data.invoice_url) {
                    throw new Error(data.message || 'Unable to resume the payment.');
                }

                window.location.href = data.invoice_url;
            } catch (err) {
                label.textContent = 'Resume Crypto Payment';
                btn.disabled = false;
                errorEl.textContent = err.message || 'Unable to resume the payment. Please try again.';
                errorEl.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
