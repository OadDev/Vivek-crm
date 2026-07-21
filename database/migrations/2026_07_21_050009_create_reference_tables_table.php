<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic reference-table store for the Product Master "standard
     * reference" popup. Each row is one named table (e.g. "ASTM B88 Type K —
     * Dimensions (inches)") with its own column headers and data rows,
     * since real copper/tube standards tables don't share a common shape.
     * Headers/rows are edited in bulk via paste-from-spreadsheet, matching
     * how this data is actually maintained (copied out of Google Sheets).
     */
    public function up(): void
    {
        Schema::create('reference_tables', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('copper');
            $table->string('title');
            $table->string('description')->nullable();
            $table->json('headers');
            $table->json('rows');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_tables');
    }
};
