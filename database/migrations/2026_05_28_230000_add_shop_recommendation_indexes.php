<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['stock', 'rate', 'views', 'created_at'], 'products_shop_rank_idx');
        });

        Schema::table('product_views', function (Blueprint $table) {
            $table->index(['product_id', 'last_activity_at'], 'pv_product_activity_idx');
            $table->index(['user_id', 'last_activity_at'], 'pv_user_activity_idx');
            $table->index(['product_id', 'viewed_seconds'], 'pv_product_viewed_seconds_idx');
            $table->index(['session_id', 'created_at'], 'pv_session_created_idx');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->index(['user_id', 'product_id'], 'wishlists_user_product_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'ordered_at'], 'orders_user_ordered_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'product_id'], 'order_items_order_product_idx');
            $table->index(['product_id'], 'order_items_product_idx');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->index(['product_id', 'rating'], 'ratings_product_rating_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex('ratings_product_rating_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_idx');
            $table->dropIndex('order_items_order_product_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_ordered_at_idx');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('wishlists_user_product_idx');
        });

        Schema::table('product_views', function (Blueprint $table) {
            $table->dropIndex('pv_session_created_idx');
            $table->dropIndex('pv_product_viewed_seconds_idx');
            $table->dropIndex('pv_user_activity_idx');
            $table->dropIndex('pv_product_activity_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_shop_rank_idx');
        });
    }
};

