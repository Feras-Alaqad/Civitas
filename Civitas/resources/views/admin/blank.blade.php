@extends('layouts.admin')

@section('title', 'Blank Page')

@php $page = 'blank'; $pageName = 'Blank Page'; @endphp

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <div class="flex flex-col items-center justify-center py-20">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Blank Page</h3>
        <p class="mt-2 text-gray-500 dark:text-gray-400">Start building your page here</p>
    </div>
</div>
@endsection
