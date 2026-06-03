<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => false,
            'cache' => false,
            'queue' => false,
        ];

        try {
            DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (\Throwable) {
        }

        try {
            Cache::store(config('cache.default'))->set('health_check', true, 10);
            $checks['cache'] = Cache::store(config('cache.default'))->get('health_check') === true;
        } catch (\Throwable) {
        }

        try {
            $queueConnection = config('queue.default');
            $checks['queue'] = in_array($queueConnection, ['database', 'redis', 'sqs', 'beanstalkd']);
        } catch (\Throwable) {
        }

        $healthy = count(array_filter($checks)) === count($checks);

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }
}
