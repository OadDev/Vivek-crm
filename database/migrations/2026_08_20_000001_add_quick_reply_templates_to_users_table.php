<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('quick_reply_f1')->nullable()->after('whatsapp_default_template_id');
            $table->text('quick_reply_f2')->nullable()->after('quick_reply_f1');
            $table->text('quick_reply_f3')->nullable()->after('quick_reply_f2');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['quick_reply_f1', 'quick_reply_f2', 'quick_reply_f3']);
        });
    }
};
