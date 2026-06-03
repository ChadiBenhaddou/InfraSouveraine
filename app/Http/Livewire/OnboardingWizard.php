<?php

namespace App\Http\Livewire;

use App\Jobs\SimulatePodProvisioning;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\AiRecommender;
use App\Services\CostCalculator;
use App\Services\SubscriptionManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OnboardingWizard extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 5;

    // Step 1: Use Case
    public ?string $companyName = '';
    public string $useCase = '';
    public bool $recommending = false;

    // Step 2: Recommendation
    public array $recommendation = [];
    public array $compatibleGpus = [];

    // Step 3: GPU Selection & Schedule
    public ?string $selectedGpuTier = null;
    public array $pricing = [];
    public string $weeklySchedule = '';
    public float $hoursPerWeek = 0;
    public float $monthlyHours = 0;

    // Step 4: Review & Checkout
    public array $checkoutData = [];
    public string $checkoutUrl = '';

    // Step 5: Confirmation
    public bool $provisioningStarted = false;

    public ?Tenant $tenant = null;

    public function mount(): void
    {
        $this->tenant = Auth::user()?->tenants()->latest()->first();

        if ($this->tenant) {
            $this->companyName = $this->tenant->company_name ?? '';
            $this->useCase = $this->tenant->use_case ?? '';
            $this->selectedGpuTier = $this->tenant->selected_gpu_tier;
            if ($this->tenant->weekly_schedule) {
                $this->weeklySchedule = json_encode($this->tenant->weekly_schedule);
            }

            // Restore step from tenant or session
            $step = $this->tenant->onboarding_step ?? session('onboarding_step');
            if ($step && $step >= 1 && $step <= $this->totalSteps) {
                $this->currentStep = $step;
            } elseif ($this->tenant->recommended_model_id && !$step) {
                $this->currentStep = 3;
            }

            // Restore transient state from session
            if (session()->has('onboarding_recommendation')) {
                $this->recommendation = session('onboarding_recommendation');
                $this->compatibleGpus = session('onboarding_compatible_gpus', []);
            } elseif ($this->tenant->recommended_model_id) {
                $modelId = $this->tenant->recommended_model_id;
                $modelConfig = config("runpod.recommended_models.{$modelId}");
                $this->recommendation = [
                    'model_id' => $modelId,
                    'estimated_vram_required_gb' => $modelConfig['min_vram_gb'] ?? 8,
                ];
                $this->loadCompatibleGpus();
            }

            if (session()->has('onboarding_checkout_data')) {
                $this->checkoutData = session('onboarding_checkout_data');
            }
            if (session()->has('onboarding_checkout_url')) {
                $this->checkoutUrl = session('onboarding_checkout_url');
            }

            // Recalculate pricing if at step 3+ with data
            if ($this->currentStep >= 3 && $this->selectedGpuTier && $this->weeklySchedule) {
                $this->recalculateForSchedule();
            }
        }
    }

    public function submitUseCase(): void
    {
        $this->validate([
            'useCase' => 'required|string|min:10|max:1000',
            'companyName' => 'nullable|string|max:255',
        ]);

        $this->recommending = true;

        $this->tenant = Auth::user()->tenants()->firstOrCreate(
            ['id' => $this->tenant?->id],
            ['subscription_status' => 'pending'],
        );

        $this->tenant->update([
            'company_name' => $this->companyName,
            'use_case' => $this->useCase,
        ]);

        $recommender = app(AiRecommender::class);
        $this->recommendation = $recommender->recommend($this->useCase);
        $this->recommending = false;

        if (isset($this->recommendation['model_id'])) {
            $this->tenant->update(['recommended_model_id' => $this->recommendation['model_id']]);
        }

        session([
            'onboarding_recommendation' => $this->recommendation,
        ]);

        $this->loadCompatibleGpus();
        $this->goToStep(2);
    }

    public function selectGpu(string $gpuTier): void
    {
        $this->selectedGpuTier = $gpuTier;
        $this->tenant->update(['selected_gpu_tier' => $gpuTier]);

        if (!$this->weeklySchedule) {
            $default = config('settings.default_hours_per_week', 40);
            $dayHours = $default <= 24 ? array_slice(range(9, 9 + ($default / 5) - 1), 0, 8) : range(9, 16);
            $this->weeklySchedule = json_encode([
                'mon' => $dayHours,
                'tue' => $dayHours,
                'wed' => $dayHours,
                'thu' => $dayHours,
                'fri' => $dayHours,
                'sat' => [],
                'sun' => [],
            ]);
        }

        $this->recalculateForSchedule();
        $this->goToStep(3);
    }

    public function recalculateForSchedule(): void
    {
        $schedule = json_decode($this->weeklySchedule, true);
        $hoursPerWeek = 0;
        if (is_array($schedule)) {
            foreach ($schedule as $hours) {
                if (is_array($hours)) {
                    $hoursPerWeek += count($hours);
                }
            }
        }

        $this->hoursPerWeek = $hoursPerWeek;
        $this->monthlyHours = round($hoursPerWeek * 4.33, 1);

        if ($this->selectedGpuTier && $hoursPerWeek > 0) {
            $calculator = app(CostCalculator::class);
            $this->pricing = $calculator->calculateWeeklyPrice(
                $this->selectedGpuTier,
                $hoursPerWeek,
            );
        }
    }

    public function updatedWeeklySchedule(): void
    {
        $this->recalculateForSchedule();
        if ($this->tenant) {
            $this->tenant->update([
                'weekly_schedule' => json_decode($this->weeklySchedule, true),
            ]);
        }
    }

    public function reviewAndCheckout(): void
    {
        $this->validate(['selectedGpuTier' => 'required']);

        $this->tenant->update([
            'weekly_schedule' => json_decode($this->weeklySchedule, true),
        ]);

        $plan = Plan::active()->where('gpu_tier', $this->selectedGpuTier)->first();
        if (!$plan) {
            $plan = Plan::create([
                'gpu_tier' => $this->selectedGpuTier,
                'name' => config("runpod.gpu_tiers.{$this->selectedGpuTier}.display"),
                'base_hourly_rate' => config("runpod.gpu_tiers.{$this->selectedGpuTier}.hourly_rate"),
                'monthly_price' => $this->pricing['monthly_subscription_price'],
            ]);
        }

        $this->checkoutData = [
            'plan_id' => $plan->id,
            'gpu_tier' => $this->selectedGpuTier,
            'monthly_price' => $this->pricing['monthly_subscription_price'],
            'base_cost' => $this->pricing['base_monthly_cost'],
            'hours_per_week' => $this->hoursPerWeek,
        ];

        session(['onboarding_checkout_data' => $this->checkoutData]);

        $this->goToStep(4);
    }

    public function startCheckout(): void
    {
        $manager = app(SubscriptionManager::class);
        $plan = Plan::findOrFail($this->checkoutData['plan_id']);

        $result = $manager->createSubscriptionCheckout(
            $this->tenant,
            $plan,
            route('onboarding.success'),
            route('onboarding.cancel'),
        );

        $this->checkoutUrl = $result['checkout_url'];
        session(['onboarding_checkout_url' => $this->checkoutUrl]);

        $this->redirect($result['checkout_url']);
    }

    public function deployWithTestHours(): void
    {
        if (!$this->tenant || $this->tenant->test_hours_balance < 1) {
            session()->flash('error', 'Solde d\'heures de test insuffisant.');
            return;
        }

        $plan = Plan::findOrFail($this->checkoutData['plan_id']);

        $this->tenant->deductTestHours(1);

        $this->tenant->update([
            'subscription_status' => 'active',
            'monthly_subscription_price' => $this->checkoutData['monthly_price'],
        ]);

        dispatch(new \App\Jobs\ProvisionRunPodPod($this->tenant));

        $this->goToStep(5);
    }

    public function startTestDeployment(): void
    {
        $plan = Plan::findOrFail($this->checkoutData['plan_id']);

        $this->tenant->update([
            'subscription_status' => 'active',
            'paypal_subscription_id' => 'test_sub_' . uniqid(),
            'monthly_subscription_price' => $this->checkoutData['monthly_price'],
        ]);

        \App\Jobs\SimulatePodProvisioning::dispatch($this->tenant);

        $this->goToStep(5);
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
            if ($this->tenant) {
                $this->tenant->update(['onboarding_step' => $step]);
            }
            session(['onboarding_step' => $step]);
        }
    }

    public function render()
    {
        $availableGpus = config('runpod.gpu_tiers');
        $modelConfig = isset($this->recommendation['model_id'])
            ? config("runpod.recommended_models.{$this->recommendation['model_id']}")
            : null;

        return view('livewire.onboarding-wizard', [
            'availableGpus' => $availableGpus,
            'modelConfig' => $modelConfig,
            'hasTestHours' => $this->tenant?->hasTestHours() ?? false,
            'testHoursBalance' => $this->tenant?->test_hours_balance ?? 0,
            'hoursPerWeek' => $this->hoursPerWeek,
        ]);
    }

    private function loadCompatibleGpus(): void
    {
        $requiredVram = $this->recommendation['estimated_vram_required_gb'] ?? 8;
        $allGpus = config('runpod.gpu_tiers');

        $this->compatibleGpus = collect($allGpus)
            ->filter(fn ($gpu) => $gpu['vram_gb'] >= $requiredVram)
            ->toArray();

        session(['onboarding_compatible_gpus' => $this->compatibleGpus]);
    }
}
