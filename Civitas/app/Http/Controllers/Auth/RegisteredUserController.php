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
        ]);

        $adminRole = DB::table('roles')->where('RoleName', 'Admin')->first();

        $user = User::create([
            'Username' => $request->Username,
            'email' => $request->email,
            'password' => $request->password,
            'RoleID' => $adminRole?->RoleID,
            'IsActive' => 1,
        ]);

        event(new Registered($user));

        Auth::login($user);

        AuditLog::create([
            'UserID' => $user->id,
            'ActionType' => 'Register',
            'Description' => "New admin user registered: {$request->Username}",
            'Timestamp' => now(),
            'IPAddress' => $request->ip(),
        ]);

        return redirect(route('admin.dashboard', absolute: false));
    }
}
