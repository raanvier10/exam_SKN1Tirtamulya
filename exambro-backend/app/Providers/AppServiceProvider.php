<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limit login per NIS/username + IP agar 1 WiFi sekolah / tunnel tidak saling memblokir
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower(trim((string) $request->input('username'))) . '|' . $request->ip();
            return Limit::perMinute(10)->by($key);
        });
    }
}
