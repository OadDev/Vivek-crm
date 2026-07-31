<?php

namespace App\Console\Commands\Gmail;

use App\Models\GmailAccount;
use App\Services\GmailApiService;
use Illuminate\Console\Command;
use Throwable;

class SyncGmailInbox extends Command
{
    /**
     * @var string
     */
    protected $signature = 'gmail:sync';

    protected $description = 'Pull the most recent inbox messages from the connected Gmail account';

    public function handle(GmailApiService $service): int
    {
        $account = GmailAccount::current();

        if (! $account->isConnected()) {
            $this->comment('No Gmail account connected — skipping.');

            return self::SUCCESS;
        }

        try {
            $result = $service->syncInbox();
            $this->info("Synced Gmail inbox: {$result['created']} new, {$result['skipped']} skipped.");
        } catch (Throwable $e) {
            $account->update(['last_sync_status' => 'failed', 'last_sync_message' => $e->getMessage()]);
            $this->warn('Gmail sync failed: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
