@php
    $amount = (float) $serviceType?->Fees;
    $currency = strtoupper(config('lahza.default_currency', 'ILS'));
    $symbol = $currency === 'ILS' ? '₪' : ($currency === 'USD' ? '$' : $currency . ' ');
    $continueAction = route('payment.lahza.continue');
    $backUrl = route('admin.citizens', ['person_id' => $person?->PersonID]);
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
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4a4 4 0 100-8 4 4 0 000 8zm7-3a3 3 0 100-6 3 3 0 000 6zm0 3c0 1.5-1.3 2.2-2.6 2.2H11"/></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Continue with Lahza</h1>
                    <p class="text-xs text-gray-400">Bank of Palestine</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm">
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Service</span>
                    <span class="font-semibold text-gray-800">{{ $serviceType?->ServiceName ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Person</span>
                    <span class="font-semibold text-gray-800">{{ $person?->FullName ?? '—' }}</span>
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

            <form method="POST" action="{{ $continueAction }}" class="mt-6" id="continueForm">
                @csrf
                <input type="hidden" name="request_id" value="{{ $serviceRequest->RequestID }}">

                <button type="submit" id="continueBtn"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-blue-700 transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="continueBtnLabel">Resume Payment</span>
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between text-xs">
                <a href="{{ $backUrl }}" class="text-gray-400 hover:text-gray-600">Back to Citizen</a>
                <a href="{{ $cancelUrl }}" class="text-gray-400 hover:text-gray-600">Cancel</a>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = document.getElementById('continueForm');

        form.addEventListener('submit', () => {
            const btn = document.getElementById('continueBtn');
            btn.disabled = true;
            document.getElementById('continueBtnLabel').textContent = 'Redirecting to Lahza\u2026';
        });
    </script>
</body>
</html>
