<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// This is a deploy-time replacement for public/index.php, used when the only
// writable location on the host is a single public_html folder (no vhost/
// symlink control). The app itself (vendor, app, bootstrap, etc.) is synced
// to public_html/app/ instead of a sibling directory, and this file becomes
// public_html/index.php -- the paths below are one level deeper than stock
// Laravel's, and usePublicPath() tells Laravel this directory (not app/public)
// is the real public path.

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/app/bootstrap/app.php';
$app->usePublicPath(__DIR__);
$app->handleRequest(Request::capture());
