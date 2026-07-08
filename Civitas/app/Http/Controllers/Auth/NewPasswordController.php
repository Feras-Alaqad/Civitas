<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
            'Username' => $request->Username,
            'token' => $request->route('token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'Username' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $record = DB::table('password_resets')
            ->where('Username', $request->Username)
            ->first();

        if (! $record || ! hash_equals(hash('sha256', $request->token), $record->token)) {
            return back()
                ->withInput($request->only('Username'))
                ->withErrors(['Username' => 'Invalid or expired reset token.']);
        }

        if (now()->diffInMinutes($record->created_at) >= 60) {
            DB::table('password_resets')->where('Username', $request->Username)->delete();
            return back()
                ->withInput($request->only('Username'))
                ->withErrors(['Username' => 'Reset token has expired. Please request a new one.']);
        }

        $user = User::where('Username', $request->Username)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('Username'))
                ->withErrors(['Username' => 'User not found.']);
        }

        $user->password = $request->password;
        $user->remember_token = Str::random(60);
        $user->save();

        DB::table('password_resets')->where('Username', $request->Username)->delete();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }
}
