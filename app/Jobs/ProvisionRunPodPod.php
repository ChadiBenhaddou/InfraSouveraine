<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Pod;
use App\Services\RunPodApi;
use App\Exceptions\RunPodApiException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProvisionRunPodPod implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 300;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly Tenant $tenant,
    ) {}

    public function handle(RunPodApi $api): void
    {
        $gpuTier = $this->tenant->selected_gpu_tier;
        $modelId = $this->tenant->recommended_model_id;
        $tierConfig = config("runpod.gpu_tiers.{$gpuTier}");
        $modelConfig = config("runpod.recommended_models.{$modelId}");
        $imageName = config('runpod.default_image_name');
        $templateId = config('runpod.default_template_id');
        $containerDiskGb = config('runpod.default_container_disk_size_gb', 30);
        $volumeGb = config('runpod.default_volume_size_gb', 50);
        $volumePath = config('runpod.default_volume_mount_path', '/root/.ollama');
        $port = config('runpod.default_webui_port', 8080);

        if (!$tierConfig || !$modelConfig) {
            Log::error('Invalid GPU tier or model config for provisioning', [
                'tenant_id' => $this->tenant->id,
                'gpu_tier' => $gpuTier,
                'model_id' => $modelId,
            ]);
            $this->fail(new \RuntimeException("Invalid GPU tier '{$gpuTier}' or model '{$modelId}'"));
            return;
        }

        $adminUsername = 'admin_' . Str::random(12);
        $adminPassword = Str::password(24, symbols: true);
        $encryptedPassword = encrypt($adminPassword);
        $encryptedUsername = encrypt($adminUsername);

        $ollamaModel = $modelConfig['ollama_tag'] ?? $modelId;
        $huggingFaceId = $modelConfig['huggingface_id'] ?? '';
        $webuiSecretKey = Str::random(32);

        $env = [
            'OLLAMA_MODELS' => $ollamaModel,
            'WEBUI_SECRET_KEY' => $webuiSecretKey,
            'WEBUI_ADMIN_USERNAME' => $adminUsername,
            'WEBUI_ADMIN_PASSWORD' => $adminPassword,
            'WEBUI_AUTH' => 'true',
            'HF_MODEL_ID' => $huggingFaceId,
        ];

        $payload = [
            'name' => "infra-{$this->tenant->id}-" . Str::slug($modelId),
            'gpu_type_ids' => [$tierConfig['runpod_id']],
            'gpu_count' => 1,
            'container_disk_size_gb' => $containerDiskGb,
            'volume_in_gb' => $volumeGb,
            'volume_mount_path' => $volumePath,
            'ports' => ["{$port}/http"],
            'env' => $env,
            'support_public_ip' => true,
        ];

        if ($templateId) {
            $payload['template_id'] = $templateId;
            unset($payload['volume_mount_path']);
        } else {
            $payload['image_name'] = $imageName;
        }

        try {
            $response = $api->createPod($payload);

            $runpodPodId = $response['id'] ?? $response['pod']['id'] ?? null;
            if (!$runpodPodId) {
                throw new RunPodApiException('RunPod did not return a pod ID', responseData: $response);
            }

            $pod = Pod::create([
                'tenant_id' => $this->tenant->id,
                'runpod_pod_id' => $runpodPodId,
                'status' => 'CREATING',
                'gpu_tier' => $gpuTier,
                'model_id' => $modelId,
                'admin_username' => $encryptedUsername,
                'admin_password' => $encryptedPassword,
                'port' => $port,
                'runtime_metrics' => ['provisioning_payload' => $payload],
            ]);

            Log::info('RunPod pod provisioned with ' . ($templateId ? 'template' : 'image'), [
                'tenant_id' => $this->tenant->id,
                'pod_id' => $pod->id,
                'runpod_pod_id' => $runpodPodId,
            ]);

            MonitorPodStatus::dispatch($pod)
                ->delay(now()->addSeconds(config('runpod.poll_interval_seconds', 15)));
        } catch (RunPodApiException $e) {
            Log::error('Failed to provision RunPod pod', [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
