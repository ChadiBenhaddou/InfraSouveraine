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

    // Step 3: GPU Selection
    public ?string $selectedGpuTier = null;
    public array $pricing = [];

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
            if ($this->tenant->recommended_model_id) {
                $this->currentStep = 3;
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

        $this->loadCompatibleGpus();
        $this->goToStep(2);
    }

    public function selectGpu(string $gpuTier): void
    {
        $this->selectedGpuTier = $gpuTier;
        $this->tenant->update(['selected_gpu_tier' => $gpuTier]);

        $calculator = app(CostCalculator::class);
        $modelConfig = config("runpod.recommended_models.{$this->recommendation['model_id']}");
        $this->pricing = $calculator->calculateSubscriptionPrice(
            $gpuTier,
            storageGb: 50,
        );

        $this->goToStep(3);
    }

    public function reviewAndCheckout(): void
    {
        $this->validate(['selectedGpuTier' => 'required']);

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
        ];

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
        ]);
    }

    private function loadCompatibleGpus(): void
    {
        $requiredVram = $this->recommendation['estimated_vram_required_gb'] ?? 8;
        $allGpus = config('runpod.gpu_tiers');

        $this->compatibleGpus = collect($allGpus)
            ->filter(fn ($gpu) => $gpu['vram_gb'] >= $requiredVram)
            ->toArray();
    }
}
