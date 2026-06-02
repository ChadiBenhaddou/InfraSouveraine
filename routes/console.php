<?php

use App\Models\Pod;
use App\Models\Tenant;
use App\Services\CostCalculator;
use App\Services\RunPodApi;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $activePods = Pod::whereIn('status', ['RUNNING', 'CREATING', 'INITIALIZING'])->get();

    foreach ($activePods as $pod) {
        try {
            $api = app(RunPodApi::class);
            $response = $api->getPod($pod->runpod_pod_id);
            $pod->update([
                'runtime_metrics' => $response,
                'last_active_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Schedule: pod status check failed', [
                'pod_id' => $pod->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
})->name('sync-pod-status')->everyFiveMinutes();

Schedule::call(function () {
    $calculator = app(CostCalculator::class);
    $activeTenants = Tenant::where('subscription_status', 'active')->get();

    foreach ($activeTenants as $tenant) {
        $totalCost = $tenant->pods()->sum('cost_incurred');
        $profit = $calculator->calculateProfit($totalCost, $tenant->monthly_subscription_price);

        $tenant->updateQuietly([
            'actual_raw_cost_incurred' => $totalCost,
            'profit_generated' => $profit,
        ]);
    }
})->name('sync-profit-metrics')->hourly();
