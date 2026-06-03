<?php

namespace App\Services;

use App\Exceptions\RunPodApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunPodApi
{
    private string $apiKey;
    private string $baseUrl;
    private bool $useV1Api;

    public function __construct()
    {
        $this->apiKey = config('runpod.api_key');
        $this->baseUrl = config('runpod.base_url', 'https://rest.runpod.io/v1');
        $useV1Config = config('runpod.use_v1_api_payload');
        $this->useV1Api = $useV1Config !== null ? (bool) $useV1Config : str_contains($this->baseUrl, 'rest.runpod.io');
    }

    public function createPod(array $payload): array
    {
        $normalized = $payload;

        if ($this->useV1Api) {
            $v1Payload = [];
            foreach ($payload as $key => $value) {
                $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                $v1Payload[$camelKey] = $value;
            }

            if (isset($v1Payload['templateId'])) {
                $v1Payload['templateId'] = $payload['template_id'];
                unset($v1Payload['templateId']);
            }

            $normalized = $v1Payload;
        }

        return $this->post('pods', $normalized);
    }

    public function getPod(string $podId): array
    {
        return $this->get("pods/{$podId}");
    }

    public function listPods(?int $userId = null): array
    {
        $endpoint = 'pods';
        if ($userId) {
            $endpoint .= "?userId={$userId}";
        }
        return $this->get($endpoint);
    }

    public function stopPod(string $podId): array
    {
        return $this->post("pods/{$podId}/stop");
    }

    public function startPod(string $podId): array
    {
        return $this->post("pods/{$podId}/start");
    }

    public function terminatePod(string $podId): array
    {
        return $this->delete("pods/{$podId}");
    }

    public function getPodMetrics(string $podId): array
    {
        return $this->get("pods/{$podId}/metrics");
    }

    public function getGpuAvailability(): array
    {
        return $this->get('gpus/availability');
    }

    private function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, $data);
    }

    private function get(string $endpoint): array
    {
        return $this->request('get', $endpoint);
    }

    private function delete(string $endpoint): array
    {
        return $this->request('delete', $endpoint);
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->$method($url, $method === 'post' ? $data : []);

            if ($response->failed()) {
                throw new RunPodApiException(
                    message: "RunPod API {$method} to {$endpoint} failed: {$response->body()}",
                    code: $response->status(),
                    responseData: $response->json(),
                );
            }

            return $response->json();
        } catch (RunPodApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('RunPod API connection error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            throw new RunPodApiException(
                message: "RunPod API connection error: {$e->getMessage()}",
                code: 0,
                previous: $e,
            );
        }
    }
}
