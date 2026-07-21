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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->unique();
            $table->string('whatsapp')->nullable();
            $table->string('designation')->nullable();
            $table->enum('status', ['active', 'follow_up', 'inactive'])->default('active');
            $table->boolean('is_starred')->default(false);
            $table->timestamp('last_contacted_at')->nullable();
            $table->enum('source', ['manual', 'excel_import', 'google_sheet'])->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['is_starred']);
            $table->index(['last_contacted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
