<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'runpod_pod_id' => 'pod_' . fake()->uuid(),
            'status' => 'RUNNING',
            'gpu_tier' => 'RTX_4090',
            'model_id' => 'llama-3-8b-instruct',
            'webui_url' => fake()->url(),
            'public_ip' => fake()->ipv4(),
            'port' => 3000,
            'admin_username' => 'admin_' . fake()->word(),
            'admin_password' => fake()->password(24),
            'cost_incurred' => fake()->randomFloat(2, 100, 500),
            'provisioned_at' => now()->subMinutes(fake()->numberBetween(5, 120)),
            'last_active_at' => now()->subMinutes(fake()->numberBetween(1, 30)),
        ];
    }

    public function creating(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'CREATING',
            'provisioned_at' => null,
            'webui_url' => null,
            'public_ip' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'FAILED',
        ]);
    }
}
