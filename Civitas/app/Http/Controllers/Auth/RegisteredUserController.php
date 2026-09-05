<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'Username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'access_key' => ['required', 'string'],
        ], [
            'access_key.required' => 'An administrator access key is required to create an account.',
        ]);

        // Self-registration is only possible with the shared access key. If the
        // key is empty in configuration, registration is disabled entirely.
        $configuredKey = (string) config('app.admin_access_key', '');

        if ($configuredKey === '' || ! hash_equals($configuredKey, (string) $request->access_key)) {
            return back()
                ->withErrors(['access_key' => 'Invalid administrator access key.'])
                ->onlyInput('Username', 'email');
        }

        $adminRole = DB::table('roles')->where('RoleName', 'Admin')->first();

        if ($adminRole === null) {
            throw new \RuntimeException('The Admin role is not configured.');
        }

        $user = new User;
        $user->Username = $request->Username;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->RoleID = $adminRole->RoleID;
        $user->IsActive = true;
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        AuditLog::create([
            'UserID' => $user->id,
            'ActionType' => 'Register',
            'Description' => "New administrator user registered: {$request->Username}",
            'Timestamp' => now(),
            'IPAddress' => $request->ip(),
        ]);

        return redirect(route('admin.dashboard', absolute: false));
    }
}
