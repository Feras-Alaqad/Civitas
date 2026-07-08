<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Civitas') }} - Sign In</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center w-full dark:bg-gray-950 bg-gray-100 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-900 shadow-md rounded-lg px-6 sm:px-8 py-6 sm:py-8 w-[400px] lg:w-[500px] max-w-[calc(100%-2rem)] mx-auto">
        <h1 class="text-xl sm:text-2xl font-bold text-center mb-4 dark:text-gray-200">Welcome Back!</h1>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 text-center">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="Username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                <input type="text" id="Username" name="Username" value="{{ old('Username') }}" required autofocus autocomplete="username" placeholder="Enter your username" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('Username')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="shadow-sm rounded-md w-full px-3 py-2.5 sm:py-2 border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                @error('password')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <button type="button" onclick="openForgotModal()" class="inline-block mt-1 text-xs text-gray-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:text-gray-400 bg-transparent border-0 p-0 cursor-pointer">Forgot Password?</button>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 mb-4">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:outline-none dark:bg-gray-800 dark:border-gray-600">
                    <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Remember me</label>
                </div>
                <a href="{{ route('register') }}" class="text-xs text-indigo-500 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Create Account</a>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 sm:py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">Login</button>
        </form>
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
                    <input type="text" id="resetUsername" placeholder="Enter your username" class="shadow-sm rounded-md w-full px-3 py-2.5 border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    <p id="resetError" class="text-sm text-red-500 mt-1 hidden"></p>
                </div>
                <button type="button" onclick="sendResetLink()" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
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
                    <a id="resetLink" href="#" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500 break-all mb-4 inline-block">Open reset page</a>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Didn't receive the email? You can resend in</p>
                        <p id="timer" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">60</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">seconds</p>
                        <button type="button" id="resendBtn" onclick="sendResetLink()" disabled class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-400 cursor-not-allowed transition-colors">Resend</button>
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
            resendBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'cursor-pointer');
            resendBtn.classList.add('bg-indigo-400', 'cursor-not-allowed');

            countdownInterval = setInterval(() => {
                seconds--;
                timerEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('bg-indigo-400', 'cursor-not-allowed');
                    resendBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'cursor-pointer');
                }
            }, 1000);
        }
    </script>
</body>
</html>