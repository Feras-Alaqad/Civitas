<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $mode = $request->query('mode', 'login');

        // Self-registration via Google is disabled: it previously created
        // DataEntry accounts that were auto-logged-in with full access to the
        // admin area. New accounts can only be created with the registration
        // page (password + administrator access key).
        if ($mode === 'register') {
            return redirect()->route('login')
                ->with('error', 'Account creation via Google is disabled. Create your account with a username and password using the administrator access key.');
        }

        session(['google_auth_mode' => 'login']);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth failed: ' . get_class($e) . ' - ' . $e->getMessage() . ' - URL: ' . request()->fullUrl() . ' - session has google_auth_mode: ' . (session()->has('google_auth_mode') ? 'yes' : 'no'));
            return redirect()->route('login')
                ->with('error', 'An error occurred while signing in with Google.');
        }

        $mode = session('google_auth_mode', 'login');

        // Registration mode is disabled (see redirect()). A callback may only
        // ever log an existing user in.
        if ($mode !== 'login') {
            session()->forget('google_auth_mode');

            return redirect()->route('login')
                ->with('error', 'Account creation via Google is disabled.');
        }

        session()->forget('google_auth_mode');

        try {
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'No account is linked to this email address. Please create an account first.');
            }

            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            if (!$user->IsActive) {
                return redirect()->route('login')
                    ->with('error', 'Your account is not active. Please contact the administrator.');
            }

            Auth::login($user, remember: true);

            AuditLog::create([
                'UserID' => $user->id,
                'ActionType' => 'Login (Google)',
                'Description' => 'Google OAuth login via ' . $googleUser->getEmail(),
                'Timestamp' => now(),
                'IPAddress' => request()->ip(),
            ]);

            return redirect()->route('admin.dashboard');
        } catch (\Exception $e) {
            Log::error('Google login callback error: ' . get_class($e) . ' - ' . $e->getMessage() . ' - URL: ' . request()->fullUrl());
            return redirect()->route('login')
                ->with('error', 'An error occurred while setting up your account. Please try again.');
        }
    }
}
