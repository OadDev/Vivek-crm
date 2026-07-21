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
        Schema::create('copper_standards', function (Blueprint $table) {
            $table->id();
            $table->string('size_designation');
            $table->string('cross_section_sqmm')->nullable();
            $table->string('weight_per_km_kg')->nullable();
            $table->string('current_rating_amps')->nullable();
            $table->string('standard_reference')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copper_standards');
    }
};
