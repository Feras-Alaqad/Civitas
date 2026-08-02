<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'Username' => ['required', 'string', 'exists:users,Username'],
        ], [
            'Username.exists' => 'This username does not exist in our system.',
        ]);

        $user = User::where('Username', $request->Username)->first();

        DB::table('password_resets')->where('Username', $request->Username)->delete();

        $token = Str::random(60);

        DB::table('password_resets')->insert([
            'Username' => $request->Username,
            'token' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        $resetLink = route('password.reset', ['token' => $token, 'Username' => $request->Username]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link sent!',
                'reset_url' => $resetLink,
            ]);
        }

        return back()->with('status', 'Password reset link sent!');
    }
}
