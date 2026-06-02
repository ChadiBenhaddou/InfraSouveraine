<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('use_case')->nullable();
            $table->string('recommended_model_id')->nullable();
            $table->string('selected_gpu_tier')->nullable();
            $table->string('subscription_status', 50)->default('pending');
            $table->string('stripe_customer_id')->nullable()->unique();
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->decimal('monthly_subscription_price', 10, 2)->nullable();
            $table->decimal('base_monthly_cost', 10, 2)->nullable();
            $table->decimal('benefit_margin_rate', 5, 4)->nullable();
            $table->decimal('fixed_markup', 10, 2)->nullable();
            $table->decimal('actual_raw_cost_incurred', 12, 2)->default(0);
            $table->decimal('profit_generated', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
