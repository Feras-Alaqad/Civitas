@extends('layouts.admin')

@section('title', 'eCommerce Dashboard')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
        @include('admin.partials.metric-group-01')
        @include('admin.partials.chart-01')
    </div>
    <div class="col-span-12 xl:col-span-5">
        @include('admin.partials.chart-02')
    </div>
    <div class="col-span-12">
        @include('admin.partials.chart-03')
    </div>
    <div class="col-span-12 xl:col-span-5">
        @include('admin.partials.map-01')
    </div>
    <div class="col-span-12 xl:col-span-7">
        @include('admin.partials.table-01')
    </div>
</div>
@endsection
