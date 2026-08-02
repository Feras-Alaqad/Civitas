<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $mode = $request->query('mode', 'login');
        session(['google_auth_mode' => $mode]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'An error occurred while signing in with Google.');
        }

        $mode = session('google_auth_mode', 'login');

        try {
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($mode === 'register') {
                if ($user) {
                    Auth::login($user, remember: true);

                    AuditLog::create([
                        'UserID' => $user->id,
                        'ActionType' => 'Login (Google)',
                        'Description' => 'Google OAuth login via ' . $googleUser->getEmail(),
                        'Timestamp' => now(),
                        'IPAddress' => request()->ip(),
                    ]);

                    return redirect()->route('admin.dashboard');
                }

                $defaultRole = DB::table('roles')->where('RoleName', 'DataEntry')->first();

                $user = User::create([
                    'Username' => $googleUser->getName() ?? $googleUser->getNickname() ?? Str::before($googleUser->getEmail(), '@'),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(Str::random(32)),
                    'RoleID' => $defaultRole?->RoleID,
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'IsActive' => true,
                ]);

                AuditLog::create([
                    'UserID' => $user->id,
                    'ActionType' => 'Register (Google)',
                    'Description' => 'New user registered via Google OAuth: ' . $googleUser->getEmail(),
                    'Timestamp' => now(),
                    'IPAddress' => request()->ip(),
                ]);

                Auth::login($user, remember: true);

                return redirect()->route('admin.dashboard');
            }

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
            Log::error('Google login callback error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'An error occurred while setting up your account. Please try again.');
        }
    }
}
