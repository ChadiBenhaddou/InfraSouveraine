<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'paypal_plan_id',
        'gpu_tier',
        'name',
        'description',
        'base_hourly_rate',
        'storage_cost_monthly',
        'benefit_margin_rate',
        'fixed_markup',
        'monthly_price',
        'is_active',
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:4',
        'storage_cost_monthly' => 'decimal:2',
        'benefit_margin_rate' => 'decimal:4',
        'fixed_markup' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function gpuConfig(): ?array
    {
        return config("runpod.gpu_tiers.{$this->gpu_tier}");
    }
}
