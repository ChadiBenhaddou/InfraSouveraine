<?php

namespace App\Models;

use App\Enums\PodStatus;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'use_case',
        'recommended_model_id',
        'selected_gpu_tier',
        'test_hours_balance',
        'subscription_status',
        'paypal_subscription_id',
        'monthly_subscription_price',
        'base_monthly_cost',
        'benefit_margin_rate',
        'fixed_markup',
        'weekly_schedule',
        'onboarding_step',
        'actual_raw_cost_incurred',
        'profit_generated',
    ];

    protected $casts = [
        'monthly_subscription_price' => 'decimal:2',
        'base_monthly_cost' => 'decimal:2',
        'benefit_margin_rate' => 'decimal:4',
        'fixed_markup' => 'decimal:2',
        'weekly_schedule' => 'json',
        'actual_raw_cost_incurred' => 'decimal:2',
        'profit_generated' => 'decimal:2',
        'test_hours_balance' => 'decimal:2',
        'subscription_status' => SubscriptionStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pods(): HasMany
    {
        return $this->hasMany(Pod::class);
    }

    public function activePod(): ?Pod
    {
        return $this->pods()->whereIn('status', [
            PodStatus::CREATING->value,
            PodStatus::INITIALIZING->value,
            PodStatus::RUNNING->value,
        ])->latest()->first();
    }

    public function testHourPurchases(): HasMany
    {
        return $this->hasMany(TestHourPurchase::class);
    }

    public function isSubscriptionActive(): bool
    {
        return $this->subscription_status === SubscriptionStatus::ACTIVE;
    }

    public function hasTestHours(): bool
    {
        return $this->test_hours_balance > 0;
    }

    public function canDeploy(): bool
    {
        return $this->isSubscriptionActive() || $this->hasTestHours();
    }

    public function deductTestHours(float $hours): void
    {
        $this->decrement('test_hours_balance', $hours);
    }
}
