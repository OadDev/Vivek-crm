<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Normal installs go through the Setup Wizard (/setup), which creates
     * the admin account and seeds CopperStandardSeeder (+ DemoDataSeeder if
     * requested) itself. This entry point is only for developers running
     * `php artisan db:seed` directly against an already-migrated database.
     */
    public function run(): void
    {
        $this->call([
            CopperStandardSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
