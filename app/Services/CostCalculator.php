<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Config;

class CostCalculator
{
    private const HOURS_PER_MONTH = 730;

    public function calculateWeeklyPrice(
        string $gpuTier,
        float $hoursPerWeek,
        ?int $storageGb = null,
        ?float $customMarginRate = null,
        ?float $customFixedMarkup = null,
    ): array {
        $storageGb = $storageGb ?? (int) config('settings.default_container_disk_gb', 50);
        $tiers = config('runpod.gpu_tiers');
        $tier = $tiers[$gpuTier] ?? null;

        if (!$tier) {
            throw new \InvalidArgumentException("Unknown GPU tier: {$gpuTier}");
        }

        $hourlyRate = $tier['hourly_rate'];
        $storageCostPerGb = config('settings.storage_cost_per_gb_monthly', 0.10);

        $monthlyHours = $hoursPerWeek * 4.33;
        $computeCost = $hourlyRate * $monthlyHours;
        $storageCost = $storageGb * $storageCostPerGb;
        $baseCost = $computeCost + $storageCost;

        $marginRate = $customMarginRate ?? config('settings.default_benefit_margin', 0.35);
        $fixedMarkup = $customFixedMarkup ?? config('settings.fixed_platform_markup', 9.99);

        $finalPrice = ($baseCost * (1 + $marginRate)) + $fixedMarkup;

        return [
            'gpu_tier' => $gpuTier,
            'base_monthly_cost' => round($baseCost, 2),
            'benefit_margin_rate' => $marginRate,
            'benefit_margin_amount' => round($baseCost * $marginRate, 2),
            'fixed_platform_markup' => $fixedMarkup,
            'monthly_subscription_price' => round($finalPrice, 2),
            'storage_gb' => $storageGb,
            'hours_per_week' => $hoursPerWeek,
            'monthly_hours' => round($monthlyHours, 1),
            'compute_monthly_cost' => round($computeCost, 2),
            'storage_monthly_cost' => round($storageCost, 2),
        ];
    }

    public function calculateMonthlyBaseCost(string $gpuTier, ?int $storageGb = null): float
    {
        $storageGb = $storageGb ?? (int) config('settings.default_container_disk_gb', 50);
        $tiers = config("runpod.gpu_tiers");
        $tier = $tiers[$gpuTier] ?? null;

        if (!$tier) {
            throw new \InvalidArgumentException("Unknown GPU tier: {$gpuTier}");
        }

        $hourlyRate = $tier['hourly_rate'];
        $storageCostPerGb = Config::get('settings.storage_cost_per_gb_monthly', 0.10);

        $computeCost = $hourlyRate * self::HOURS_PER_MONTH;
        $storageCost = $storageGb * $storageCostPerGb;

        return round($computeCost + $storageCost, 2);
    }

    public function calculateSubscriptionPrice(
        string $gpuTier,
        ?float $customMarginRate = null,
        ?float $customFixedMarkup = null,
        ?int $storageGb = null,
    ): array {
        $storageGb = $storageGb ?? (int) config('settings.default_container_disk_gb', 50);
        $baseCost = $this->calculateMonthlyBaseCost($gpuTier, $storageGb);

        $marginRate = $customMarginRate ?? Config::get('settings.default_benefit_margin', 0.35);
        $fixedMarkup = $customFixedMarkup ?? Config::get('settings.fixed_platform_markup', 9.99);

        // Formula: Final Subscription Price = (Estimated Base Cost * (1 + Benefit Margin)) + Fixed Platform Markup
        $finalPrice = ($baseCost * (1 + $marginRate)) + $fixedMarkup;

        return [
            'gpu_tier' => $gpuTier,
            'base_monthly_cost' => round($baseCost, 2),
            'benefit_margin_rate' => $marginRate,
            'benefit_margin_amount' => round($baseCost * $marginRate, 2),
            'fixed_platform_markup' => $fixedMarkup,
            'monthly_subscription_price' => round($finalPrice, 2),
            'storage_gb' => $storageGb,
            'hours_per_month' => self::HOURS_PER_MONTH,
        ];
    }

    public function calculateProfit(float $actualRawCost, float $subscriptionPrice): float
    {
        return round($subscriptionPrice - $actualRawCost, 2);
    }

    public function syncPlanFromTier(Plan $plan): Plan
    {
        $pricing = $this->calculateSubscriptionPrice(
            $plan->gpu_tier,
            $plan->benefit_margin_rate,
            $plan->fixed_markup,
        );

        $plan->update([
            'base_hourly_rate' => config("runpod.gpu_tiers.{$plan->gpu_tier}.hourly_rate"),
            'monthly_price' => $pricing['monthly_subscription_price'],
        ]);

        return $plan->fresh();
    }
}
