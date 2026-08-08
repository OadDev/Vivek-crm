<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Emails seeded by DemoDataSeeder — safe to identify because they use
     * invented domains that cannot collide with a real imported lead, and
     * we only ever touch rows still tagged source=manual (the seeder never
     * sets excel_import/google_sheet), so a genuinely re-imported contact
     * that happens to reuse one of these addresses is left alone.
     */
    protected array $demoContactEmails = [
        'priya.sharma@nimbusretail.com',
        'rohan.mehta@bluepeak.io',
        'neha.k@kulkarnisons.in',
        'arjun.verma@vertexlog.com',
        'sanya@aurorahome.co',
        'vikram.singh@singhhw.com',
        'ananya.iyer@iyertextiles.com',
        'karan@malhotrasteel.in',
        'divya.nair@nairexports.com',
        'aditya.rao@raoconstruct.com',
    ];

    /**
     * Demo email threads not tied to any demo contact.
     */
    protected array $demoOnlyConversationEmails = [
        'sameer.joshi@newventure.co',
    ];

    public function up(): void
    {
        $contactIds = DB::table('contacts')
            ->whereIn('email', $this->demoContactEmails)
            ->where('source', 'manual')
            ->pluck('id');

        if ($contactIds->isNotEmpty()) {
            DB::table('whatsapp_messages')->whereIn('contact_id', $contactIds)->delete();
            DB::table('email_conversations')->whereIn('contact_id', $contactIds)->delete();
        }

        DB::table('email_conversations')
            ->whereIn('sender_email', array_merge($this->demoContactEmails, $this->demoOnlyConversationEmails))
            ->delete();

        DB::table('contacts')
            ->whereIn('email', $this->demoContactEmails)
            ->where('source', 'manual')
            ->delete();
    }

    public function down(): void
    {
        // Intentionally irreversible — this is a one-time data cleanup, not a schema change.
    }
};
