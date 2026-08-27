<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller
{
    /**
     * A tiny, silent ping the browser sends every few minutes while any
     * page is open (see layouts/app.blade.php). It runs the same sync
     * commands a server cron job would otherwise call on a schedule --
     * this app has no server cron access, so this is what actually
     * triggers automatic syncing instead. Each Cache::add() below only
     * lets the matching command through once per cooldown window, no
     * matter how many browser tabs/users are pinging at once; the
     * commands themselves stay the source of truth for whether they
     * actually have anything to do (e.g. contacts:sync still checks its
     * own configured interval and enabled flag).
     */
    public function heartbeat()
    {
        if (Cache::add('heartbeat:contacts-sync', true, 60)) {
            Artisan::call('contacts:sync');
        }

        if (Cache::add('heartbeat:gmail-sync', true, 240)) {
            Artisan::call('gmail:sync');
        }

        if (Cache::add('heartbeat:status-recalc', true, 20 * 3600)) {
            Artisan::call('contacts:recalculate-statuses');
        }

        return response()->noContent();
    }
}
