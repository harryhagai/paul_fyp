<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('robot_arm_commands', function (Blueprint $table): void {
            $table->uuid('batch_id')->nullable()->after('order_reference')->index();
            $table->foreignId('order_item_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('sequence')->nullable()->after('location');
            $table->unsignedInteger('total')->nullable()->after('sequence');

            $table->index(['batch_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('robot_arm_commands', function (Blueprint $table): void {
            $table->dropForeign(['order_item_id']);
            $table->dropIndex(['batch_id', 'sequence']);
            $table->dropIndex(['batch_id']);
            $table->dropColumn(['batch_id', 'order_item_id', 'sequence', 'total']);
        });
    }
};
