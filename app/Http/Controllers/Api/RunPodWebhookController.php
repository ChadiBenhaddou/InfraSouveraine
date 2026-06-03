<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pod;
use App\Events\PodProvisioned;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RunPodWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $signature = $request->header('X-RunPod-Signature');
        $webhookSecret = config('runpod.webhook_secret');

        if ($webhookSecret) {
            $payload = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            $providedSignature = $signature;

            if (!$providedSignature || !hash_equals($expectedSignature, $providedSignature)) {
                Log::warning('RunPod webhook signature verification failed');
                return response('Invalid signature', 401);
            }
        }

        $validated = $request->validate([
            'pod_id' => 'required|string',
            'status' => 'required|string',
            'machine' => 'sometimes|array',
            'runtime' => 'sometimes|array',
        ]);

        $pod = Pod::where('runpod_pod_id', $validated['pod_id'])->first();

        if (!$pod) {
            Log::warning('RunPod webhook received for unknown pod', ['pod_id' => $validated['pod_id']]);
            return response('Pod not found', 404);
        }

        $desiredStatus = strtoupper($validated['status']);
        $machine = $validated['machine'] ?? [];
        $runtime = $validated['runtime'] ?? [];

        $pod->update([
            'status' => $desiredStatus,
            'public_ip' => $machine['publicIp'] ?? $runtime['publicIp'] ?? $pod->public_ip,
            'webui_url' => $runtime['proxyUrl'] ?? $validated['proxyUrl'] ?? $pod->webui_url,
            'runtime_metrics' => $validated,
            'last_active_at' => now(),
        ]);

        if ($desiredStatus === 'RUNNING') {
            $pod->update(['provisioned_at' => now()]);

            try {
                PodProvisioned::dispatch($pod);
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
