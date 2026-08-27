<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | CivitasAdmin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/tailadmin.css', 'resources/js/tailadmin.js'])
    @stack('styles')
</head>
<body
    x-data="{ page: '{{ $page ?? 'ecommerce' }}', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
>
    @include('admin.partials.preloader')

    <div class="flex h-screen overflow-hidden">
        @include('admin.partials.sidebar')

        <div id="scroll-container" :class="sidebarToggle ? 'lg:ml-[290px]' : 'lg:ml-0'" class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto transition-all duration-300">
            @include('admin.partials.overlay')
            @include('admin.partials.header')

            <main>
                <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                    @if(isset($breadcrumb) && $breadcrumb)
                        @include('admin.partials.breadcrumb')
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (function() {
            var c = document.getElementById('scroll-container');
            var key = 'scroll_pos_' + location.pathname;
            var saved = sessionStorage.getItem(key);
            if (saved) {
                c.scrollTop = parseInt(saved, 10);
            }
            c.addEventListener('scroll', function() {
                sessionStorage.setItem(key, c.scrollTop);
            });
        })();
    </script>
</body>
</html>
