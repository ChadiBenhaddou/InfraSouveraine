<?php

namespace App\Jobs;

use App\Models\Pod;
use App\Services\RunPodApi;
use App\Exceptions\RunPodApiException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MonitorPodStatus implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 120;
    public int $tries = 0; // unlimited — controlled by max_attempts check

    public function __construct(
        public readonly Pod $pod,
        public int $attempt = 1,
    ) {}

    public function handle(RunPodApi $api): void
    {
        $maxAttempts = config('runpod.poll_max_attempts', 60);
        $interval = config('runpod.poll_interval_seconds', 15);

        if ($this->attempt > $maxAttempts) {
            $this->pod->update(['status' => 'FAILED']);
            Log::error('Pod provisioning timed out', [
                'pod_id' => $this->pod->id,
                'runpod_pod_id' => $this->pod->runpod_pod_id,
            ]);
            return;
        }

        try {
            $response = $api->getPod($this->pod->runpod_pod_id);

            $desiredStatus = $response['pod']['status'] ?? $response['status'] ?? 'UNKNOWN';
            $machine = $response['pod']['machine'] ?? $response['machine'] ?? [];
            $runtime = $response['pod']['runtime'] ?? $response['runtime'] ?? [];

            $publicIp = $machine['publicIp'] ?? $runtime['publicIp'] ?? $response['publicIp'] ?? null;
            $portsRaw = $runtime['ports'] ?? $response['ports'] ?? null;
            $webuiUrl = $response['pod']['proxyUrl'] ?? $response['proxyUrl'] ?? $runtime['proxyUrl'] ?? null;
            $containerId = $response['pod']['containerId'] ?? $response['containerId'] ?? null;

            $port = null;
            if (is_array($portsRaw)) {
                $firstPort = $portsRaw[0] ?? [];
                $port = $firstPort['privatePort'] ?? $firstPort['publicPort'] ?? null;
            } elseif (is_string($portsRaw)) {
                $port = $portsRaw;
            } elseif (is_numeric($portsRaw)) {
                $port = $portsRaw;
            } else {
                $port = $this->pod->port;
            }

            if (!$webuiUrl && $publicIp && $port) {
                $webuiUrl = "http://{$publicIp}:{$port}";
            }

            if (!$webuiUrl && $this->pod->runpod_pod_id && $port) {
                $webuiUrl = "https://{$this->pod->runpod_pod_id}-{$port}.proxy.runpod.ai";
            }

            $this->pod->update([
                'status' => $desiredStatus,
                'internal_ip' => $machine['privateIp'] ?? $response['privateIp'] ?? $this->pod->internal_ip,
                'public_ip' => $publicIp,
                'port' => $port,
                'webui_url' => $webuiUrl,
                'container_id' => $containerId,
                'runtime_metrics' => $response,
                'last_active_at' => now(),
            ]);

            if (strtoupper($desiredStatus) === 'RUNNING') {
                $this->pod->update([
                    'provisioned_at' => now(),
                ]);

                Log::info('Pod is RUNNING', [
                    'pod_id' => $this->pod->id,
                    'runpod_pod_id' => $this->pod->runpod_pod_id,
                    'webui_url' => $webuiUrl,
                ]);

                SendWelcomeEmail::dispatch($this->pod);

                return;
            }

            if (in_array(strtoupper($desiredStatus), ['TERMINATED', 'FAILED'])) {
                Log::warning('Pod entered terminal state', [
                    'pod_id' => $this->pod->id,
                    'status' => $desiredStatus,
                ]);
                return;
            }

            static::dispatch($this->pod, $this->attempt + 1)
                ->delay(now()->addSeconds($interval));
        } catch (RunPodApiException $e) {
            Log::warning('RunPod API error during pod monitoring, retrying', [
                'pod_id' => $this->pod->id,
                'attempt' => $this->attempt,
                'error' => $e->getMessage(),
            ]);

            static::dispatch($this->pod, $this->attempt + 1)
                ->delay(now()->addSeconds($interval));
        }
    }
}
