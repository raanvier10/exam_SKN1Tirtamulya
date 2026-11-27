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

        // Rate limit laporan pelanggaran browser per akun siswa + IP
        RateLimiter::for('web-violation', function (Request $request) {
            $userKey = $request->user()?->id ?? $request->session()->getId();
            return Limit::perMinute(60)->by($userKey . '|' . $request->ip());
        });

        // Rate limit verifikasi kode masuk per siswa + ujian + IP
        RateLimiter::for('exam-code', function (Request $request) {
            $userKey = $request->user()?->id ?? $request->session()->getId();
            $examKey = $request->route('exam')?->id ?? '0';
            return Limit::perMinute(10)->by($userKey . '|' . $examKey . '|' . $request->ip());
        });
    }
}
