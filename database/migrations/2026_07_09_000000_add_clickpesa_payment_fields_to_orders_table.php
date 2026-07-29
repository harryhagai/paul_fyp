<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('currency');
            }

            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }

            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_email');
            }

            if (!Schema::hasColumn('orders', 'payment_provider')) {
                $table->string('payment_provider')->nullable()->after('customer_phone');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_provider');
            }

            if (!Schema::hasColumn('orders', 'clickpesa_client_id')) {
                $table->string('clickpesa_client_id')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'clickpesa_channel')) {
                $table->string('clickpesa_channel')->nullable()->after('clickpesa_client_id');
            }

            if (!Schema::hasColumn('orders', 'clickpesa_payment_id')) {
                $table->string('clickpesa_payment_id')->nullable()->after('clickpesa_channel');
            }

            if (!Schema::hasColumn('orders', 'clickpesa_payment_reference')) {
                $table->string('clickpesa_payment_reference')->nullable()->after('clickpesa_payment_id');
            }

            if (!Schema::hasColumn('orders', 'payment_message')) {
                $table->text('payment_message')->nullable()->after('clickpesa_payment_reference');
            }

            if (!Schema::hasColumn('orders', 'clickpesa_payload')) {
                $table->json('clickpesa_payload')->nullable()->after('payment_message');
            }

            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('clickpesa_payload');
            }

            if (!Schema::hasColumn('orders', 'payment_failed_at')) {
                $table->timestamp('payment_failed_at')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach ([
                'payment_failed_at',
                'paid_at',
                'clickpesa_payload',
                'payment_message',
                'clickpesa_payment_reference',
                'clickpesa_payment_id',
                'clickpesa_channel',
                'clickpesa_client_id',
                'payment_status',
                'payment_provider',
                'customer_phone',
                'customer_email',
                'customer_name',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
