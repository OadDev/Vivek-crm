<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sales_man')->nullable()->after('role');
            $table->text('html_signature')->nullable()->after('phone');
            $table->foreignId('whatsapp_default_template_id')->nullable()->after('html_signature')
                ->constrained('whatsapp_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('whatsapp_default_template_id');
            $table->dropColumn(['sales_man', 'html_signature']);
        });
    }
};
