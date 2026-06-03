<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Pod;
use App\Enums\PodStatus;
use App\Events\PodProvisioned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SimulatePodProvisioning implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 30;

    public function __construct(
        public readonly Tenant $tenant,
    ) {}

    public function handle(): void
    {
        $adminUsername = encrypt('admin_' . Str::random(12));
        $adminPassword = encrypt(Str::password(24, symbols: true));

        $pod = Pod::create([
            'tenant_id' => $this->tenant->id,
            'runpod_pod_id' => 'test_pod_' . Str::random(16),
            'status' => PodStatus::RUNNING,
            'gpu_tier' => $this->tenant->selected_gpu_tier,
            'model_id' => $this->tenant->recommended_model_id,
            'webui_url' => 'https://test-pod-' . Str::random(8) . '-8080.proxy.runpod.ai',
            'public_ip' => '203.0.113.' . random_int(1, 254),
            'port' => 8080,
            'admin_username' => $adminUsername,
            'admin_password' => $adminPassword,
            'container_id' => 'test_container_' . Str::random(16),
            'runtime_metrics' => [
                'test_mode' => true,
                'simulated' => true,
                'gpu_usage_percent' => 42.5,
                'memory_usage_percent' => 38.2,
                'uptime_seconds' => 3600,
            ],
            'cost_incurred' => 0,
            'provisioned_at' => now(),
            'last_active_at' => now(),
        ]);

        Log::info('Test pod simulated', [
            'tenant_id' => $this->tenant->id,
            'pod_id' => $pod->id,
        ]);

        PodProvisioned::dispatch($pod);
        SendWelcomeEmail::dispatch($pod);
    }
}
