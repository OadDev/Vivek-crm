<?php
// Lives at app/scripts/diagnostics/ once deployed. Permanently deletes
// every contact row (hard delete, bypassing soft-deletes) -- run only
// after backup_contacts.php has produced a verified backup. Real FK
// constraints on contact_id (reminders: cascade, whatsapp_messages /
// email_conversations: set null) are enforced by the DB engine itself,
// so a single bulk delete leaves no orphaned rows.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$before = App\Models\Contact::withTrashed()->count();

App\Models\Contact::withTrashed()->forceDelete();

$after = App\Models\Contact::withTrashed()->count();

echo json_encode([
    'contacts_before' => $before,
    'contacts_after' => $after,
], JSON_PRETTY_PRINT);
