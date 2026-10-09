<?php

// error-visibility note: this script deliberately forces errors to print
// in the response instead of letting the host's generic error page (or a
// blank 500) swallow them -- there's no other way to see what went wrong
// on hosting with no log/terminal access. Safe here because the whole
// endpoint is already token-gated.
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        header('Content-Type: text/plain');
        echo "\n\n--- FATAL ERROR ---\n";
        echo $error['message']."\n";
        echo 'in '.$error['file'].' on line '.$error['line']."\n";
    }
});

// deploy-finalize.php -- deployed to the webroot (next to index.php), for
// hosting with no SSH, no panel/cron access, and no terminal available to
// anyone involved -- FTP is the only capability. Visiting this file in a
// browser runs the exact same app:deploy-finalize logic a cron job would
// (bootstrap .env on first run, run pending migrations, rebuild caches),
// via Laravel's console kernel directly -- this deliberately bypasses the
// HTTP kernel/middleware stack, which can't boot before .env/APP_KEY
// exist (see App\Console\Commands\DeployFinalize and app-js.blade.php's
// sibling discussion of this). Safe to run repeatedly.
//
// Protected by a token that is NEVER committed to this (public) repo --
// it's read from app/deploy_token.txt (app/ already exists once the code
// bundle is uploaded, unlike storage/ subfolders which may not if hidden
// files were skipped during a manual FTP upload), a file you create
// yourself and upload separately via FTP. Visit:
//   https://your-domain/deploy-finalize.php?token=YOUR_SECRET
//
// Delete this file (and the token file) once setup is complete -- it's
// harmless to leave since it's token-gated and the underlying command is
// idempotent, but removing it is better hygiene.

$appDir = __DIR__.'/app';
$tokenFile = $appDir.'/deploy_token.txt';

if (! file_exists($tokenFile)) {
    http_response_code(403);
    exit("Not configured -- upload app/deploy_token.txt first (a plain text file containing a secret string you choose).\n");
}

$expected = trim(file_get_contents($tokenFile));
$provided = (string) ($_GET['token'] ?? '');

if ($expected === '' || ! hash_equals($expected, $provided)) {
    http_response_code(403);
    exit("Forbidden.\n");
}

header('Content-Type: text/plain');

try {
    require $appDir.'/vendor/autoload.php';
    $app = require $appDir.'/bootstrap/app.php';

    /** @var \Illuminate\Contracts\Console\Kernel $kernel */
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $output = new Symfony\Component\Console\Output\BufferedOutput;
    Illuminate\Support\Facades\Artisan::call('app:deploy-finalize', [], $output);
    echo $output->fetch();
} catch (Throwable $e) {
    echo "--- EXCEPTION ---\n";
    echo get_class($e).': '.$e->getMessage()."\n";
    echo 'in '.$e->getFile().' on line '.$e->getLine()."\n\n";
    echo $e->getTraceAsString()."\n";
}
