<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Civitas') }} - Sign In</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.classList.toggle('dark', JSON.parse(localStorage.getItem('darkMode')) === true);
    </script>
</head>
<body class="min-h-screen flex items-center justify-center w-full dark:bg-gray-950 bg-gray-100 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-6 sm:px-8 py-6 sm:py-8 w-[400px] lg:w-[500px] max-w-[calc(100%-2rem)] mx-auto">
        <div class="mb-4 flex justify-center">
            <img class="h-12 w-auto dark:hidden" src="{{ asset('logo.svg') }}" alt="Civitas" />
            <img class="hidden h-12 w-auto dark:block" src="{{ asset('logo-dark.svg') }}" alt="Civitas" />
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-center mb-4 dark:text-gray-200">Welcome Back!</h1>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 text-center">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 text-sm text-red-600 text-center bg-red-50 dark:bg-red-500/10 rounded-md py-2 px-3">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="Username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                <input type="text" id="Username" name="Username" value="{{ old('Username') }}" required autofocus autocomplete="username" placeholder="Enter your username" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('Username')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('password')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <button type="button" onclick="openForgotModal()" class="inline-block mt-1 text-xs text-gray-600 hover:text-brand-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:text-gray-400 bg-transparent border-0 p-0 cursor-pointer">Forgot Password?</button>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 mb-4">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 focus:outline-none dark:bg-gray-800 dark:border-gray-600">
                    <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Remember me</label>
                </div>
                <a href="{{ route('register') }}" class="text-xs text-brand-500 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">Create Account</a>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 sm:py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">Login</button>
        </form>

        <div class="flex items-center my-5">
            <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
            <span class="px-3 text-xs text-gray-400 dark:text-gray-500">or</span>
            <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
        </div>

        <a href="{{ route('auth.google') }}?mode=login"
           class="flex items-center justify-center gap-3 w-full border border-gray-300 dark:border-gray-600 rounded-md py-2.5 sm:py-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Sign in with Google</span>
        </a>
    </div>

    <div id="forgotModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
        <div id="modalOverlay" class="absolute inset-0 bg-black/0 backdrop-blur-none transition-all duration-500"></div>
        <div id="modalContent" class="relative bg-white dark:bg-gray-900 rounded-lg shadow-2xl w-[400px] max-w-full mx-auto p-6 sm:p-8 opacity-0 translate-y-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg sm:text-xl font-bold dark:text-gray-200">Reset Password</h2>
                <button type="button" onclick="closeForgotModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none transition-colors">&times;</button>
            </div>

            <div id="forgotStep1">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Enter your username and we'll send you a reset link.</p>
                <div class="mb-4">
                    <label for="resetUsername" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                    <input type="text" id="resetUsername" placeholder="Enter your username" class="shadow-sm rounded-md w-full px-3 py-2.5 border border-gray-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    <p id="resetError" class="text-sm text-red-500 mt-1 hidden"></p>
                </div>
                <button type="button" onclick="sendResetLink()" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                    <span id="sendText">Send Reset Link</span>
                    <svg id="sendSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            </div>

            <div id="forgotStep2" class="hidden">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">Reset link sent!</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Check your email or click the link below.</p>
                    <a id="resetLink" href="#" target="_blank" class="text-sm text-brand-500 hover:text-brand-400 break-all mb-4 inline-block">Open reset page</a>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Didn't receive the email? You can resend in</p>
                        <p id="timer" class="text-2xl font-bold text-brand-500 dark:text-brand-400">60</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">seconds</p>
                        <button type="button" id="resendBtn" onclick="sendResetLink()" disabled class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-400 cursor-not-allowed transition-colors">Resend</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-show #modalOverlay {
            background-color: rgba(0, 0, 0, 0.6);
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
        }
        .modal-show #modalContent {
            opacity: 1;
            transform: translateY(0);
            animation: modalBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes modalBounce {
            0% { opacity: 0; transform: translateY(40px) scale(0.95); }
            50% { opacity: 1; transform: translateY(-8px) scale(1.01); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-hide #modalOverlay {
            background-color: rgba(0, 0, 0, 0);
            -webkit-backdrop-filter: blur(0px);
            backdrop-filter: blur(0px);
        }
        .modal-hide #modalContent {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            transition: all 0.2s ease-in;
        }
    </style>

    <script>
        const forgotModal = document.getElementById('forgotModal');
        const modalContent = document.getElementById('modalContent');
        const step1 = document.getElementById('forgotStep1');
        const step2 = document.getElementById('forgotStep2');
        const resetUsername = document.getElementById('resetUsername');
        const resetError = document.getElementById('resetError');
        const sendText = document.getElementById('sendText');
        const sendSpinner = document.getElementById('sendSpinner');
        const resetLink = document.getElementById('resetLink');
        const timerEl = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        let countdownInterval;

        function openForgotModal() {
            forgotModal.classList.remove('hidden');
            forgotModal.classList.add('flex');
            step1.classList.remove('hidden');
            step2.classList.add('hidden');
            resetUsername.value = '';
            resetError.classList.add('hidden');
            requestAnimationFrame(() => {
                forgotModal.classList.remove('modal-hide');
                forgotModal.classList.add('modal-show');
            });
        }

        function closeForgotModal() {
            forgotModal.classList.remove('modal-show');
            forgotModal.classList.add('modal-hide');
            setTimeout(() => {
                forgotModal.classList.add('hidden');
                forgotModal.classList.remove('flex', 'modal-hide');
                clearInterval(countdownInterval);
            }, 250);
        }

        forgotModal.addEventListener('click', function(e) {
            if (e.target === forgotModal || e.target.id === 'modalOverlay') closeForgotModal();
        });

        function sendResetLink() {
            const username = resetUsername.value.trim();
            if (!username) {
                resetError.textContent = 'Please enter your username.';
                resetError.classList.remove('hidden');
                return;
            }
            resetError.classList.add('hidden');
            sendText.classList.add('hidden');
            sendSpinner.classList.remove('hidden');

            fetch('{{ route("password.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ Username: username })
            })
            .then(r => r.json().then(data => ({ status: r.status, body: data })))
            .then(({ status, body }) => {
                sendText.classList.remove('hidden');
                sendSpinner.classList.add('hidden');
                if (status === 200 && body.status === 'success') {
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                    resetLink.href = body.reset_url;
                    resetLink.textContent = 'Open reset page';
                    startTimer(60);
                } else if (status === 422) {
                    const msg = body.errors?.Username?.[0] || 'Invalid username.';
                    resetError.textContent = msg;
                    resetError.classList.remove('hidden');
                } else {
                    resetError.textContent = body.message || 'Something went wrong.';
                    resetError.classList.remove('hidden');
                }
            })
            .catch(() => {
                sendText.classList.remove('hidden');
                sendSpinner.classList.add('hidden');
                resetError.textContent = 'An error occurred. Please try again later.';
                resetError.classList.remove('hidden');
            });
        }

        function startTimer(seconds) {
            clearInterval(countdownInterval);
            timerEl.textContent = seconds;
            resendBtn.disabled = true;
            resendBtn.classList.remove('bg-brand-500', 'hover:bg-brand-600', 'cursor-pointer');
            resendBtn.classList.add('bg-brand-400', 'cursor-not-allowed');

            countdownInterval = setInterval(() => {
                seconds--;
                timerEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('bg-brand-400', 'cursor-not-allowed');
                    resendBtn.classList.add('bg-brand-500', 'hover:bg-brand-600', 'cursor-pointer');
                }
            }, 1000);
        }
    </script>
</body>
</html>