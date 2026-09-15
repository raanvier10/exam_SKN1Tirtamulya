<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-publish: draft (inactive) exams yang start_at sudah tercapai → aktifkan otomatis
Schedule::call(function () {
    \App\Models\Exam::where('status', 'inactive')
        ->where('start_at', '<=', now())
        ->update(['status' => 'active']);
})->everyMinute()->name('auto-publish-exams');
