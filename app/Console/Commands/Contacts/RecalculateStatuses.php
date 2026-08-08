<?php

namespace App\Console\Commands\Contacts;

use App\Models\Activity;
use App\Models\Contact;
use Illuminate\Console\Command;

class RecalculateStatuses extends Command
{
    /**
     * Active -> Follow-up after 7 days from the quotation date, -> Inactive after 2 months.
     * Manually set statuses still get re-evaluated daily, so the automation stays
     * authoritative over time. Won/archived leads are frozen and skipped.
     *
     * @var string
     */
    protected $signature = 'contacts:recalculate-statuses';

    protected $description = 'Recalculate lead statuses (Active / Follow-up / Inactive) from the quotation date';

    public function handle(): int
    {
        $changed = 0;

        Contact::query()->where('is_won', false)->where('is_archived', false)
            ->chunkById(200, function ($contacts) use (&$changed) {
                foreach ($contacts as $contact) {
                    $newStatus = Contact::computeStatusFromDate($contact->quotation_date ?? $contact->last_contacted_at);

                    if ($newStatus !== $contact->status) {
                        $contact->update(['status' => $newStatus]);
                        $changed++;
                    }
                }
            });

        if ($changed > 0) {
            Activity::log("Follow-up automation updated status for {$changed} contact(s)", 'bi-arrow-repeat', 'warning');
        }

        $this->info("Recalculated statuses. {$changed} contact(s) updated.");

        return self::SUCCESS;
    }
}
