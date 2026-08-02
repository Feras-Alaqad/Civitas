<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <div class="flex flex-col items-center">
        <div class="w-full max-w-[120px] overflow-hidden rounded-full border-[3px] border-brand-500">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="Profile" />
            @else
                <div class="flex h-[114px] w-[114px] items-center justify-center bg-brand-100 dark:bg-brand-900">
                    <span class="text-3xl font-bold text-brand-600 dark:text-brand-300">{{ strtoupper(substr($user->Username, 0, 1)) }}</span>
                </div>
            @endif
        </div>
        <div class="mt-4 text-center">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $user->Username }}</h4>
            <p class="text-gray-500 dark:text-gray-400">{{ $user->email ?? 'No email' }}</p>
        </div>
        <div class="mt-4 flex items-center gap-2">
            @if($user->IsActive)
                <span class="rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
            @else
                <span class="rounded-full bg-error-50 px-3 py-1 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Inactive</span>
            @endif
        </div>
    </div>
    <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Member since</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->created_at->format('M Y') }}</span>
        </div>
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">Last active</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->updated_at->diffForHumans() }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500 dark:text-gray-400">Role</span>
            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->RoleID ? 'Admin' : 'User' }}</span>
        </div>
    </div>
</div>
