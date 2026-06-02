<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'use_case' => fake()->sentence(),
            'subscription_status' => 'pending',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'active',
            'selected_gpu_tier' => 'RTX_4090',
            'recommended_model_id' => 'llama-3-8b-instruct',
            'monthly_subscription_price' => 799.99,
            'base_monthly_cost' => 586.70,
            'benefit_margin_rate' => 0.35,
            'fixed_markup' => 9.99,
            'stripe_customer_id' => 'cus_' . fake()->uuid(),
            'stripe_subscription_id' => 'sub_' . fake()->uuid(),
        ]);
    }
}
