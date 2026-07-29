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
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }

            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
        });

        Schema::table('checkout_information', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_information', 'shipping_region')) {
                $table->dropColumn('shipping_region');
            }

            if (Schema::hasColumn('checkout_information', 'delivery_location')) {
                $table->dropColumn('delivery_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('tax_amount');
            }
        });

        Schema::table('checkout_information', function (Blueprint $table) {
            if (!Schema::hasColumn('checkout_information', 'shipping_region')) {
                $table->string('shipping_region')->after('phone_number');
            }

            if (!Schema::hasColumn('checkout_information', 'delivery_location')) {
                $table->string('delivery_location')->after('shipping_region');
            }
        });
    }
};
