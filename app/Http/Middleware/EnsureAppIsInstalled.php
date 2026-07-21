<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $installed = file_exists(storage_path('app/installed.lock'));

        if (! $installed && ! $request->routeIs('setup.*')) {
            return redirect()->route('setup.index');
        }

        if ($installed && $request->routeIs('setup.*')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
