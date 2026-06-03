<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex('plans_stripe_price_id_unique');
            $table->dropColumn('stripe_price_id');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('tenants_stripe_customer_id_unique');
            $table->dropIndex('tenants_stripe_subscription_id_unique');
            $table->dropColumn(['stripe_customer_id', 'stripe_subscription_id']);
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_price_id')->nullable()->after('id');
            $table->unique('stripe_price_id');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('subscription_status');
            $table->unique('stripe_customer_id');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            $table->unique('stripe_subscription_id');
        });
    }
};
