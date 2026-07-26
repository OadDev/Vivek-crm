<?php
// Temporary, read-only diagnostic for the Hostinger deploy pipeline.
// Reveals whether PHP-FPM can actually traverse the public_html -> laravel-app
// symlink (open_basedir on shared hosting is often scoped to the domain's own
// folder and silently blocks exactly this). Safe to delete once deploys work.
header('Content-Type: text/plain');
echo "PHP version: " . phpversion() . "\n";
echo "open_basedir: " . var_export(ini_get('open_basedir'), true) . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? '?') . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "realpath(__DIR__): " . realpath(__DIR__) . "\n";
echo "is_link(__DIR__ parent public_html)?: " . (is_link(dirname(realpath(__FILE__))) ? 'yes' : 'n/a') . "\n";
echo "vendor/autoload.php readable: " . (is_readable(__DIR__ . '/../vendor/autoload.php') ? 'yes' : 'no') . "\n";
echo ".env readable: " . (is_readable(__DIR__ . '/../.env') ? 'yes' : 'no') . "\n";
if (is_readable(__DIR__ . '/../.env')) {
    $env = file_get_contents(__DIR__ . '/../.env');
    echo "APP_KEY set: " . (preg_match('/^APP_KEY=base64:.+/m', $env) ? 'yes' : 'NO') . "\n";
}
echo "storage/logs writable: " . (is_writable(__DIR__ . '/../storage/logs') ? 'yes' : 'no') . "\n";
