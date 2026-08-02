@extends('layouts.admin')

@php $page = 'audit-logs'; $pageName = 'Audit Logs'; @endphp

@section('title', 'Audit Logs')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Title --}}
    <div class="px-5 pt-5 pb-4 border-b border-gray-100 dark:border-gray-800">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex shrink-0 items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Audit Logs</h3>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-500 dark:bg-brand-500/15 dark:text-brand-500">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    {{ $auditLogs->total() }}
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
            <a href="{{ route('admin.audit-logs.export', request()->only(['search', 'action_type', 'status', 'date_from', 'date_to', 'user_id'])) }}"
               class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-500 bg-brand-50 px-4 py-2 text-xs font-medium text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/15 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="px-5 pt-4 pb-4">
        <form method="GET" action="{{ route('admin.audit-logs') }}">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                {{-- Search --}}
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description, user, person..."
                               class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all"/>
                    </div>
                </div>

                {{-- Action Type --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Action Type</label>
                    <select name="action_type" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all">
                        <option value="">All Actions</option>
                        @foreach($distinctActions as $action)
                            <option value="{{ $action }}" {{ request('action_type') === $action ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Request Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all">
                        <option value="">All Statuses</option>
                        @foreach(['Pending', 'In Progress', 'Completed', 'Cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
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

            {{-- User + Buttons --}}
            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="w-full sm:w-48">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">User</label>
                    <select name="user_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all">
                        <option value="">All Users</option>
                        @foreach($distinctUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') === $u->id ? 'selected' : '' }}>{{ $u->Username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 active:bg-brand-700 transition-colors shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'action_type', 'status', 'date_from', 'date_to', 'user_id']))
                    <a href="{{ route('admin.audit-logs') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                    @endif
                </div>
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
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Timestamp</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Person</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Service</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Req. Status</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</span></th>
                        <th class="py-3.5 px-4 text-left"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($auditLogs as $log)
                    @php
                        $actionBadge = match($log->ActionType) {
                            'Service Request Created' => 'bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-blue-400',
                            'Payment Completed' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                            'Login' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                        };
                        $reqBadge = match($log->RequestStatus) {
                            'Completed' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                            'Pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                            'Cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                            'In Progress' => 'bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-blue-400',
                            default => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                        };
                        $payBadge = match($log->PaymentStatus) {
                            'Completed' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                            'Pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                            'Failed' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                            default => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors cursor-pointer"
                        data-ref="{{ $log->LogID }}">
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($log->Timestamp)->format('d M Y, h:i A') }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                @if($log->avatar)
                                <img src="{{ asset($log->avatar) }}" alt="{{ $log->Username }}" class="h-6 w-6 rounded-full object-cover">
                                @else
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-[10px] font-bold text-purple-600 dark:bg-purple-900/50 dark:text-purple-400 uppercase">{{ substr($log->Username, 0, 1) }}</span>
                                @endif
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $log->Username }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $actionBadge }}">{{ $log->ActionType }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-[200px] block">{{ $log->Description ?? '—' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->FullName)
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $log->FullName }}</span>
                            @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->ServiceName)
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $log->ServiceName }}</span>
                            @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->RequestStatus)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $reqBadge }}">{{ $log->RequestStatus }}</span>
                            @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->PaymentStatus)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $payBadge }}">{{ $log->PaymentStatus }}</span>
                            @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->Amount)
                            <span class="text-sm text-gray-500 dark:text-gray-400">${{ number_format($log->Amount, 2) }}</span>
                            @else
                            <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-xs font-mono text-gray-400 dark:text-gray-500">{{ $log->IPAddress ?? '—' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="text-sm text-gray-400 dark:text-gray-500">No audit logs found.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $auditLogs->onEachSide(1)->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>

@if(session('error'))
<div class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-600 dark:bg-red-500/15 dark:text-red-400">
    {{ session('error') }}
</div>
@endif
@endsection

@push('modals')
<div id="auditTrailModal"
     class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/50 pt-24 pb-8 px-6 sm:px-10 md:pl-72 md:pr-16 backdrop-blur-sm"
     style="display: none;">
    <div class="flex w-full max-w-xl flex-col rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900" style="max-height: 75vh;">

        <div id="auditTrailBody" class="flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">

            <div id="auditTrailLoading" class="flex flex-col items-center justify-center py-16">
                <svg class="h-8 w-8 text-[#465fff] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <p class="mt-3 text-xs font-medium text-gray-500 dark:text-gray-400">Loading audit trail...</p>
            </div>

            <div id="auditTrailContent" class="hidden"></div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function() {
    var actionIcons = {
        'Service Request Created': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', cls: 'text-brand-500 bg-brand-50 dark:bg-brand-500/10 dark:text-blue-400' },
        'Payment Completed': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', cls: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400' },
        'Payment Pending': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>', cls: 'text-amber-600 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-400' },
        'Login': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>', cls: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 dark:text-indigo-400' },
        'Logout': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>', cls: 'text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400' },
        'Failed Login Attempt': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>', cls: 'text-red-600 bg-red-50 dark:bg-red-500/10 dark:text-red-400' },
        'Register': { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>', cls: 'text-violet-600 bg-violet-50 dark:bg-violet-500/10 dark:text-violet-400' },
    };
    var defaultIcon = { svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', cls: 'text-gray-400 bg-gray-100 dark:bg-gray-700 dark:text-gray-400' };

    function sectionHeader(iconSvg, iconCls, title) {
        var h = '<div class="flex items-center gap-2.5 mb-3">';
        h += '<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ' + iconCls + '">';
        h += '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' + iconSvg + '</svg>';
        h += '</div>';
        h += '<h4 class="text-sm font-bold text-gray-800 dark:text-white">' + title + '</h4>';
        h += '</div>';
        return h;
    }

    function infoRow(label, value, mono) {
        return '<div class="flex items-start justify-between gap-4 py-2 border-b border-gray-100/60 dark:border-gray-700/40 last:border-0">' +
            '<span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 shrink-0">' + label + '</span>' +
            '<span class="text-[11px] font-semibold text-gray-800 dark:text-white text-right break-words min-w-0' + (mono ? ' font-mono' : '') + '">' + (value || '—') + '</span>' +
            '</div>';
    }

    function openAuditTrail(referenceId) {
        var modal = document.getElementById('auditTrailModal');
        var loading = document.getElementById('auditTrailLoading');
        var content = document.getElementById('auditTrailContent');
        if (!modal) return;

        loading.classList.remove('hidden');
        content.classList.add('hidden');
        modal.style.display = 'flex';

        fetch('{{ route("admin.audit-logs.audit-trail", ["referenceId" => "__ID__"]) }}'.replace('__ID__', referenceId))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var html = '';

                if (data.details) {
                    var d = data.details;

                    var detailIconMap = {
                        'PersonSearch': { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>', color: 'text-brand-500 bg-brand-50 dark:bg-brand-500/10 dark:text-blue-400' },
                        'Login': { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>', color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 dark:text-indigo-400' },
                        'Login (Google)': { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>', color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 dark:text-indigo-400' },
                        'Logout': { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>', color: 'text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400' },
                        'Failed Login Attempt': { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>', color: 'text-red-600 bg-red-50 dark:bg-red-500/10 dark:text-red-400' },
                        'Register': { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>', color: 'text-violet-600 bg-violet-50 dark:bg-violet-500/10 dark:text-violet-400' },
                    };
                    var di = detailIconMap[d.ActionType] || { icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', color: 'text-gray-400 bg-gray-100 dark:bg-gray-700 dark:text-gray-400' };

                    html += '<div class="mb-5">';
                    html += '<div class="flex items-center gap-2.5 mb-3">';
                    html += '<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ' + di.color + '">';
                    html += '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' + di.icon + '</svg>';
                    html += '</div>';
                    html += '<h4 class="text-sm font-bold text-gray-800 dark:text-white">' + d.ActionType + '</h4>';
                    html += '</div>';
                    html += '</div>';

                    html += '<div class="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/50 dark:bg-gray-800/40">';
                    html += infoRow('Action', d.ActionType);
                    html += infoRow('User', d.Username);
                    html += infoRow('IP Address', d.IPAddress, true);
                    html += infoRow('Date & Time', d.Timestamp);
                    if (d.Description) {
                        html += infoRow('Details', d.Description);
                    }
                    html += '</div></div>';
                }

                if (data.request) {
                    var req = data.request;

                    html += '<div class="mb-5">';
                    html += sectionHeader('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>', 'text-[#465fff] bg-[#465fff]/10', 'Request Summary');

                    html += '<div class="rounded-xl border border-gray-100 bg-gray-50/80 p-4 dark:border-gray-700/50 dark:bg-gray-800/40">';
                    html += infoRow('Person', req.FullName);
                    html += infoRow('National ID', req.NationalID, true);
                    html += infoRow('Department', req.DepartmentName);
                    html += infoRow('Status', req.Status);
                    html += infoRow('Fees', req.Fees ? '$' + parseFloat(req.Fees).toFixed(2) : null);
                    html += infoRow('Date', req.RequestDate);
                    html += infoRow('Phone', req.Phone, true);
                    html += infoRow('Email', req.Email);
                    html += '</div></div>';
                }

                if (data.timeline && data.timeline.length > 0) {
                    html += '<div class="mb-5">';
                    html += sectionHeader('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'text-[#465fff] bg-[#465fff]/10', 'Activity Timeline');

                    html += '<div class="rounded-xl border border-gray-100 dark:border-gray-700/50 overflow-x-auto">';
                    html += '<table class="w-full min-w-[400px]"><thead><tr class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-700/50 dark:bg-gray-800/40">';
                    html += '<th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>';
                    html += '<th class="hidden sm:table-cell px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>';
                    html += '<th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">By</th>';
                    html += '<th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>';
                    html += '</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';

                    var actionBadgeMap = {
                        'Service Request Created': 'bg-brand-50 text-blue-700 ring-1 ring-blue-200/60 dark:bg-brand-500/10 dark:text-blue-400',
                        'Payment Completed': 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/60 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'Payment Completed (Simulated)': 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/60 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'Payment Pending': 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/60 dark:bg-amber-500/10 dark:text-amber-400',
                        'Login': 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200/60 dark:bg-indigo-500/10 dark:text-indigo-400',
                        'Login (Google)': 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200/60 dark:bg-indigo-500/10 dark:text-indigo-400',
                        'Logout': 'bg-gray-100 text-gray-600 ring-1 ring-gray-200/60 dark:bg-gray-700 dark:text-gray-400',
                        'Failed Login Attempt': 'bg-red-50 text-red-700 ring-1 ring-red-200/60 dark:bg-red-500/10 dark:text-red-400',
                        'Register': 'bg-violet-50 text-violet-700 ring-1 ring-violet-200/60 dark:bg-violet-500/10 dark:text-violet-400',
                    };

                    data.timeline.forEach(function(entry) {
                        var ab = actionBadgeMap[entry.ActionType] || 'bg-gray-100 text-gray-600 ring-1 ring-gray-200/60 dark:bg-gray-700 dark:text-gray-400';
                        html += '<tr>';
                        html += '<td class="px-3 py-2.5 text-center"><span class="inline-flex items-center justify-center rounded-full px-2.5 py-1 text-[10px] font-semibold leading-none ' + ab + '">' + entry.ActionType + '</span></td>';
                        html += '<td class="hidden sm:table-cell px-3 py-2.5 text-[11px] text-gray-500 dark:text-gray-400 max-w-[160px] truncate">' + (entry.Description || '—') + '</td>';
                        html += '<td class="px-3 py-2.5">';
                        html += '<span class="inline-flex items-center gap-1.5">';
                        html += '<span class="h-4 w-4 rounded-full bg-[#465fff]/10 text-[#465fff] flex items-center justify-center text-[8px] font-bold shrink-0">' + entry.Username.charAt(0).toUpperCase() + '</span>';
                        html += '<span class="text-[11px] font-medium text-gray-700 dark:text-gray-300 truncate">' + entry.Username + '</span>';
                        html += '</span></td>';
                        html += '<td class="px-3 py-2.5 text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">' + entry.Timestamp + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div></div>';
                }

                if (data.payments && data.payments.length > 0) {
                    html += '<div class="mb-5">';
                    html += sectionHeader('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'text-[#465fff] bg-[#465fff]/10', 'Payments');

                    html += '<div class="rounded-xl border border-gray-100 dark:border-gray-700/50 overflow-x-auto">';
                    html += '<table class="w-full min-w-[350px]"><thead><tr class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-700/50 dark:bg-gray-800/40">';
                    html += '<th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>';
                    html += '<th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>';
                    html += '<th class="hidden sm:table-cell px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt</th>';
                    html += '<th class="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>';
                    html += '</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';

                    var payBadgeMap = {
                        'Completed': 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/60 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'Pending': 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/60 dark:bg-amber-500/10 dark:text-amber-400',
                        'Failed': 'bg-red-50 text-red-700 ring-1 ring-red-200/60 dark:bg-red-500/10 dark:text-red-400',
                    };

                    data.payments.forEach(function(pay) {
                        var pb = payBadgeMap[pay.Status] || 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
                        html += '<tr>';
                        html += '<td class="px-3 py-2.5 text-[11px] text-gray-600 dark:text-gray-400 whitespace-nowrap">' + (pay.PaymentDate || '—') + '</td>';
                        html += '<td class="px-3 py-2.5 text-[11px] font-semibold text-gray-800 dark:text-white">$' + parseFloat(pay.Amount).toFixed(2) + '</td>';
                        html += '<td class="hidden sm:table-cell px-3 py-2.5 text-[10px] font-mono text-gray-400 dark:text-gray-500">' + (pay.ReceiptNumber || '—') + '</td>';
                        html += '<td class="px-3 py-2.5"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ' + pb + '">' + pay.Status + '</span></td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div></div>';
                }

                if (data.attachments && data.attachments.length > 0) {
                    html += '<div class="mb-5">';
                    html += sectionHeader('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 8.586a2 2 0 102.828 2.828l6.414-8.586a4 4 0 00-5.656-5.656l-6.415 8.585a6 6 0 108.486 8.486L20.5 13"/>', 'text-[#465fff] bg-[#465fff]/10', 'Attachments');

                    html += '<div class="flex flex-col gap-2">';
                    data.attachments.forEach(function(att) {
                        html += '<div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/80 px-3 py-2.5 dark:border-gray-700/50 dark:bg-gray-800/40">';
                        html += '<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#465fff]/10">';
                        html += '<svg class="h-4 w-4 text-[#465fff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
                        html += '</div>';
                        html += '<div class="min-w-0 flex-1">';
                        html += '<p class="text-[11px] font-semibold text-gray-800 dark:text-white">' + (att.DocumentType || 'Document') + '</p>';
                        html += '<p class="text-[10px] text-gray-400 dark:text-gray-500 truncate">' + att.FilePath + '</p>';
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div></div>';
                }

                if (!html) {
                    html = '<div class="flex flex-col items-center justify-center py-12">';
                    html += '<div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 mb-3">';
                    html += '<svg class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>';
                    html += '</div>';
                    html += '<p class="text-xs font-medium text-gray-400 dark:text-gray-500">No trail data available.</p>';
                    html += '</div>';
                }

                content.innerHTML = html;
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            })
            .catch(function() {
                content.innerHTML = '<div class="flex flex-col items-center justify-center py-12"><div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-500/10 mb-3"><svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg></div><p class="text-xs font-medium text-red-500">Failed to load audit trail.</p></div>';
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            });
    }

    function closeModal() {
        var modal = document.getElementById('auditTrailModal');
        if (modal) { modal.style.display = 'none'; }
    }
    window.closeAuditTrail = closeModal;

    document.addEventListener('click', function(e) {
        var row = e.target.closest('tr[data-ref]');
        if (row) {
            openAuditTrail(row.getAttribute('data-ref'));
            return;
        }
        var modal = document.getElementById('auditTrailModal');
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var modal = document.getElementById('auditTrailModal');
            if (modal && modal.style.display !== 'none') {
                modal.style.display = 'none';
            }
        }
    });
})();
</script>
@endpush
