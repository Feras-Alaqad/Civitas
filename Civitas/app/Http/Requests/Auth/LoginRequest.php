<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $user = User::where('Username', $this->string('Username'))->first();

        if (! $user || ! Auth::attempt($this->only('Username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 60);

            AuditLog::create([
                'UserID' => null,
                'ActionType' => 'Failed Login Attempt',
                'Description' => 'Username: ' . $this->string('Username'),
                'IPAddress' => $this->ip(),
                'Timestamp' => now(),
            ]);

            throw ValidationException::withMessages([
                'Username' => trans('auth.failed'),
            ]);
        }

        $adminRole = DB::table('roles')->where('RoleName', 'Admin')->first();

        if (! $adminRole || $user->RoleID !== $adminRole->RoleID) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey(), 60);

            AuditLog::create([
                'UserID' => null,
                'ActionType' => 'Failed Login Attempt',
                'Description' => 'Non-admin access attempt: ' . $this->string('Username'),
                'IPAddress' => $this->ip(),
                'Timestamp' => now(),
            ]);

            throw ValidationException::withMessages([
                'Username' => 'You do not have admin access.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('Username')) . '|' . $this->ip());
    }
}
