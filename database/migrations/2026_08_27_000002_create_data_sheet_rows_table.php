<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sheet_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_sheet_id')->constrained()->cascadeOnDelete();
            $table->json('data');
            // Every cell value flattened + lowercased, so "searchable by all
            // columns" is one LIKE query instead of one OR-clause per column
            // (which would have to change shape per sheet's own headers).
            $table->text('search_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['data_sheet_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sheet_rows');
    }
};
