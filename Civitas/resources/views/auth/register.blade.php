<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Civitas') }} - Sign Up</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center w-full dark:bg-gray-950 bg-gray-100 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-6 sm:px-8 py-6 sm:py-8 w-[400px] lg:w-[500px] max-w-[calc(100%-2rem)] mx-auto">
        <h1 class="text-xl sm:text-2xl font-bold text-center mb-4 dark:text-gray-200">Create Account</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label for="Username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                <input type="text" id="Username" name="Username" value="{{ old('Username') }}" required autofocus autocomplete="username" placeholder="Enter your username" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('Username')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('email')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Enter a password" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('password')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('password_confirmation')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 mb-4">
                <a href="{{ route('login') }}" class="text-xs text-brand-500 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">Already registered? Sign in</a>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 sm:py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">Sign Up</button>
        </form>

        <div class="flex items-center my-5">
            <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
            <span class="px-3 text-xs text-gray-400 dark:text-gray-500">or</span>
            <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
        </div>

        <a href="{{ route('auth.google') }}?mode=register"
           class="flex items-center justify-center gap-3 w-full border border-gray-300 dark:border-gray-600 rounded-md py-2.5 sm:py-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Sign up with Google</span>
        </a>
    </div>
</body>
</html>