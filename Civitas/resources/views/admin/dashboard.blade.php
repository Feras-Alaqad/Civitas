@extends('layouts.admin')

@php $page = 'dashboard'; $pageName = 'Dashboard'; @endphp

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 flex items-center justify-between">
        @isset($loadTimeMs)
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $loadTimeMs }} ms
        </span>
        @endisset
    </div>
    <div class="col-span-12">
        @include('admin.partials.metric-group-01')
    </div>
    <div class="col-span-12">
        @include('admin.partials.chart-01')
    </div>
    <div class="col-span-12">
        @include('admin.partials.chart-02')
    </div>
    <div class="col-span-12">
        @include('admin.partials.chart-03')
    </div>

</div>
@endsection
