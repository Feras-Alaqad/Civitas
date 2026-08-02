@extends('layouts.admin')

@php $page = 'service'; $pageName = 'Service Application'; @endphp

@section('title', 'Service Application')

@section('content')
<style>
    .service-type-card {
        position: relative;
        overflow: hidden;
    }
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.15);
        transform: scale(0);
        animation: rippleAnim 0.6s ease-out;
        pointer-events: none;
        z-index: 0;
    }
    @keyframes rippleAnim {
        to { transform: scale(4); opacity: 0; }
    }
    @keyframes cardPop {
        0% { transform: scale(1); }
        40% { transform: scale(0.97); }
        70% { transform: scale(1.03); }
        100% { transform: scale(1); }
    }
    @keyframes checkBounce {
        0% { transform: scale(0) rotate(-45deg); }
        50% { transform: scale(1.3) rotate(0deg); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1) rotate(0deg); }
    }
    @keyframes slideDown {
        0% { opacity: 0; transform: translateY(-12px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes glowPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        50% { box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.08); }
    }
    .service-type-card.selected {
        animation: glowPulse 1.5s ease-in-out infinite;
    }
</style>
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Header --}}
    <div class="flex items-center gap-3 px-5 py-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Add Service</h3>
        <a href="{{ route('admin.citizens', ['person_id' => $person->PersonID]) }}"
           class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- Person Info --}}
    <div class="border-t border-gray-100 dark:border-gray-800 px-5 py-4">
        <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600 dark:bg-blue-900/50 dark:text-blue-400 uppercase">
                {{ substr($person->FullName, 0, 1) }}
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $person->FullName }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $person->NationalID ?? '—' }} | {{ $person->GovernorateName ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Service Form --}}
    <div class="border-t border-gray-100 dark:border-gray-800 px-5 py-5">
        <form id="serviceForm" class="space-y-5">

            <input type="hidden" name="person_id" value="{{ $person->PersonID }}">

            {{-- Step 1: Select Service Type --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Select Service Type</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="serviceTypeGrid">
                    @foreach($serviceTypes as $st)
                    <label class="service-type-card group relative flex cursor-pointer flex-col rounded-xl border-2 border-gray-200 bg-white p-4 transition-all duration-300 ease-out hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-blue-500/50 dark:hover:shadow-lg dark:hover:shadow-blue-500/5 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:shadow-lg has-[:checked]:shadow-blue-500/10 has-[:checked]:scale-[1.02] dark:has-[:checked]:border-blue-400/60 dark:has-[:checked]:bg-blue-500/10 dark:has-[:checked]:shadow-lg dark:has-[:checked]:shadow-blue-500/20"
                           data-service-id="{{ $st->ServiceTypeID }}"
                           data-service-name="{{ $st->ServiceName }}"
                           data-service-fees="{{ $st->Fees }}"
                           data-service-docs="{{ $st->RequiredDocuments ?? '' }}"
                           data-department="{{ $st->department?->DepartmentName ?? '—' }}">
                        <input type="radio" name="service_type_id" value="{{ $st->ServiceTypeID }}" class="sr-only" required>

                        <div class="check-indicator absolute -right-1.5 -top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 text-white shadow-lg shadow-blue-500/30 transition-all duration-300 scale-0 opacity-0 has-[:checked]:scale-100 has-[:checked]:opacity-100 dark:bg-blue-400 dark:shadow-blue-400/30">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800 transition-colors duration-300 group-has-[:checked]:text-blue-700 dark:text-white/90 dark:group-has-[:checked]:text-blue-300">{{ $st->ServiceName }}</span>
                            <span class="rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600 transition-all duration-300 group-has-[:checked]:bg-blue-500 group-has-[:checked]:text-white dark:bg-blue-500/15 dark:text-blue-400 dark:group-has-[:checked]:bg-blue-400 dark:group-has-[:checked]:text-white">${{ number_format($st->Fees, 2) }}</span>
                        </div>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $st->department?->DepartmentName ?? '—' }}</span>
                        @if($st->RequiredDocuments)
                        <span class="mt-2 text-[11px] text-gray-400 dark:text-gray-500 line-clamp-2">{{ $st->RequiredDocuments }}</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Step 2: Service Details (shown after selection) --}}
            <div id="serviceDetails" class="hidden space-y-4">
                {{-- Required Documents Info --}}
                <div id="docsInfo" class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                    <div class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">Required Documents</p>
                            <ul id="docsList" class="mt-1 list-disc list-inside text-xs text-amber-600 dark:text-amber-400/80"></ul>
                        </div>
                    </div>
                </div>

                {{-- Fee Summary --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Service Fee</span>
                        <span id="feeDisplay" class="text-lg font-bold text-gray-800 dark:text-white/90">$0.00</span>
                    </div>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Upload Documents</label>
                    <div id="fileDropZone" class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 py-6 transition-colors hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700/50 dark:hover:bg-gray-700">
                        <svg class="mb-2 h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold text-blue-600 dark:text-blue-400">Click to upload</span> or drag and drop</p>
                        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">PDF, JPG, PNG up to 10MB each</p>
                        <input type="file" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                    </div>
                    <div id="fileList" class="mt-3 space-y-2"></div>
                </div>
            </div>

            {{-- Submit --}}
            <div id="submitSection" class="hidden" style="animation: slideDown 0.4s ease-out">
                <button type="button" id="submitBtn" onclick="showPaymentModal()"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600 hover:shadow-xl hover:shadow-brand-500/30 hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Proceed to Payment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Payment Confirmation Modal --}}
<div id="paymentModal"
     class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/45"
     style="display: none;">
    <div class="w-[480px] max-w-[92%] rounded-2xl bg-white shadow-2xl dark:bg-gray-800">

        {{-- Step 1: Confirm Payment --}}
        <div id="paymentStepConfirm">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Confirm Payment</h3>
                <button onclick="closePaymentModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-5 flex flex-col items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-500/15">
                        <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <p id="paymentModalService" class="text-sm font-semibold text-gray-800 dark:text-white/90"></p>
                        <p id="paymentModalPerson" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"></p>
                    </div>
                    <div class="rounded-xl bg-gray-50 px-6 py-3 dark:bg-gray-700/50">
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Amount to Pay</p>
                        <p id="paymentModalAmount" class="text-2xl font-bold text-gray-800 dark:text-white/90 text-center"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/30 mb-5">
                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Secure payment via PayPal Sandbox</span>
                </div>

                {{-- PayPal Button Container --}}
                <div id="paypal-button-container" class="mb-3"></div>

                <button onclick="closePaymentModal()" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
            </div>
        </div>

        {{-- Step 2: Processing --}}
        <div id="paymentStepProcessing" class="hidden">
            <div class="flex flex-col items-center justify-center py-16 px-6">
                <svg class="mb-4 h-12 w-12 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Processing Payment...</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Please do not close this window</p>
            </div>
        </div>

        {{-- Step 3: Receipt --}}
        <div id="paymentStepReceipt" class="hidden">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Payment Receipt</h3>
                <button onclick="closePaymentModal(); window.location='{{ route('admin.citizens', ['person_id' => $person->PersonID]) }}'"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="flex flex-col items-center gap-3 mb-5">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-500/15">
                        <svg class="h-7 w-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-green-600 dark:text-green-400">Payment Successful</p>
                </div>

                <div id="receiptContent" class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 dark:text-gray-500">Receipt Number</span>
                            <span id="receiptNumber" class="text-xs font-semibold font-mono text-gray-800 dark:text-white/90"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 dark:text-gray-500">Service</span>
                            <span id="receiptService" class="text-xs font-semibold text-gray-800 dark:text-white/90"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 dark:text-gray-500">Person</span>
                            <span id="receiptPerson" class="text-xs font-semibold text-gray-800 dark:text-white/90"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 dark:text-gray-500">Date</span>
                            <span id="receiptDate" class="text-xs font-semibold text-gray-800 dark:text-white/90"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 dark:text-gray-500">Payment Method</span>
                            <span class="text-xs font-semibold text-gray-800 dark:text-white/90">PayPal</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Paid</span>
                                <span id="receiptAmount" class="text-lg font-bold text-gray-800 dark:text-white/90"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <button onclick="printReceipt()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print Receipt
                    </button>
                    <button onclick="closePaymentModal(); window.location='{{ route('admin.citizens', ['person_id' => $person->PersonID]) }}'"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PayPal SDK --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD&intent=capture" data-sdk-integration-source="button-factory"></script>

<script>
const PERSON_ID = '{{ $person->PersonID }}';
const PERSON_NAME = '{{ addslashes($person->FullName) }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

let selectedService = null;
let uploadedFiles = [];

document.querySelectorAll('.service-type-card').forEach(card => {
    card.addEventListener('click', function(e) {
        const radio = this.querySelector('input[type="radio"]');

        document.querySelectorAll('.service-type-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');

        createRipple(e, this);

        this.style.animation = 'cardPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
        setTimeout(() => this.style.animation = '', 400);

        const check = this.querySelector('.check-indicator');
        if (check) {
            check.style.animation = 'checkBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            setTimeout(() => check.style.animation = '', 500);
        }

        selectedService = {
            id: radio.value,
            name: this.dataset.serviceName,
            fees: parseFloat(this.dataset.serviceFees),
            docs: this.dataset.serviceDocs,
            department: this.dataset.department,
        };

        const details = document.getElementById('serviceDetails');
        details.classList.remove('hidden');
        details.style.animation = 'slideDown 0.4s ease-out';
        setTimeout(() => details.style.animation = '', 400);

        document.getElementById('submitSection').classList.remove('hidden');

        document.getElementById('feeDisplay').textContent = '$' + selectedService.fees.toFixed(2);

        const docsList = document.getElementById('docsList');
        if (selectedService.docs) {
            const docs = selectedService.docs.split(',').map(d => d.trim()).filter(d => d);
            docsList.innerHTML = docs.map(d => `<li>${d}</li>`).join('');
            document.getElementById('docsInfo').classList.remove('hidden');
        } else {
            document.getElementById('docsInfo').classList.add('hidden');
        }
    });
});

function createRipple(event, element) {
    const existing = element.querySelector('.ripple-effect');
    if (existing) existing.remove();

    const circle = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    circle.style.width = circle.style.height = size + 'px';
    circle.style.left = x + 'px';
    circle.style.top = y + 'px';
    circle.classList.add('ripple-effect');

    element.appendChild(circle);
    setTimeout(() => circle.remove(), 600);
}

const fileInput = document.getElementById('fileInput');
const fileDropZone = document.getElementById('fileDropZone');

fileDropZone.addEventListener('click', () => fileInput.click());
fileDropZone.addEventListener('dragover', e => { e.preventDefault(); fileDropZone.classList.add('border-blue-400', 'bg-blue-50'); });
fileDropZone.addEventListener('dragleave', () => { fileDropZone.classList.remove('border-blue-400', 'bg-blue-50'); });
fileDropZone.addEventListener('drop', e => {
    e.preventDefault();
    fileDropZone.classList.remove('border-blue-400', 'bg-blue-50');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', () => handleFiles(fileInput.files));

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(file.name + ' exceeds 10MB limit.');
            return;
        }
        uploadedFiles.push(file);
    });
    renderFileList();
}

function renderFileList() {
    const container = document.getElementById('fileList');
    container.innerHTML = uploadedFiles.map((file, i) => `
        <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="flex-1 text-xs font-medium text-gray-700 dark:text-gray-300 truncate">${file.name}</span>
            <span class="text-[11px] text-gray-400 dark:text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
            <button type="button" onclick="removeFile(${i})" class="shrink-0 p-0.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `).join('');
}

function removeFile(index) {
    uploadedFiles.splice(index, 1);
    renderFileList();
}

function showPaymentModal() {
    if (!selectedService) {
        alert('Please select a service type first.');
        return;
    }

    document.getElementById('paymentModalService').textContent = selectedService.name;
    document.getElementById('paymentModalPerson').textContent = PERSON_NAME;
    document.getElementById('paymentModalAmount').textContent = '$' + selectedService.fees.toFixed(2);

    document.getElementById('paymentStepConfirm').classList.remove('hidden');
    document.getElementById('paymentStepProcessing').classList.add('hidden');
    document.getElementById('paymentStepReceipt').classList.add('hidden');

    document.getElementById('paymentModal').style.display = 'flex';

    initPayPalButton();
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function initPayPalButton() {
    const container = document.getElementById('paypal-button-container');
    container.innerHTML = '';

    if (typeof paypal === 'undefined') {
        container.innerHTML = `
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-center dark:border-amber-500/20 dark:bg-amber-500/10">
                <p class="text-xs text-amber-600 dark:text-amber-400">PayPal SDK not loaded. Configure your PayPal Client ID in <code>.env</code>.</p>
                <button type="button" onclick="simulatePayment()" class="mt-3 rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700 transition-colors">
                    Simulate Payment (Dev Mode)
                </button>
            </div>
        `;
        return;
    }

    paypal.Buttons({
        style: {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'pay',
        },
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: selectedService.fees.toFixed(2),
                    },
                }],
            });
        },
        onApprove: function(data, actions) {
            showProcessingStep();
            return actions.order.capture().then(function(details) {
                submitServiceRequest(data.orderID, details);
            });
        },
        onError: function(err) {
            console.error('PayPal error:', err);
            alert('Payment failed. Please try again.');
            closePaymentModal();
        },
        onCancel: function() {
            closePaymentModal();
        }
    }).render('#paypal-button-container');
}

function simulatePayment() {
    showProcessingStep();

    setTimeout(() => {
        submitServiceRequest('SIMULATED-' + Date.now(), {
            id: 'SIMULATED-' + Date.now(),
            status: 'COMPLETED',
            payer: { payer_id: 'SIMULATED_PAYER' },
            purchase_units: [{
                payments: {
                    captures: [{
                        id: 'SIMULATED-CAPTURE-' + Date.now(),
                        amount: { value: selectedService.fees.toFixed(2), currency_code: 'USD' },
                    }],
                },
            }],
        });
    }, 1500);
}

function showProcessingStep() {
    document.getElementById('paymentStepConfirm').classList.add('hidden');
    document.getElementById('paymentStepProcessing').classList.remove('hidden');
}

function submitServiceRequest(orderID, paymentDetails) {
    const formData = new FormData();
    formData.append('person_id', PERSON_ID);
    formData.append('service_type_id', selectedService.id);
    formData.append('payment_method', 'paypal');

    uploadedFiles.forEach(file => {
        formData.append('documents[]', file);
    });

    fetch('{{ route("admin.service.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to create service request');

        return fetch('{{ route("admin.service.paypal-capture-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                order_id: orderID,
                request_id: data.request_id,
            }),
        });
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) throw new Error(data.error);

        showReceiptStep(data, orderID);
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error: ' + err.message);
        closePaymentModal();
    });
}

function showReceiptStep(paymentData, orderID) {
    document.getElementById('paymentStepProcessing').classList.add('hidden');
    document.getElementById('paymentStepReceipt').classList.remove('hidden');

    const receiptNumber = paymentData.receipt_number || 'RCPT-' + Date.now();
    const amount = paymentData.amount || selectedService.fees.toFixed(2);
    const now = new Date();

    document.getElementById('receiptNumber').textContent = receiptNumber;
    document.getElementById('receiptService').textContent = selectedService.name;
    document.getElementById('receiptPerson').textContent = PERSON_NAME;
    document.getElementById('receiptDate').textContent = now.toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
    document.getElementById('receiptAmount').textContent = '$' + parseFloat(amount).toFixed(2);
}

function printReceipt() {
    const content = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt</title>
            <style>
                body { font-family: 'Segoe UI', sans-serif; padding: 20px; color: #333; }
                .flex { display: flex; justify-content: space-between; margin: 8px 0; }
                .text-xs { font-size: 12px; }
                .text-sm { font-size: 14px; }
                .text-lg { font-size: 18px; }
                .font-semibold { font-weight: 600; }
                .font-bold { font-weight: 700; }
                .text-gray-400 { color: #9ca3af; }
                .text-gray-800 { color: #1f2937; }
                .border-t { border-top: 1px solid #e5e7eb; margin-top: 12px; padding-top: 12px; }
                .font-mono { font-family: monospace; }
                h2 { text-align: center; margin-bottom: 20px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Civitas - Payment Receipt</h2>
            </div>
            ${content}
            <script>window.onload=function(){window.print();}<\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

document.getElementById('paymentModal').addEventListener('click', function(e) {
    if (e.target === this) closePaymentModal();
});
</script>
@endsection
