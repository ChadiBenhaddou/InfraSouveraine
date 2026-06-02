<?php

namespace App\Http\Livewire\Onboarding;

use App\Models\Tenant;
use App\Models\Plan;
use App\Services\CostCalculator;
use Livewire\Component;

class GpuSelectionStep extends Component
{
    public ?Tenant $tenant = null;
    public string $selectedGpu = '';
    public ?Plan $selectedPlan = null;
    public array $pricing = [];
    public array $availableGpus = [];

    protected $listeners = ['gpu-selected'];

    public function gpuSelected(string $gpuTier): void
    {
        $this->selectedGpu = $gpuTier;
        $this->loadPlans();
    }

    public function proceedToCheckout(): void
    {
        if (!$this->selectedPlan) return;

        $this->dispatch('checkout-plan', planId: $this->selectedPlan->id);
        $this->dispatch('go-to-step', step: 4);
    }

    public function render()
    {
        return view('livewire.onboarding.gpu-selection-step');
    }

    private function loadPlans(): void
    {
        $this->availableGpus = config('runpod.gpu_tiers');

        $this->selectedPlan = Plan::active()
            ->where('gpu_tier', $this->selectedGpu)
            ->first();

        if (!$this->selectedPlan) {
            $calculator = app(CostCalculator::class);
            $this->pricing = $calculator->calculateSubscriptionPrice($this->selectedGpu);
        }

        if ($this->tenant) {
            $this->tenant->update(['selected_gpu_tier' => $this->selectedGpu]);
        }
    }
}
