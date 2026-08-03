<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower($request->input('Username') ?? '') . '|' . $request->ip());
            $attempts = RateLimiter::attempts($key);

            $decayMinutes = match (true) {
                $attempts >= 20 => 60,
                $attempts >= 10 => 15,
                default => 1,
            };

            return Limit::perMinutes($decayMinutes, 5)->by($key);
        });
    }
}
