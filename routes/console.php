<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription expiry command to run hourly
Schedule::command('subscriptions:expire-overdue')->hourly()
    ->description('Expire overdue subscriptions')
    ->withoutOverlapping();
