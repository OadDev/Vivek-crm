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
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('quote_no')->nullable()->unique()->after('id');
            $table->date('quotation_date')->nullable()->after('quote_no');
            $table->string('sales_man')->nullable()->after('designation');
            $table->string('gst_number')->nullable()->after('sales_man');
            $table->string('transport')->nullable()->after('gst_number');
            $table->text('shipping_address')->nullable()->after('transport');
            $table->string('stage')->nullable()->after('shipping_address');
            $table->string('priority')->nullable()->after('stage');
            $table->boolean('is_archived')->default(false)->after('is_starred');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->boolean('is_won')->default(false)->after('archived_at');
            $table->timestamp('won_at')->nullable()->after('is_won');
            $table->softDeletes();

            $table->index(['is_archived']);
            $table->index(['is_won']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('contacts_email_unique');
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['is_archived']);
            $table->dropIndex(['is_won']);
            $table->dropColumn([
                'quote_no', 'quotation_date', 'sales_man', 'gst_number', 'transport',
                'shipping_address', 'stage', 'priority', 'is_archived', 'archived_at',
                'is_won', 'won_at',
            ]);
        });
    }
};
