@php
    $amount = (float) $serviceType?->Fees;
    $personName = $person?->FullName ?? '—';
    $stripePublishableKey = trim((string) config('services.stripe.key'));
    $statusEndpoint = route('admin.service.payments.status', ['requestId' => $serviceRequest->RequestID]);
    $returnUrl = route('admin.service.payments.page', ['requestId' => $serviceRequest->RequestID]);
    $backUrl = route('admin.citizens', ['person_id' => $person?->PersonID]);
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Payment | Civitas</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .card-in { animation: cardIn .5s cubic-bezier(.22,1,.36,1); }
        @keyframes cardIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn .35s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .spinner { border: 3px solid rgba(255,255,255,.25); border-top-color: #fff; border-radius: 50%; width: 18px; height: 18px; animation: spin .7s linear infinite; }
        .spinner-dark { border: 3px solid rgba(0,0,0,.12); border-top-color: #0f172a; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .brand-bg { background: radial-gradient(1200px 600px at top -100px left -100px, #1e293b 0%, #0f172a 45%, #020617 100%); }
        #payment-element { display: block; width: 100%; }
        .stripe-label { display: block; font-size: .75rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; margin-bottom: .5rem; }
    </style>
</head>
<body class="h-full min-h-screen brand-bg text-gray-900 antialiased">
    <div class="flex min-h-screen flex-col">
        <a href="{{ $backUrl }}" class="group mx-auto mt-6 inline-flex w-fit max-w-sm items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-medium text-gray-300 backdrop-blur transition-colors hover:bg-white/10 hover:text-white">
            <svg class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Citizen
        </a>

        <div class="flex flex-1 items-center justify-center p-4 py-10 sm:p-8">
            <div class="w-full max-w-4xl">

                <div class="mb-8 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur ring-1 ring-white/10">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.5 0-5 1.7-5 5.5C7 17.5 9.9 21 12 21s5-3.5 5-7.5c0-3.8-2.5-5.5-5-5.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8V3M8 5l4 3 4-3"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white sm:text-3xl">Secure Checkout</h1>
                    <p class="mt-1.5 text-sm text-gray-400">Powered by Stripe · Encrypted &amp; PCI-Compliant</p>
                </div>

                <div class="card-in grid gap-6 lg:grid-cols-5">

                    {{-- Order Summary --}}
                    <div class="lg:col-span-2 overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <div class="border-b border-gray-100 px-6 py-5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Order Summary</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800">{{ $personName }}</p>
                            <p class="text-xs text-gray-400">ID: {{ $person?->NationalID ?? '—' }}</p>
                        </div>

                        <div class="space-y-4 px-6 py-5">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $serviceType?->ServiceName ?? 'Service' }}</p>
                                <p class="text-xs text-gray-400">Request #{{ $serviceRequest->RequestID }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Service Fee</span>
                                <span class="text-sm font-semibold text-gray-800">${{ number_format($amount, 2) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">VAT</span>
                                <span class="text-sm text-gray-500">Included</span>
                            </div>

                            <div class="border-t border-gray-100 pt-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-semibold text-gray-700">Total</span>
                                    <span class="text-2xl font-bold text-gray-900">${{ number_format($amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Your card details are never stored on our servers.
                            </div>
                        </div>
                    </div>

                    {{-- Payment Panel --}}
                    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-2xl sm:p-8 card-in">

                        @if (!empty($alreadyProcessed) && empty($clientSecret))
                            {{-- Already processed / finalized by backend --}}
                            <div id="stateAlready" class="fade-in">
                                <div class="flex flex-col items-center justify-center py-14 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <h2 class="mt-4 text-lg font-bold text-gray-800">This request is already processed</h2>
                                    <p class="mt-1 max-w-sm text-sm text-gray-500">
                                        This service request has already been
                                        @if ($payment?->Status === 'succeeded') paid successfully.
                                        @else marked as {{ $serviceRequest->Status }}.
                                        @endif
                                    </p>
                                    <a href="{{ $backUrl }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                        Back to Citizen
                                    </a>
                                </div>
                            </div>

                        @elseif (empty($clientSecret))
                            {{-- Could not start payment --}}
                            <div id="stateSetupError" class="fade-in">
                                <div class="flex flex-col items-center justify-center py-14 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                                        <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h2 class="mt-4 text-lg font-bold text-gray-800">We couldn't start the payment</h2>
                                    <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $setupError ?? 'Please try again in a moment.' }}</p>
                                    <a href="{{ $backUrl }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                        Back to Citizen
                                    </a>
                                </div>
                            </div>

                        @else
                            {{-- Transaction states: confirm / processing / success / failed / cancelled --}}

                            {{-- Success (backend/webhook confirmed) --}}
                            <div id="stateSuccess" class="hidden fade-in">
                                <div class="flex flex-col items-center justify-center py-14 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <h2 class="mt-4 text-lg font-bold text-gray-800">Payment Successful</h2>
                                    <p class="mt-1 text-sm text-gray-500">Your payment has been confirmed and the request is now being processed.</p>

                                    <div class="mt-6 w-full rounded-xl border border-gray-100 bg-gray-50 p-5 text-left">
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-400">Receipt Number</span>
                                                <span id="receiptNumber" class="text-xs font-semibold font-mono text-gray-800"></span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-400">Service</span>
                                                <span class="text-xs font-semibold text-gray-800">{{ $serviceType?->ServiceName }}</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-400">Person</span>
                                                <span class="text-xs font-semibold text-gray-800">{{ $personName }}</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-400">Date</span>
                                                <span id="receiptDate" class="text-xs font-semibold text-gray-800"></span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-3">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-gray-500">Total Paid</span>
                                                    <span id="receiptAmount" class="text-lg font-bold text-gray-900">${{ number_format($amount, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="{{ $backUrl }}" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition-colors">
                                        Done
                                    </a>
                                </div>
                            </div>

                            {{-- Failed (card declined / auth error) --}}
                            <div id="stateFailed" class="hidden fade-in">
                                <div class="flex flex-col items-center justify-center py-14 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                                        <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h2 class="mt-4 text-lg font-bold text-gray-800">Payment Failed</h2>
                                    <p id="failedMessage" class="mt-1 max-w-sm text-sm text-gray-500">Your payment could not be completed.</p>
                                    <div class="mt-6 flex w-full gap-3">
                                        <button type="button" id="retryBtn" onclick="resetToPayment()"
                                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M4 9a8 8 0 106-14 8 8 0 016 11"/></svg>
                                            Try Again
                                        </button>
                                        <a href="{{ $backUrl }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                            Cancel &amp; Return
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Cancelled --}}
                            <div id="stateCancelled" class="hidden fade-in">
                                <div class="flex flex-col items-center justify-center py-14 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                                        <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                    <h2 class="mt-4 text-lg font-bold text-gray-800">Payment Cancelled</h2>
                                    <p class="mt-1 max-w-sm text-sm text-gray-500">You have cancelled the payment. No charge was made. You can restart the payment anytime.</p>
                                    <div class="mt-6 flex w-full gap-3">
                                        <button type="button" onclick="resetToPayment()" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition-colors">
                                            Resume Payment
                                        </button>
                                        <a href="{{ $backUrl }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                                            Cancel &amp; Return
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Processing / verifying (shown while waiting for backend) --}}
                            <div id="stateProcessing" class="hidden fade-in">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                                        <div class="spinner spinner-dark h-7 w-7"></div>
                                    </div>
                                    <p id="processingTitle" class="mt-4 text-sm font-semibold text-gray-800">Processing Payment…</p>
                                    <p id="processingSub" class="mt-1 text-xs text-gray-400">Verifying with our secure server. Please do not close this window.</p>
                                </div>
                            </div>

                            {{-- Confirm / Payment Element --}}
                            <div id="stateConfirm">
                                <h2 class="text-lg font-bold text-gray-800">Payment Details</h2>
                                <p class="mt-1 text-sm text-gray-500">Complete your payment securely using a card or a supported method.</p>

                                <div class="mt-6">
                                    <label class="stripe-label" for="payment-element">Payment Method</label>
                                    <div id="payment-element" class="rounded-xl border border-gray-200 p-4"></div>
                                </div>

                                <p id="paymentErrorMsg" class="mt-4 hidden rounded-lg bg-red-50 px-4 py-2.5 text-sm font-medium text-red-600"></p>

                                <div class="mt-6 flex gap-3">
                                    <button type="button" id="payBtn" onclick="confirmStripePayment()"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-50 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span id="payBtnLabel">Pay ${{ number_format($amount, 2) }}</span>
                                    </button>
                                    <a href="{{ $backUrl }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </a>
                                </div>

                                <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Secured by Stripe. Your card details are never stored on our servers.
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($clientSecret) && !empty($stripePublishableKey))
    <script>
        const STRIPE_KEY = {{ Js::from($stripePublishableKey) }};
        const CLIENT_SECRET = {{ Js::from($clientSecret) }};
        const REQUEST_ID = {{ Js::from($serviceRequest->RequestID) }};
        const RETURN_URL = {{ Js::from($returnUrl) }};
        const STATUS_URL = {{ Js::from($statusEndpoint) }};
        const AMOUNT = {{ $amount }};

        const stripe = Stripe(STRIPE_KEY);
        const stripeElements = stripe.elements({ clientSecret: CLIENT_SECRET, locale: 'auto' });
        const paymentElement = stripeElements.create('payment', { layout: { type: 'tabs' } });
        paymentElement.mount('#payment-element');

        const payBtn = document.getElementById('payBtn');
        const payBtnLabel = document.getElementById('payBtnLabel');
        payBtn.disabled = true;

        paymentElement.on('change', (event) => {
            payBtn.disabled = event.complete ? false : true;
            if (event.error) showPaymentError(event.error.message);
            else hidePaymentError();
        });

        function showPaymentError(message) {
            const el = document.getElementById('paymentErrorMsg');
            if (!message) { hidePaymentError(); return; }
            el.textContent = message;
            el.classList.remove('hidden');
        }
        function hidePaymentError() {
            const el = document.getElementById('paymentErrorMsg');
            el.classList.add('hidden');
            el.textContent = '';
        }

        function setState(name) {
            ['Success', 'Failed', 'Cancelled', 'Processing', 'Confirm'].forEach(s => {
                const el = document.getElementById('state' + s);
                if (el) el.classList.add('hidden');
            });
            const target = document.getElementById('state' + name);
            if (target) target.classList.remove('hidden');
        }

        function resetToPayment() {
            hidePaymentError();
            setState('Confirm');
            paymentElement.focus();
        }

        function showSuccess(data) {
            const now = new Date();
            document.getElementById('receiptNumber').textContent = data.receipt_number || ('RCPT-' + now.getTime());
            document.getElementById('receiptDate').textContent = now.toLocaleString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
            });
            document.getElementById('receiptAmount').textContent =
                (data.currency ? data.currency + ' ' : '$ ') +
                Number(data.amount || AMOUNT).toFixed(2);
            setState('Success');
        }

        async function waitForBackendConfirmation() {
            const start = Date.now();
            const timeout = 25000;
            while (Date.now() - start < timeout) {
                try {
                    const res = await fetch(STATUS_URL, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    if (data.is_finalized) return showSuccess(data);
                    if (data.payment_status === 'failed') {
                        document.getElementById('processingSub').textContent = 'Payment failed.';
                        return showFailed(data.fail_reason || 'Your payment could not be completed.');
                    }
                } catch (e) { /* transient, keep polling */ }
                await new Promise(r => setTimeout(r, 1800));
            }
            // Timeout: rely on the server page (authoritative) after redirect.
            document.getElementById('processingSub').textContent = 'Still verifying… taking you to the status page.';
            window.location.href = RETURN_URL;
        }

        function showFailed(reason) {
            document.getElementById('failedMessage').textContent = reason || 'Your payment could not be completed.';
            setState('Failed');
        }

        function isCancellation(error) {
            const code = error && (error.code || error.type || '');
            return /cancel/i.test(code) || error.message === 'Your payment was cancelled.';
        }

        async function confirmStripePayment() {
            if (payBtn.disabled) return;

            hidePaymentError();
            payBtn.disabled = true;
            setState('Processing');
            document.getElementById('processingTitle').textContent = 'Processing Payment…';
            document.getElementById('processingSub').textContent = 'Please do not close this window.';

            const { error, paymentIntent } = await stripe.confirmPayment({
                elements: stripeElements,
                confirmParams: { return_url: RETURN_URL },
                redirect: 'if_required',
            });

            if (error) {
                if (isCancellation(error)) {
                    setState('Cancelled');
                    return;
                }
                // Recoverable (e.g. declined / incomplete auth): return to form with message.
                if (error.code === 'card_declined' || error.message) {
                    document.getElementById('processingTitle').textContent = 'Payment Failed';
                    document.getElementById('processingSub').textContent = error.message || 'Please try a different payment method.';
                    setTimeout(() => showFailed(error.message || 'Your payment could not be completed.'), 600);
                    return;
                }
                showFailed(error.message || 'Your payment could not be completed.');
                return;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
                // Success is only finalized by the backend/webhook; confirm it server-side.
                document.getElementById('processingSub').textContent = 'Verifying with our secure server…';
                await waitForBackendConfirmation();
                return;
            }

            if (paymentIntent && paymentIntent.status === 'processing') {
                document.getElementById('processingTitle').textContent = 'Payment Processing';
                document.getElementById('processingSub').textContent = 'This may take a moment. We will confirm once verified.';
                await waitForBackendConfirmation();
                return;
            }

            if (paymentIntent && paymentIntent.status === 'requires_payment_method') {
                setState('Confirm');
                showPaymentError('Payment was not completed. Please try again.');
                payBtn.disabled = false;
                payBtnLabel.textContent = 'Pay $' + AMOUNT.toFixed(2);
                return;
            }

            // 3DS / redirect occurred: verify server-side.
            document.getElementById('processingSub').textContent = 'Verifying secure authentication…';
            await waitForBackendConfirmation();
        }
    </script>
    @elseif (!empty($clientSecret) && empty($stripePublishableKey))
    <script>
        // Publishable key is not configured: never throw the empty-string error,
        // show a clean configuration notice instead.
        setTimeout(() => {
            document.getElementById('paymentErrorMsg').classList.remove('hidden');
            document.getElementById('paymentErrorMsg').textContent =
                'Payment is temporarily unavailable (Stripe is not configured). Please contact support.';
            document.getElementById('payBtn').disabled = true;
            const label = document.getElementById('payBtnLabel');
            if (label) label.textContent = 'Unavailable';
        }, 0);
    </script>
    @endif
</body>
</html>
