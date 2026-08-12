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

    protected $description = 'Pull new inbox messages for every connected Gmail account';

    public function handle(GmailApiService $service): int
    {
        $accounts = GmailAccount::whereNotNull('user_id')->whereNotNull('refresh_token')->get();

        if ($accounts->isEmpty()) {
            $this->comment('No Gmail accounts connected — skipping.');

            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            try {
                $result = $service->syncInbox($account);
                $this->info("[{$account->email}] Synced: {$result['created']} new, {$result['skipped']} skipped.");
            } catch (Throwable $e) {
                $account->update(['last_sync_status' => 'failed', 'last_sync_message' => $e->getMessage()]);
                $this->warn("[{$account->email}] Gmail sync failed: ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
