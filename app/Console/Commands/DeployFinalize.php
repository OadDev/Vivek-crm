<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployFinalize extends Command
{
    /**
     * Finishes what an FTP-only deploy can't do itself (no SSH access on
     * this hosting account -- see .github/workflows/deploy-copperfitting.yml).
     * Meant to be triggered by a cron job on the server after each deploy;
     * safe to run repeatedly/on a schedule since every step here is a no-op
     * once it's already done.
     *
     * @var string
     */
    protected $signature = 'app:deploy-finalize';

    protected $description = 'Bootstrap .env, ensure storage dirs exist, and run pending migrations/cache rebuild after an FTP deploy';

    public function handle(): int
    {
        $this->bootstrapEnv();
        $this->ensureStorageDirectories();

        if (! file_exists(storage_path('app/installed.lock'))) {
            $this->info('App not installed yet -- visit /setup to finish installation. Skipping migrate/cache for now.');

            return self::SUCCESS;
        }

        Artisan::call('migrate', ['--force' => true], $this->output);
        Artisan::call('config:cache', [], $this->output);
        Artisan::call('route:cache', [], $this->output);
        Artisan::call('view:cache', [], $this->output);
        Artisan::call('event:cache', [], $this->output);

        $this->info('Deploy finalize complete.');

        return self::SUCCESS;
    }

    protected function bootstrapEnv(): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            return;
        }

        $this->info('No .env found -- bootstrapping one from .env.example');

        copy(base_path('.env.example'), $path);

        Artisan::call('key:generate', ['--force' => true], $this->output);
    }

    protected function ensureStorageDirectories(): void
    {
        $dirs = [
            storage_path('app/private'),
            storage_path('app/public'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            @chmod($dir, 0775);
        }
    }
}
