<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        $tiers = ['RTX_4090', 'RTX_A6000', 'A100_40GB', 'A100_80GB', 'H100'];

        return [
            'gpu_tier' => fake()->randomElement($tiers),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'base_hourly_rate' => fake()->randomFloat(4, 0.5, 8.0),
            'storage_cost_monthly' => 5.00,
            'benefit_margin_rate' => 0.35,
            'fixed_markup' => 9.99,
            'monthly_price' => fake()->randomFloat(2, 200, 3000),
            'is_active' => true,
        ];
    }
}
