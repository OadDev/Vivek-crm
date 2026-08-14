<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->index('sales_man');
            $table->index('quotation_date');
            $table->index('email');
        });

        Schema::table('email_conversations', function (Blueprint $table) {
            $table->index(['folder', 'last_message_at']);
            $table->index('is_read');
            $table->index('is_starred');
            $table->index('sender_email');
        });

        Schema::table('email_messages', function (Blueprint $table) {
            $table->index('sent_at');
            $table->index('direction');
        });

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->index('sent_at');
        });

        Schema::table('products', function (Blueprint $table) {
            // 'code' already has a unique index from its create migration.
            $table->index('unit');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['sales_man']);
            $table->dropIndex(['quotation_date']);
            $table->dropIndex(['email']);
        });

        Schema::table('email_conversations', function (Blueprint $table) {
            $table->dropIndex(['folder', 'last_message_at']);
            $table->dropIndex(['is_read']);
            $table->dropIndex(['is_starred']);
            $table->dropIndex(['sender_email']);
        });

        Schema::table('email_messages', function (Blueprint $table) {
            $table->dropIndex(['sent_at']);
            $table->dropIndex(['direction']);
        });

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropIndex(['sent_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['unit']);
        });
    }
};
