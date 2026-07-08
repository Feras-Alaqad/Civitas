<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Civitas') }} - Forgot Password</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center w-full dark:bg-gray-950 bg-gray-100 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-6 sm:px-8 py-6 sm:py-8 w-[400px] lg:w-[700px] max-w-[calc(100%-2rem)] mx-auto">
        <h1 class="text-xl sm:text-2xl font-bold text-center mb-4 dark:text-gray-200">Forgot Password</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">Enter your username and we'll send you a reset link.</p>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 text-center">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="Username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                <input type="text" id="Username" name="Username" value="{{ old('Username') }}" required autofocus placeholder="Enter your username" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('Username')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('login') }}" class="text-xs text-indigo-500 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Back to Login</a>
                <button type="submit" class="py-2.5 sm:py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">Send Reset Link</button>
            </div>
        </form>
    </div>
</body>
</html>