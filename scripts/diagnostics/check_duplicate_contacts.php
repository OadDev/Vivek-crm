<?php
// Lives at app/scripts/diagnostics/ once deployed -- two levels up from the app root.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$groups = App\Models\Contact::select('id', 'company', 'source', 'quote_no', 'email', 'whatsapp', 'quotation_date')
    ->whereNotNull('company')
    ->where('company', '!=', '')
    ->get()
    ->groupBy('company');

$duplicateGroups = $groups->filter(fn ($group) => $group->count() > 1);

$isStale = fn ($contact) => $contact->source === 'excel_import'
    && empty($contact->quote_no)
    && empty($contact->quotation_date);

$staleGroups = $duplicateGroups->filter(
    fn ($group) => $group->contains($isStale) && $group->contains(fn ($c) => ! $isStale($c))
);

echo json_encode([
    'duplicate_company_groups' => $duplicateGroups->count(),
    'stale_pattern_groups' => $staleGroups->count(),
    'contacts_in_stale_groups' => $staleGroups->flatten(1)->count(),
    'sample_companies' => $staleGroups->keys()->take(20)->values(),
], JSON_PRETTY_PRINT);
