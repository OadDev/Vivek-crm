<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_type')->default('google_sheet'); // google_sheet|excel_upload
            $table->string('google_sheet_url')->nullable();
            $table->string('excel_file_path')->nullable();
            $table->string('excel_original_name')->nullable();
            $table->json('headers')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sheets');
    }
};
