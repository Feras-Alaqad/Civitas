<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('cache:warm-citizens --pages=5')
    ->everyFourMinutes()
    ->withoutOverlapping();

Schedule::command('cache:warm-dashboard')
    ->everyFourMinutes()
    ->withoutOverlapping();

Schedule::command('citizens:refresh-cache')
    ->everyFiveSeconds()
    ->withoutOverlapping();

Schedule::command('imports:resume-stalled')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('meilisearch:import --resume --chunk=5000 --timeout=300')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
