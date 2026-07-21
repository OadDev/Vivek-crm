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
        Schema::create('contact_sync_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['excel_upload', 'google_sheet'])->default('google_sheet');
            $table->string('excel_file_path')->nullable();
            $table->string('excel_original_name')->nullable();
            $table->string('google_sheet_url')->nullable();
            $table->unsignedInteger('interval_minutes')->default(5);
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_sync_settings');
    }
};
