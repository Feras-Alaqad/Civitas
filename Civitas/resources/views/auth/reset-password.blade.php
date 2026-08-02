<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Civitas') }} - Reset Password</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.classList.toggle('dark', JSON.parse(localStorage.getItem('darkMode')) === true);
    </script>
</head>
<body class="min-h-screen flex items-center justify-center w-full dark:bg-gray-950 bg-gray-100 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-6 sm:px-8 py-6 sm:py-8 w-[400px] lg:w-[700px] max-w-[calc(100%-2rem)] mx-auto">
        <div class="mb-4 flex justify-center">
            <img class="h-12 w-auto dark:hidden" src="{{ asset('logo.svg') }}" alt="Civitas" />
            <img class="hidden h-12 w-auto dark:block" src="{{ asset('logo-dark.svg') }}" alt="Civitas" />
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-center mb-4 dark:text-gray-200">Reset Password</h1>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-4">
                <label for="Username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                <input type="text" id="Username" name="Username" value="{{ old('Username', $Username) }}" required readonly class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 cursor-not-allowed">
                @error('Username')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Enter new password" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('password')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm new password" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('password_confirmation')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 sm:py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">Reset Password</button>
        </form>
    </div>
</body>
</html>