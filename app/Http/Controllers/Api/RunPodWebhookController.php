<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pod;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RunPodWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->validate([
            'pod_id' => 'required|string',
            'status' => 'required|string',
            'machine' => 'sometimes|array',
            'runtime' => 'sometimes|array',
        ]);

        $pod = Pod::where('runpod_pod_id', $payload['pod_id'])->first();

        if (!$pod) {
            Log::warning('RunPod webhook received for unknown pod', ['pod_id' => $payload['pod_id']]);
            return response('Pod not found', 404);
        }

        $desiredStatus = strtoupper($payload['status']);
        $machine = $payload['machine'] ?? [];
        $runtime = $payload['runtime'] ?? [];

        $pod->update([
            'status' => $desiredStatus,
            'public_ip' => $machine['publicIp'] ?? $runtime['publicIp'] ?? $pod->public_ip,
            'webui_url' => $runtime['proxyUrl'] ?? $payload['proxyUrl'] ?? $pod->webui_url,
            'runtime_metrics' => $payload,
            'last_active_at' => now(),
        ]);

        if ($desiredStatus === 'RUNNING') {
            $pod->update(['provisioned_at' => now()]);

            try {
                SendWelcomeEmail::dispatch($pod);
                Log::info('Welcome email dispatched via RunPod webhook', ['pod_id' => $pod->id]);
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch welcome email', [
                    'pod_id' => $pod->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response('OK', 200);
    }
}
