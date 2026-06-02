<?php

namespace App\Http\Controllers;

use App\Jobs\ProvisionRunPodPod;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    public function welcome()
    {
        return view('onboarding.welcome');
    }

    public function wizard()
    {
        $user = Auth::user();
        $tenant = $user->tenants()->latest()->first();

        if ($tenant && $tenant->subscription_status === 'active') {
            return redirect()->route('dashboard');
        }

        return view('onboarding.wizard');
    }

    public function success(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenants()->latest()->first();

        if (!$tenant || !$tenant->paypal_subscription_id) {
            return redirect()->route('onboarding.wizard');
        }

        if ($tenant->subscription_status === 'active' && !$tenant->pods()->exists()) {
            try {
                ProvisionRunPodPod::dispatch($tenant);
                Log::info('Provisioning job dispatched for tenant', ['tenant_id' => $tenant->id]);
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch provisioning job', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('onboarding.success', [
            'tenant' => $tenant,
        ]);
    }

    public function cancel()
    {
        return redirect()->route('onboarding.wizard')
            ->with('error', 'Payment was cancelled. You can try again anytime.');
    }

    public function testHoursSuccess()
    {
        return redirect()->route('test-hours')
            ->with('message', 'Paiement réussi ! Vos heures de test ont été créditées.');
    }

    public function testHoursCancel()
    {
        return redirect()->route('test-hours')
            ->with('error', 'Paiement annulé. Vous pouvez réessayer quand vous voulez.');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $tenant = $user->tenants()->latest()->first();
        $pod = $tenant?->pods()->latest()->first();

        return view('onboarding.dashboard', [
            'tenant' => $tenant,
            'pod' => $pod,
        ]);
    }
}
