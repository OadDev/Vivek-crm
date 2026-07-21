<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Checks every minute whether the configured sync interval has elapsed
// (the command itself is a no-op if auto-sync is disabled or not due yet).
Schedule::command('contacts:sync')->everyMinute()->withoutOverlapping();

// Active -> Follow-up after 7 days, -> Inactive after 20 days without contact.
Schedule::command('contacts:recalculate-statuses')->daily();
