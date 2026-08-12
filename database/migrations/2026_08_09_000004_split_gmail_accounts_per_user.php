<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Moves Gmail from one shared connection to one-per-user. NULL user_id
     * marks the row holding the shared Google OAuth Client ID/Secret (the
     * one Google Cloud app every user's "Connect Gmail" authorizes against);
     * a set user_id marks that user's own inbox connection (tokens, email).
     *
     * Whatever was connected before this migration becomes the first
     * admin's personal connection, and its Client ID/Secret are copied into
     * a new shared-config row so every other user's Connect flow keeps
     * working without re-entering them.
     */
    public function up(): void
    {
        // cascadeOnDelete (not nullOnDelete): a NULL user_id is reserved to
        // mean "the shared OAuth client row" (see sharedClient() below) — if
        // a deleted user's row were nulled instead of removed, it would
        // collide with that row and corrupt the shared credentials.
        Schema::table('gmail_accounts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('email_conversations', function (Blueprint $table) {
            $table->foreignId('gmail_account_id')->nullable()->after('contact_id')->constrained()->nullOnDelete();
        });

        $legacy = DB::table('gmail_accounts')->orderBy('id')->first();

        if ($legacy && ($legacy->client_id || $legacy->refresh_token)) {
            $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

            DB::table('gmail_accounts')->insert([
                'user_id' => null,
                'client_id' => $legacy->client_id,
                'client_secret' => $legacy->client_secret,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($adminId) {
                DB::table('gmail_accounts')->where('id', $legacy->id)->update(['user_id' => $adminId]);

                DB::table('email_conversations')
                    ->whereNull('gmail_account_id')
                    ->update(['gmail_account_id' => $legacy->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('email_conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gmail_account_id');
        });

        Schema::table('gmail_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
