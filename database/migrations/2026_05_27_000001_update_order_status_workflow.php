<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'stock_deducted_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('stock_deducted_at')->nullable()->after('estimated_delivery_date');
            });
        }

        DB::table('orders')
            ->whereIn('status', ['processing', 'preparing', 'ready_for_pickup'])
            ->update(['status' => 'confirmed']);

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','processing','preparing','ready_for_pickup','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }

        if (Schema::hasColumn('orders', 'stock_deducted_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('stock_deducted_at');
            });
        }
    }
};
