<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_hour_purchases', function (Blueprint $table) {
            $table->string('paypal_order_id')->nullable()->after('stripe_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_hour_purchases', function (Blueprint $table) {
            $table->dropColumn('paypal_order_id');
        });
    }
};
