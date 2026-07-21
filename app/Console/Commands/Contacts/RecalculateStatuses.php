<?php

namespace App\Console\Commands\Contacts;

use App\Models\Activity;
use App\Models\Contact;
use Illuminate\Console\Command;

class RecalculateStatuses extends Command
{
    /**
     * Active -> Follow-up after 7 days without contact, -> Inactive after 20 days.
     * Manually set statuses still get re-evaluated daily against last_contacted_at,
     * so the automation stays authoritative over time.
     *
     * @var string
     */
    protected $signature = 'contacts:recalculate-statuses';

    protected $description = 'Recalculate contact statuses (Active / Follow-up / Inactive) from last_contacted_at';

    public function handle(): int
    {
        $changed = 0;

        Contact::query()->chunkById(200, function ($contacts) use (&$changed) {
            foreach ($contacts as $contact) {
                $newStatus = Contact::computeStatusFromDate($contact->last_contacted_at);

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
