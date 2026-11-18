<?php
// Lives at app/scripts/diagnostics/ once deployed -- two levels up from the app root.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mirrors ContactController::index()'s default view exactly: pipeline scope
// (not archived, not won) + filter_status defaulting to 'active'.
$activeContacts = App\Models\Contact::query()
    ->where('is_archived', false)
    ->where('is_won', false)
    ->where('status', 'active')
    ->get(['id', 'company', 'name', 'source', 'quote_no', 'quotation_date']);

$activeGroups = $activeContacts->groupBy(fn ($c) => $c->company ?: $c->name);

// Within the Active tab's own grouping, which "primary" rows (the one
// actually shown per company) have no date, even though another active
// record for the same company does?
$missingWithinActive = $activeGroups->filter(function ($group) {
    $primary = $group->sortByDesc(fn ($c) => optional($c->quotation_date)->timestamp ?? 0)->first();

    return empty($primary->quotation_date) && $group->contains(fn ($c) => ! empty($c->quotation_date));
});

// Companies where the Active tab shows a dateless record, but a
// *different-status* record for the same company (filtered out of this
// view entirely) actually has the date -- the "Becon" pattern, generalized
// beyond just excel_import-sourced duplicates.
$allContactsByCompany = App\Models\Contact::query()
    ->whereNotNull('company')->where('company', '!=', '')
    ->get(['id', 'company', 'status', 'source', 'quote_no', 'quotation_date'])
    ->groupBy('company');

$hiddenElsewhere = $activeGroups->filter(function ($group, $company) use ($allContactsByCompany) {
    $primary = $group->sortByDesc(fn ($c) => optional($c->quotation_date)->timestamp ?? 0)->first();
    if (! empty($primary->quotation_date)) {
        return false;
    }

    $allForCompany = $allContactsByCompany->get($company, collect());

    return $allForCompany->contains(fn ($c) => $c->status !== 'active' && ! empty($c->quotation_date));
});

echo json_encode([
    'active_tab_total_contacts' => $activeContacts->count(),
    'active_tab_company_groups' => $activeGroups->count(),
    'active_groups_with_no_date_at_all' => $activeGroups->filter(fn ($g) => $g->every(fn ($c) => empty($c->quotation_date)))->count(),
    'active_groups_missing_date_but_sibling_has_one_within_active' => $missingWithinActive->count(),
    'active_groups_missing_date_but_dated_record_hidden_in_other_status' => $hiddenElsewhere->count(),
    'sample_hidden_elsewhere_companies' => $hiddenElsewhere->keys()->take(15)->values(),
], JSON_PRETTY_PRINT);
