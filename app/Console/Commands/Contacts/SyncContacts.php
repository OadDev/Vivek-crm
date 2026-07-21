<?php

namespace App\Console\Commands\Contacts;

use App\Services\ContactSyncService;
use Illuminate\Console\Command;

class SyncContacts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'contacts:sync {--force : Ignore the configured interval and is_enabled flag}';

    protected $description = 'Sync contacts from the configured Excel file or Google Sheet data source';

    public function handle(ContactSyncService $service): int
    {
        $result = $service->run(force: $this->option('force'));

        if ($result['success']) {
            $this->info($result['message']);
        } else {
            $this->warn($result['message']);
        }

        return self::SUCCESS;
    }
}
