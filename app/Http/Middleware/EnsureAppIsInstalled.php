<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureAppIsInstalled
{
    /**
     * Redirects every request to the Setup Wizard until the app has been
     * installed (MySQL credentials configured, migrated, admin created).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('app/installed.lock'))
            || $this->detectExistingInstallOnSharedDatabase();

        if (! $installed && ! $request->routeIs('setup.*')) {
            return redirect()->route('setup.index');
        }

        if ($installed && $request->routeIs('setup.*')) {
            return redirect()->route('login');
        }

        return $next($request);
    }

    /**
     * This machine's own installed.lock file won't exist the first time it
     * points at a database another install already set up (e.g. a local
     * XAMPP install pointed at the already-live Hostinger database). If the
     * users table already has rows, someone already completed setup on this
     * shared database — adopt that as "installed" here too instead of
     * showing the wizard again (which would offer to re-seed demo data and
     * create a duplicate admin).
     */
    protected function detectExistingInstallOnSharedDatabase(): bool
    {
        try {
            if (Schema::hasTable('users') && DB::table('users')->exists()) {
                @touch(storage_path('app/installed.lock'));

                return true;
            }
        } catch (Throwable) {
            // DB unreachable or not configured yet — fall through to the
            // normal setup-wizard flow (or, if further along, the global
            // QueryException handler will show a friendly offline page).
        }

        return false;
    }
}
