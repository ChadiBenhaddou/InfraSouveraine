<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_price_id')->unique()->nullable();
            $table->string('gpu_tier'); // RTX_4090, A100_80GB, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_hourly_rate', 10, 4);
            $table->decimal('storage_cost_monthly', 10, 2)->default(5.00);
            $table->decimal('benefit_margin_rate', 5, 4)->default(0.35);
            $table->decimal('fixed_markup', 10, 2)->default(9.99);
            $table->decimal('monthly_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
