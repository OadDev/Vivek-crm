<?php

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Support\DatabaseConnectivity;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureAppIsInstalled::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A dead/unreachable DB host (e.g. the local machine's internet is
        // down and it can't reach the shared Hostinger MySQL server) should
        // show a friendly message instead of a raw error page. A real query
        // bug (bad SQL, constraint violation) still surfaces normally.
        $exceptions->render(function (QueryException $e, $request) {
            if (DatabaseConnectivity::isConnectivityFailure($e)) {
                return response()->view('errors.db-unavailable', [], 503);
            }
        });
    })->create();
