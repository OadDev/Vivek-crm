<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_conversations', function (Blueprint $table) {
            $table->string('gmail_thread_id')->nullable()->unique()->after('contact_id');
        });

        Schema::table('email_messages', function (Blueprint $table) {
            $table->string('gmail_message_id')->nullable()->unique()->after('email_conversation_id');
            $table->string('message_id_header')->nullable()->after('gmail_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_conversations', function (Blueprint $table) {
            $table->dropColumn('gmail_thread_id');
        });

        Schema::table('email_messages', function (Blueprint $table) {
            $table->dropColumn(['gmail_message_id', 'message_id_header']);
        });
    }
};
