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
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'country')) {
                $table->dropColumn('country');
            }
        });

        Schema::table('order_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('order_addresses', 'country')) {
                $table->dropColumn('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'country')) {
                $table->string('country')->default('Tanzania')->after('postal_code');
            }
        });

        Schema::table('order_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('order_addresses', 'country')) {
                $table->string('country')->default('Tanzania')->after('postal_code');
            }
        });
    }
};
