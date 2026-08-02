<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Edit Profile</h3>

    @if(session('status') === 'profile-updated')
        <div class="mb-4 rounded-lg bg-success-50 p-4 text-sm text-success-600 dark:bg-success-500/15 dark:text-success-500">
            Profile updated successfully.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Profile Photo</label>
                <div class="flex items-center gap-4">
                    <div class="shrink-0">
                        <div id="avatar-preview" class="h-16 w-16 overflow-hidden rounded-full border-2 border-brand-500">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="Avatar" class="h-full w-full object-cover" />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-brand-100 dark:bg-brand-900">
                                    <span class="text-xl font-bold text-brand-600 dark:text-brand-300">{{ strtoupper(substr($user->Username, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1">
                        <input
                            type="file"
                            name="avatar"
                            id="avatar-input"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-brand-900/50 dark:file:text-brand-300"
                        />
                        <p class="mt-1 text-xs text-gray-400">JPG, PNG, GIF or WebP. Max 2MB.</p>
                    </div>
                </div>
                @error('avatar')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Username</label>
                <input
                    type="text"
                    name="Username"
                    value="{{ old('Username', $user->Username) }}"
                    required
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                />
                @error('Username')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                />
                @error('email')
                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-3 sm:col-span-2">
                <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('avatar-preview').innerHTML = '<img src="' + ev.target.result + '" class="h-full w-full object-cover" />';
    };
    reader.readAsDataURL(file);
});
</script>
