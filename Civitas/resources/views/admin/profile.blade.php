@extends('layouts.admin')

@section('title', 'Profile')

@php $page = 'profile'; $pageName = 'Profile'; @endphp

@section('content')
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="xl:col-span-1">
        @include('admin.partials.profile-card')
    </div>
    <div class="space-y-6 xl:col-span-2">
        @include('admin.partials.profile-form')
    </div>
</div>
@endsection
