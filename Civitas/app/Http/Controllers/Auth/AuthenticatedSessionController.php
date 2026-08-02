<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        DB::table('audit_logs')->insert([
            'LogID' => \Str::uuid()->toString(),
            'UserID' => Auth::id(),
            'ActionType' => 'Login',
            'Description' => 'Username: ' . $request->string('Username'),
            'Timestamp' => now(),
            'IPAddress' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            if ($user) {
                DB::table('audit_logs')->insert([
                    'LogID' => \Str::uuid()->toString(),
                    'UserID' => $user->id,
                    'ActionType' => 'Logout',
                    'Description' => 'Username: ' . $user->Username,
                    'Timestamp' => now(),
                    'IPAddress' => $request->ip(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Logout audit log failed: ' . $e->getMessage());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
