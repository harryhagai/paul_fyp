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
        // PRODUCTS TABLE INDEXES
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('slug');
            $table->index('new_price');
            $table->index('discount');
            $table->index('rate');
            $table->index('stock');
            $table->index('is_advertised');
        });

        // CATEGORIES TABLE INDEXES
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
        });

        // PRODUCT_MEDIA TABLE INDEXES
        Schema::table('product_media', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('type');
            $table->index('is_primary');
        });

        // PRODUCT_DESCRIPTIONS TABLE INDEXES
        Schema::table('product_descriptions', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        // DROP INDEXES (Safe rollback)
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['slug']);
            $table->dropIndex(['new_price']);
            $table->dropIndex(['discount']);
            $table->dropIndex(['rate']);
            $table->dropIndex(['stock']);
            $table->dropIndex(['is_advertised']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('product_media', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['is_primary']);
        });

        Schema::table('product_descriptions', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};
