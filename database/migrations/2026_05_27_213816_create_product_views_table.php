<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 120)->index();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedInteger('view_count')->default(1);
            $table->unsignedInteger('viewed_seconds')->default(0);
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamps();

            $table->index(['product_id', 'session_id', 'created_at'], 'product_views_product_session_created_idx');
            $table->index(['user_id', 'last_activity_at'], 'product_views_user_activity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
