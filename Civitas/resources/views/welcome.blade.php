<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Civitas — a government-grade platform to manage citizen records, deliver public services, collect payments, and keep a full audit trail." />
    <title>{{ config('app.name', 'Civitas') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white dark:bg-gray-950">
    @include('landing.partials.header')

    <main class="space-y-40 mb-40">
        @include('landing.partials.hero')
        @include('landing.partials.features')
        @include('landing.partials.stats')
        @include('landing.partials.testimonials')
        @include('landing.partials.cta')
        @include('landing.partials.blog')
    </main>

    @include('landing.partials.footer')

    <script>document.getElementById('year').textContent = new Date().getFullYear();</script>
</body>
</html>
