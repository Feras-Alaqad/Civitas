<div class="relative py-16">
    <div aria-hidden="true" class="absolute inset-0 h-max w-full m-auto grid grid-cols-2 -space-x-52 opacity-40 dark:opacity-20">
        <div class="blur-[106px] h-56 bg-gradient-to-br from-brand-500 to-brand-300 dark:from-brand-600"></div>
        <div class="blur-[106px] h-32 bg-gradient-to-r from-brand-300 to-brand-100 dark:to-brand-500"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="relative">
            <div class="flex items-center justify-center -space-x-2">
                <img class="size-12 rounded-full object-cover" src="{{ asset('landing/images/avatars/avatar.webp') }}" alt="avatar" loading="lazy" />
                <img class="size-12 rounded-full object-cover" src="{{ asset('landing/images/avatars/avatar-2.webp') }}" alt="avatar" loading="lazy" />
                <img class="size-12 rounded-full object-cover" src="{{ asset('landing/images/avatars/avatar-3.webp') }}" alt="avatar" loading="lazy" />
                <img class="size-12 rounded-full object-cover" src="{{ asset('landing/images/avatars/avatar-4.webp') }}" alt="avatar" loading="lazy" />
                <img class="size-12 rounded-full object-cover" src="{{ asset('landing/images/avatars/avatar-1.webp') }}" alt="avatar" loading="lazy" />
            </div>
            <div class="mt-6 m-auto space-y-6 md:w-8/12 lg:w-7/12">
                <h1 class="text-center text-4xl font-bold text-gray-800 dark:text-white md:text-5xl">Ready to see it in action?</h1>
                <p class="text-center text-xl text-gray-600 dark:text-gray-300">Create a free account and explore the full dashboard — no credit card required.</p>
                <div class="flex flex-wrap justify-center gap-6">
                    <a href="{{ route('register') }}" class="relative flex h-12 w-full items-center justify-center px-6 before:absolute before:inset-0 before:rounded-full before:bg-brand-500 before:transition before:duration-300 hover:before:scale-105 active:duration-75 active:before:scale-95 sm:w-max">
                        <span class="relative text-base font-semibold text-white">Try the Demo</span>
                    </a>
                    <a href="#" class="relative flex h-12 w-full items-center justify-center px-6 before:absolute before:inset-0 before:rounded-full before:border before:border-gray-200 before:bg-transparent before:transition before:duration-300 hover:before:scale-105 active:duration-75 active:before:scale-95 dark:before:border-gray-700 sm:w-max">
                        <span class="relative text-base font-semibold text-gray-700 dark:text-gray-300">Learn More</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
