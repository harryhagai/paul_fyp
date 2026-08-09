<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('robot_arm_commands', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->nullable()->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_reference')->nullable()->index();
            $table->string('command', 20);
            $table->unsignedSmallInteger('location')->nullable();
            $table->string('status', 30)->default('PENDING')->index();
            $table->string('error')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['command', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('robot_arm_commands');
    }
};
