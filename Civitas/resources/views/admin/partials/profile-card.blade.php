<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <div class="flex flex-col items-center">
        <div class="w-full max-w-[120px] overflow-hidden rounded-full border-[3px] border-brand-500">
            <img src="{{ asset('images/user/owner.jpg') }}" alt="Profile" />
        </div>
        <div class="mt-4 text-center">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ Auth::user()->name ?? 'User Name' }}</h4>
            <p class="text-gray-500 dark:text-gray-400">{{ '@' }}{{ Auth::user()->Username ?? 'user' }}</p>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <span class="rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
        </div>
    </div>
    <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Posts</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">45</span>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Followers</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">2,845</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500 dark:text-gray-400">Following</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">1,235</span>
        </div>
    </div>
</div>
