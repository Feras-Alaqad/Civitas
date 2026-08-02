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
        background: rgba(201, 169, 110, 0.15);
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
        0%, 100% { box-shadow: 0 0 0 0 rgba(201, 169, 110, 0); }
        50% { box-shadow: 0 0 0 6px rgba(201, 169, 110, 0.08); }
    }
    .service-type-card.selected {
        animation: glowPulse 1.5s ease-in-out infinite;
    }

    .department-filter {
        display: flex;
        align-items: stretch;
        gap: 12px;
        overflow-x: auto;
        padding: 4px 4px 12px;
        scrollbar-width: thin;
        scrollbar-color: #d1d5db transparent;
    }
    .department-filter::-webkit-scrollbar { height: 6px; }
    .department-filter::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
    .department-filter::-webkit-scrollbar-track { background: transparent; }

    .department-tab {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 136px;
        padding: 18px 18px 14px;
        border-radius: 16px;
        border: 2px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        flex-shrink: 0;
        font-family: inherit;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .department-tab:hover {
        border-color: #eecb76;
        transform: translateY(-2px);
        box-shadow: 0 10px 24px -8px rgba(201, 169, 110, 0.3);
    }
    .department-tab::after {
        content: '';
        position: absolute;
        bottom: 9px;
        left: 50%;
        transform: translateX(-50%) scale(0);
        width: 5px;
        height: 5px;
        border-radius: 999px;
        background: #b8924f;
        transition: transform 0.3s ease;
    }
    .department-tab.active {
        border-color: #b8924f;
        background: linear-gradient(150deg, #fdf8ec 0%, #faf0d4 100%);
        color: #9a763e;
        transform: translateY(-3px);
        box-shadow: 0 14px 30px -8px rgba(184, 146, 79, 0.45);
    }
    .department-tab.active::after { transform: translateX(-50%) scale(1); }

    .department-tab .tab-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 13px;
        background: #f3f4f6;
        color: #6b7280;
        transition: all 0.3s ease;
    }
    .department-tab:hover .tab-icon {
        background: #faf0d4;
        color: #b8924f;
        transform: scale(1.08) rotate(-4deg);
    }
    .department-tab.active .tab-icon {
        background: linear-gradient(135deg, #b8924f, #c9a96e);
        color: #fff;
        box-shadow: 0 8px 18px -5px rgba(184, 146, 79, 0.6);
    }
    .department-tab .tab-label {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.25;
        text-align: center;
    }
    .department-tab .tab-count {
        position: absolute;
        top: 10px;
        right: 10px;
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #f3f4f6;
        color: #6b7280;
        transition: all 0.3s ease;
    }
    .department-tab.active .tab-count {
        background: #b8924f;
        color: #fff;
    }

    .dark .department-tab {
        background: rgba(255, 255, 255, 0.03);
        border-color: #374151;
        color: #9ca3af;
    }
    .dark .department-tab:hover {
        border-color: rgba(201, 169, 110, 0.5);
    }
    .dark .department-tab .tab-icon {
        background: rgba(255, 255, 255, 0.06);
        color: #9ca3af;
    }
    .dark .department-tab:hover .tab-icon {
        background: rgba(201, 169, 110, 0.2);
        color: #eecb76;
    }
    .dark .department-tab .tab-count {
        background: rgba(255, 255, 255, 0.08);
        color: #9ca3af;
    }
    .dark .department-tab.active {
        background: rgba(184, 146, 79, 0.15);
        border-color: #c9a96e;
        color: #eecb76;
        box-shadow: 0 16px 32px -10px rgba(201, 169, 110, 0.55);
    }
    .dark .department-tab.active .tab-icon {
        background: linear-gradient(135deg, #b8924f, #c9a96e);
        color: #fff;
    }
    .dark .department-tab.active .tab-count {
        background: #c9a96e;
        color: #fff;
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
                <div class="mb-4">
                    <div class="mb-3 flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Filter by Department</label>
                        <span id="serviceCount" class="text-[11px] font-medium text-gray-400 dark:text-gray-500"></span>
                    </div>
                    <div class="department-filter" id="departmentFilter">
                        @php
                        $icons = [
                            'all' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
                            'passport' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>',
                            'finance' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 4h16M8 13h.01M16 13h.01M8 6h8"/></svg>',
                            'legal' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m0-3H5.5a2 2 0 01-1.8-3l2.3-4h12l2.3 4a2 2 0 01-1.8 3H12zm0-15L5.5 6.5 12 8.5 18.5 6.5 12 3z"/></svg>',
                            'folder' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>',
                        ];
                        $deptIconKey = [
                            'قسم الجوازات' => 'passport',
                            'قسم المالية' => 'finance',
                            'الشؤون القانونية' => 'legal',
                            '—' => 'folder',
                        ];
                        @endphp
                        <button type="button" class="department-tab active" data-dept="all">
                            <span class="tab-icon">{!! $icons['all'] !!}</span>
                            <span class="tab-label">All Services</span>
                            <span class="tab-count">{{ $serviceTypes->count() }}</span>
                        </button>
                        @foreach($departments as $dept)
                        <button type="button" class="department-tab" data-dept="{{ $dept->DepartmentName }}">
                            <span class="tab-icon">{!! $icons[$deptIconKey[$dept->DepartmentName] ?? 'folder'] !!}</span>
                            <span class="tab-label">{{ $dept->DepartmentName }}</span>
                            <span class="tab-count">{{ $dept->ServiceCount }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="serviceTypeGrid">
                    @foreach($serviceTypes as $st)
                    <label class="service-type-card group relative flex cursor-pointer flex-col rounded-xl border-2 border-gray-200 bg-white p-4 transition-all duration-300 ease-out hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:border-brand-500/50 dark:hover:shadow-lg dark:hover:shadow-brand-500/5 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:shadow-lg has-[:checked]:shadow-brand-500/10 has-[:checked]:scale-[1.02] dark:has-[:checked]:border-brand-400/60 dark:has-[:checked]:bg-brand-500/10 dark:has-[:checked]:shadow-lg dark:has-[:checked]:shadow-brand-500/20"
                           data-service-id="{{ $st->ServiceTypeID }}"
                           data-service-name="{{ $st->ServiceName }}"
                           data-service-fees="{{ $st->Fees }}"
                           data-service-docs="{{ $st->RequiredDocuments ?? '' }}"
                           data-department="{{ $st->department?->DepartmentName ?? '—' }}">
                        <input type="radio" name="service_type_id" value="{{ $st->ServiceTypeID }}" class="sr-only" required>

                        <div class="check-indicator absolute -right-1.5 -top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-white shadow-lg shadow-brand-500/30 transition-all duration-300 scale-0 opacity-0 has-[:checked]:scale-100 has-[:checked]:opacity-100 dark:bg-brand-400 dark:shadow-brand-400/30">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800 transition-colors duration-300 group-has-[:checked]:text-brand-700 dark:text-white/90 dark:group-has-[:checked]:text-brand-300">{{ $st->ServiceName }}</span>
                            <span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-600 transition-all duration-300 group-has-[:checked]:bg-brand-500 group-has-[:checked]:text-white dark:bg-brand-500/15 dark:text-brand-400 dark:group-has-[:checked]:bg-brand-400 dark:group-has-[:checked]:text-white">${{ number_format($st->Fees, 2) }}</span>
                        </div>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $st->department?->DepartmentName ?? '—' }}</span>
                        @if($st->RequiredDocuments)
                        <span class="mt-2 text-[11px] text-gray-400 dark:text-gray-500 line-clamp-2">{{ $st->RequiredDocuments }}</span>
                        @endif
                    </label>
                    @endforeach
                </div>
                <div id="noServicesMsg" class="hidden rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-400 dark:border-gray-700">
                    No services found for this department.
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
                        <p class="text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold text-brand-600 dark:text-brand-400">Click to upload</span> or drag and drop</p>
                        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">PDF, JPG, PNG up to 10MB each</p>
                        <input type="file" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                    </div>
                    <div id="fileList" class="mt-3 space-y-2"></div>
                    <p id="fileErrorMsg" class="mt-2 hidden items-center gap-1.5 text-xs font-medium text-red-500 dark:text-red-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Please upload at least one document before proceeding to payment.
                    </p>
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
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/15">
                        <svg class="h-7 w-7 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <svg class="mb-4 h-12 w-12 text-brand-600 animate-spin" fill="none" viewBox="0 0 24 24">
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

const departmentFilter = document.getElementById('departmentFilter');
const departmentTabs = Array.from(departmentFilter.querySelectorAll('.department-tab'));
const serviceCards = Array.from(document.querySelectorAll('.service-type-card'));
const noServicesMsg = document.getElementById('noServicesMsg');
const serviceCount = document.getElementById('serviceCount');

function getActiveDepartment() {
    const active = departmentTabs.find(t => t.classList.contains('active'));
    return active ? active.dataset.dept : 'all';
}

function applyDepartmentFilter() {
    const selected = getActiveDepartment();
    let visibleCount = 0;

    serviceCards.forEach(card => {
        const match = selected === 'all' || card.dataset.department === selected;
        card.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });

    if (selectedService && selected !== 'all' && selectedService.department !== selected) {
        resetServiceSelection();
    }

    noServicesMsg.classList.toggle('hidden', visibleCount > 0);
    serviceCount.textContent = visibleCount + ' of ' + serviceCards.length + ' services';
}

function resetServiceSelection() {
    selectedService = null;
    serviceCards.forEach(c => c.classList.remove('selected'));
    const radio = document.querySelector('input[name="service_type_id"]:checked');
    if (radio) radio.checked = false;
    document.getElementById('serviceDetails').classList.add('hidden');
    document.getElementById('submitSection').classList.add('hidden');
}

departmentTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        if (tab.classList.contains('active')) return;
        departmentTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        applyDepartmentFilter();
    });
});

applyDepartmentFilter();

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
fileDropZone.addEventListener('dragover', e => { e.preventDefault(); fileDropZone.classList.add('border-brand-400', 'bg-brand-50'); });
fileDropZone.addEventListener('dragleave', () => { fileDropZone.classList.remove('border-brand-400', 'bg-brand-50'); });
fileDropZone.addEventListener('drop', e => {
    e.preventDefault();
    fileDropZone.classList.remove('border-brand-400', 'bg-brand-50');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', () => handleFiles(fileInput.files));

function clearFileError() {
    document.getElementById('fileErrorMsg').classList.add('hidden');
    document.getElementById('fileErrorMsg').classList.remove('flex');
    fileDropZone.classList.remove('border-red-400', 'bg-red-50', 'dark:border-red-500/50', 'dark:bg-red-500/10');
}

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(file.name + ' exceeds 10MB limit.');
            return;
        }
        uploadedFiles.push(file);
    });
    renderFileList();
    if (uploadedFiles.length > 0) clearFileError();
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
    if (uploadedFiles.length === 0) clearFileError();
}

function showPaymentModal() {
    if (!selectedService) {
        alert('Please select a service type first.');
        return;
    }

    if (uploadedFiles.length === 0) {
        const errorMsg = document.getElementById('fileErrorMsg');
        errorMsg.classList.remove('hidden');
        errorMsg.classList.add('flex');
        fileDropZone.classList.add('border-red-400', 'bg-red-50', 'dark:border-red-500/50', 'dark:bg-red-500/10');
        document.getElementById('fileInput').scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #b8924f; padding-bottom: 10px; }
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
