@extends('layouts.admin')

@php $page = 'citizens'; $pageName = 'Persons'; @endphp

@section('title', 'Persons')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    @if($selectedPerson)
    <div class="flex items-center gap-3 px-5 py-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Person Details</h3>
        <a href="{{ route('admin.citizens') }}" class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to list
        </a>
        @isset($loadTimeMs)
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $loadTimeMs }} ms
        </span>
        @endisset
    </div>
    @else
    {{-- Title + Search Row --}}
    <div class="flex items-center gap-4 px-5 pt-4 pb-4">
        <div class="flex shrink-0 items-center gap-3">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Persons</h3>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
                {{ $citizens ? number_format($citizens->total()) : 0 }}
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
        <form method="GET" action="{{ route('admin.citizens') }}" class="relative flex-1">
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by National ID, name, phone, or email..."
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3.5 pl-12 pr-12 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white/90 dark:placeholder-gray-500 dark:focus:bg-gray-800 dark:focus:border-brand-500 transition-all"
                />
                @if(request('search'))
                <a href="{{ route('admin.citizens') }}" class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>
    @endif

    @if($selectedPerson)
    {{-- Person Details + Service Requests --}}
    <div class="border-t border-gray-100 dark:border-gray-800">
        {{-- Person Info Card --}}
        <div class="p-5 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Full Name</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedPerson->FullName }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">National ID</span>
                    <p class="mt-1 text-sm font-semibold font-mono text-gray-800 dark:text-white/90">{{ $selectedPerson->NationalID ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Gender</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $selectedPerson->Gender === 'male' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-500' : 'bg-pink-50 text-pink-600 dark:bg-pink-500/15 dark:text-pink-500' }}">{{ ucfirst($selectedPerson->Gender ?? '—') }}</span>
                    </p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Date of Birth</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedPerson->DateOfBirth ? \Carbon\Carbon::parse($selectedPerson->DateOfBirth)->format('d M Y') : '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Phone</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        @if($selectedPerson->Phone)
                        <a href="tel:{{ $selectedPerson->Phone }}" class="hover:text-brand-500 dark:hover:text-brand-400 transition-colors">{{ $selectedPerson->Phone }}</a>
                        @else — @endif
                    </p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Email</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedPerson->Email ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Governorate</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedPerson->GovernorateName ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Nationality</span>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedPerson->NationalityName ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Service Requests --}}
        <div class="border-t border-gray-100 dark:border-gray-800 px-5 sm:px-6 py-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">Service Requests</h4>
                    <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $serviceRequests->count() }} requests</span>
                </div>
                <a href="{{ route('admin.service.create', ['person_id' => $selectedPerson->PersonID]) }}" class="group inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-brand-500/25 hover:bg-brand-600 hover:shadow-lg hover:shadow-brand-500/30 transition-all duration-200">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/20 group-hover:bg-white/30 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </span>
                    Add Service
                </a>
            </div>
            @if($serviceRequests->isNotEmpty())
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-3 px-4 text-left"><span class="font-semibold text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Date</span></th>
                            <th class="py-3 px-4 text-left"><span class="font-semibold text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Service</span></th>
                            <th class="py-3 px-4 text-left"><span class="font-semibold text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Department</span></th>
                            <th class="py-3 px-4 text-left"><span class="font-semibold text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Fees</span></th>
                            <th class="py-3 px-4 text-left"><span class="font-semibold text-gray-500 text-theme-xs dark:text-gray-400 uppercase tracking-wider">Status</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($serviceRequests as $req)
                        @php
                            $reqData = [
                                'date' => \Carbon\Carbon::parse($req->RequestDate)->format('d M Y, h:i A'),
                                'service' => $req->ServiceName,
                                'department' => $req->DepartmentName ?? '—',
                                'fees' => '$' . number_format($req->Fees, 2),
                                'status' => $req->Status,
                                'docs' => $req->RequiredDocuments ?? '—',
                                'created' => $req->created_at ? \Carbon\Carbon::parse($req->created_at)->format('d M Y, h:i A') : '—',
                                'updated' => $req->updated_at ? \Carbon\Carbon::parse($req->updated_at)->format('d M Y, h:i A') : '—',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors cursor-pointer" data-request='{{ json_encode($reqData) }}'>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($req->RequestDate)->format('d M Y, h:i A') }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $req->ServiceName }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $req->DepartmentName ?? '—' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">${{ number_format($req->Fees, 2) }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $status = $req->Status;
                                    $badgeClass = match($status) {
                                        'Completed' => 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                                        'Pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                                        'Cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                                        'In Progress' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                                        default => 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-10">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-700/50 mb-3">
                    <svg class="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">No service requests yet</span>
                <span class="text-xs text-gray-400 dark:text-gray-500 mb-4">Start by creating a new service request for this person.</span>
                <a href="{{ route('admin.service.create', ['person_id' => $selectedPerson->PersonID]) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-brand-500/25 hover:bg-brand-600 hover:shadow-lg hover:shadow-brand-500/30 transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Add First Service
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Request Details Modal --}}
    <div id="requestModal"
         class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/45"
         style="display: none;">
        <div class="w-[420px] max-w-[90%] rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-lg font-semibold text-gray-800 dark:text-white/90">Request Details</span>
                <button onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="requestModalBody" class="flex flex-col gap-3"></div>
        </div>
    </div>

    <script>
        function openRequestModal(data) {
            const statusBadge = {
                'Completed': 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
                'Pending': 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                'Cancelled': 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                'In Progress': 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            };
            const badge = statusBadge[data.status] || 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400';

            document.getElementById('requestModalBody').innerHTML = `
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Service</span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">${data.service}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Department</span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">${data.department}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Request Date</span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">${data.date}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badge}">${data.status}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Fees</span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white/90">${data.fees}</span>
                </div>
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/50">
                    <span class="text-sm text-gray-500 dark:text-gray-400 block mb-1">Required Documents</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">${data.docs}</span>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-1">
                    <div class="flex items-center justify-between rounded-lg px-4 py-2">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Created</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${data.created}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg px-4 py-2">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Last Updated</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${data.updated}</span>
                    </div>
                </div>
            `;

            document.getElementById('requestModal').style.display = 'flex';
        }

        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('requestModal');
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === this) this.style.display = 'none';
                });
            }

            document.querySelectorAll('tr[data-request]').forEach(function (row) {
                row.addEventListener('click', function () {
                    try {
                        const data = JSON.parse(this.getAttribute('data-request'));
                        openRequestModal(data);
                    } catch (e) {
                        console.error('Invalid request data', e);
                    }
                });
            });
        });
    </script>
    @else
    {{-- Table Section --}}
    <div class="border-t border-gray-100 dark:border-gray-800">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/40">
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">National ID</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gender</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date of Birth</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Governorate</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nationality</span></th>
                        <th class="py-3.5 px-4 text-left"><span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"></span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($citizens as $c)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors cursor-pointer" onclick="window.location='{{ route('admin.citizens', ['person_id' => $c->PersonID, 'search' => request('search')]) }}'">
                        <td class="py-3.5 px-4">
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $c->FullName }}</span>
                        </td>
                        <td class="py-3.5 px-4"><span class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $c->NationalID ?? '—' }}</span></td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($c->Gender ?? '') === 'male' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-500' : 'bg-pink-50 text-pink-600 dark:bg-pink-500/15 dark:text-pink-500' }}">
                                @if(($c->Gender ?? '') === 'male')
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2ZM12 14C7.58172 14 4 16.6863 4 20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20C20 16.6863 16.4183 14 12 14Z"/></svg>
                                @else
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2ZM12 14C7.58172 14 4 16.6863 4 20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20C20 16.6863 16.4183 14 12 14Z"/></svg>
                                @endif
                                {{ ucfirst($c->Gender ?? '—') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4"><span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $c->DateOfBirth ? \Carbon\Carbon::parse($c->DateOfBirth)->format('d M Y') : '—' }}</span></td>
                        <td class="py-3.5 px-4">
                            @if($c->Phone)
                            <a href="tel:{{ $c->Phone }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-brand-500 dark:hover:text-brand-400 transition-colors">{{ $c->Phone }}</a>
                            @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($c->Email)
                            <span class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-[160px] block">{{ $c->Email }}</span>
                            @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4"><span class="text-sm text-gray-500 dark:text-gray-400">{{ $c->GovernorateName ?? '—' }}</span></td>
                        <td class="py-3.5 px-4"><span class="text-sm text-gray-500 dark:text-gray-400">{{ $c->NationalityName ?? '—' }}</span></td>
                        <td class="py-3.5 px-4">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm text-gray-400 dark:text-gray-500">
                                    @if(request('search'))
                                    No persons match "{{ request('search') }}"
                                    @else
                                    Search for a person by National ID, name, phone, or email
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($citizens && $citizens->total() > 0)
        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex items-center justify-between gap-4">
                @if($currentPage > 1)
                <a href="{{ route('admin.citizens', ['search' => $search, 'page' => $currentPage - 1]) }}"
                   class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                   aria-label="Previous page">
                    <svg class="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-600" aria-disabled="true">
                    <svg class="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @endif

                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Page {{ number_format($currentPage) }} of {{ number_format($citizens->lastPage()) }}
                </span>

                @if($citizens->hasMorePages())
                <a href="{{ route('admin.citizens', ['search' => $search, 'page' => $currentPage + 1]) }}"
                   class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                   aria-label="Next page">
                    <svg class="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-600" aria-disabled="true">
                    <svg class="h-5 w-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

@if(session('error'))
<div class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-600 dark:bg-red-500/15 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

{{-- Import CSV Modal --}}
<div id="importModal"
     class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/45"
     style="display: none;">
    <div class="w-[560px] max-w-[94%] rounded-xl bg-white p-0 shadow-lg dark:bg-gray-800">
        <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                <svg class="h-4 w-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Import Persons from CSV</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500">Bulk-import person records</p>
            </div>
            <button onclick="closeImportModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="importStepUpload" class="p-5">
            <div class="mb-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 dark:text-gray-400">CSV Columns</h4>
                <div class="grid grid-cols-2 gap-1.5">
                    @php
                        $columns = [
                            'PersonID', 'FirstName', 'FatherName', 'MotherName',
                            'FamilyName', 'CityID', 'GovernorateID', 'NationalityID',
                            'Phone', 'Email', 'DateOfBirth', 'NationalID',
                            'Address', 'Gender',
                        ];
                    @endphp
                    @foreach($columns as $col)
                        <span class="rounded bg-gray-50 px-2 py-1 text-[11px] font-mono text-gray-600 dark:bg-gray-700/50 dark:text-gray-400">{{ $col }}</span>
                    @endforeach
                </div>
            </div>

            <form id="importFormModal" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="flex flex-col items-center justify-center w-full h-28 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 cursor-pointer hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700/50 dark:hover:bg-gray-700 transition-colors group">
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 group-hover:bg-brand-50 dark:bg-gray-700 dark:group-hover:bg-brand-500/10 transition-colors">
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-brand-500 dark:text-gray-500 dark:group-hover:text-brand-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold text-brand-600 dark:text-brand-400">Click to upload</span> or drop</span>
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">CSV up to 600MB</span>
                        </div>
                        <input type="file" name="csv_file" accept=".csv,.txt" class="hidden" onchange="handleFileSelectModal(this)" />
                    </label>
                    <div id="fileInfoModal" class="mt-2 hidden">
                        <div class="flex items-center gap-2 rounded-lg border border-brand-100 bg-brand-50 px-3 py-2 dark:border-brand-500/20 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span id="fileNameModal" class="text-xs font-medium text-gray-800 dark:text-white/90 truncate flex-1"></span>
                            <span id="fileSizeModal" class="text-[11px] text-gray-500 dark:text-gray-400 shrink-0"></span>
                            <button type="button" onclick="clearFileModal()" class="shrink-0 p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeImportModal()" class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <button type="submit" id="importSubmitBtnModal" disabled class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                        </svg>
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>

        {{-- Upload Progress --}}
        <div id="importStepUploadingModal" class="hidden p-5">
            <div class="flex flex-col items-center gap-3 py-4">
                <svg class="h-10 w-10 text-brand-500 animate-pulse" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">Uploading file...</p>
                <div class="w-full">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span id="uploadPercentModal">0%</span>
                        <span id="uploadCountModal">0 MB / 0 MB</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                        <div id="uploadBarModal" class="h-2 rounded-full bg-brand-500 transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Processing Progress --}}
        <div id="importStepProgressModal" class="hidden p-5">
            <div class="flex flex-col items-center gap-3 py-4">
                <svg class="h-10 w-10 text-brand-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">Processing...</p>
                <div class="w-full">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span id="progressPercentModal">0%</span>
                        <span id="progressCountModal">0 / 0 rows</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                        <div id="progressBarModal" class="h-2 rounded-full bg-brand-500 transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                <p id="progressStatusModal" class="text-xs text-gray-400 dark:text-gray-500"></p>
                <button type="button" onclick="closeImportModal()" class="mt-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors hidden" id="closeBtnModal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let importPollInterval = null;
let selectedFileModal = null;

function formatSize(bytes) {
    if (bytes === 0) return '0 MB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function openImportModal() {
    document.getElementById('importStepUpload').classList.remove('hidden');
    document.getElementById('importStepUploadingModal').classList.add('hidden');
    document.getElementById('importStepProgressModal').classList.add('hidden');
    document.getElementById('importModal').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
    if (importPollInterval) {
        clearInterval(importPollInterval);
        importPollInterval = null;
    }
}

function handleFileSelectModal(input) {
    selectedFileModal = input.files[0];
    const info = document.getElementById('fileInfoModal');
    const btn = document.getElementById('importSubmitBtnModal');
    if (selectedFileModal) {
        document.getElementById('fileNameModal').textContent = selectedFileModal.name;
        document.getElementById('fileSizeModal').textContent = formatSize(selectedFileModal.size);
        info.classList.remove('hidden');
        btn.disabled = false;
    } else {
        info.classList.add('hidden');
        btn.disabled = true;
    }
}

function clearFileModal() {
    selectedFileModal = null;
    document.querySelector('#importModal input[name="csv_file"]').value = '';
    document.getElementById('fileInfoModal').classList.add('hidden');
    document.getElementById('importSubmitBtnModal').disabled = true;
}

document.getElementById('importFormModal').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = document.getElementById('importSubmitBtnModal');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading...';

    document.getElementById('importStepUpload').classList.add('hidden');
    document.getElementById('importStepUploadingModal').classList.remove('hidden');

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function (evt) {
        if (evt.lengthComputable) {
            const pct = Math.round((evt.loaded / evt.total) * 100);
            document.getElementById('uploadPercentModal').textContent = pct + '%';
            document.getElementById('uploadCountModal').textContent = formatSize(evt.loaded) + ' / ' + formatSize(evt.total);
            document.getElementById('uploadBarModal').style.width = pct + '%';
        }
    });

    xhr.addEventListener('load', function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const data = JSON.parse(xhr.responseText);
                document.getElementById('importStepUploadingModal').classList.add('hidden');
                document.getElementById('importStepProgressModal').classList.remove('hidden');
                startPollingModal(data.import_id);
            } catch (e) {
                alert('Invalid server response.');
                closeImportModal();
            }
        } else {
            let msg = 'Upload failed';
            try {
                const err = JSON.parse(xhr.responseText);
                if (err.message) msg = err.message;
            } catch (_) {}
            alert(msg);
            closeImportModal();
        }
    });

    xhr.addEventListener('error', function () {
        alert('Network error during upload.');
        closeImportModal();
    });

    xhr.open('POST', '{{ route('admin.import.persons.upload') }}');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});

function startPollingModal(importId) {
    if (importPollInterval) clearInterval(importPollInterval);

    importPollInterval = setInterval(function () {
        fetch('{{ route('admin.import.progress', ['importId' => '__ID__']) }}'.replace('__ID__', importId))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                document.getElementById('progressPercentModal').textContent = data.percent + '%';
                document.getElementById('progressCountModal').textContent = data.processed + ' / ' + data.total + ' rows';
                document.getElementById('progressBarModal').style.width = data.percent + '%';
                document.getElementById('progressStatusModal').textContent =
                    data.status === 'processing' ? 'Processing row ' + data.processed + ' of ' + data.total : '';

                if (data.status === 'completed') {
                    clearInterval(importPollInterval);
                    importPollInterval = null;
                    document.querySelector('#importStepProgressModal svg').classList.remove('animate-spin', 'text-brand-500');
                    document.querySelector('#importStepProgressModal svg').classList.add('text-green-500');
                    document.querySelector('#importStepProgressModal p').textContent = 'Import completed!';
                    document.getElementById('progressStatusModal').textContent = data.processed + ' records inserted.';
                    document.getElementById('closeBtnModal').classList.remove('hidden');
                    setTimeout(function () { window.location.reload(); }, 3000);
                }
            });
    }, 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeImportModal();
        });
    }
});
</script>
@endsection