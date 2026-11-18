<?php
// Lives at app/scripts/diagnostics/ once deployed. Turns off the manual
// Excel-import button/route and the "Uploaded Excel File" sync source for
// this install only (Setting is per-database, so other installs of this
// same codebase are unaffected), and forces the recurring sync to
// Google Sheet so a stray excel_upload setting can't resurrect the
// duplicate-record problem.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\Setting::set('contacts_import_enabled', false);

$setting = App\Models\ContactSyncSetting::current();
$setting->update(['source_type' => 'google_sheet']);

echo json_encode([
    'contacts_import_enabled' => App\Models\Setting::get('contacts_import_enabled'),
    'sync_source_type' => $setting->fresh()->source_type,
    'google_sheet_url' => $setting->fresh()->google_sheet_url,
], JSON_PRETTY_PRINT);
