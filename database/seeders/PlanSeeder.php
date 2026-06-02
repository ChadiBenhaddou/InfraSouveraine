<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Services\CostCalculator;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $calculator = app(CostCalculator::class);

        $gpus = [
            'RTX_4090' => 'NVIDIA RTX 4090',
            'RTX_A6000' => 'NVIDIA RTX A6000',
            'A100_40GB' => 'NVIDIA A100 40GB',
            'A100_80GB' => 'NVIDIA A100 80GB',
            'H100' => 'NVIDIA H100 80GB',
        ];

        foreach ($gpus as $tier => $name) {
            $pricing = $calculator->calculateSubscriptionPrice($tier);

            Plan::create([
                'gpu_tier' => $tier,
                'name' => $name,
                'description' => "Dedicated {$name} GPU with 24/7 availability.",
                'base_hourly_rate' => config("runpod.gpu_tiers.{$tier}.hourly_rate"),
                'storage_cost_monthly' => 5.00,
                'benefit_margin_rate' => 0.35,
                'fixed_markup' => 9.99,
                'monthly_price' => $pricing['monthly_subscription_price'],
                'is_active' => true,
            ]);
        }
    }
}
