<?php
// Lives at app/scripts/diagnostics/ once deployed. Writes a full JSON
// backup of every contact (including soft-deleted/archived) to a fixed
// path so the deploy workflow can scp it off the server before any
// destructive reset.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$contacts = App\Models\Contact::withTrashed()->get();

$dir = __DIR__.'/../../storage/app/backups';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$path = $dir.'/contacts_backup_latest.json';
file_put_contents($path, $contacts->toJson(JSON_PRETTY_PRINT));

echo json_encode([
    'backed_up_count' => $contacts->count(),
    'path' => $path,
    'bytes' => filesize($path),
], JSON_PRETTY_PRINT);
