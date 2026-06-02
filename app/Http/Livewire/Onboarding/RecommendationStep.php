<?php

namespace App\Http\Livewire\Onboarding;

use App\Models\Tenant;
use Livewire\Component;

class RecommendationStep extends Component
{
    public ?Tenant $tenant = null;
    public array $recommendation = [];
    public array $availableGpus = [];
    public array $compatibleGpus = [];

    protected $listeners = ['recommendation-ready'];

    public function recommendationReady(array $recommendation, int $tenantId): void
    {
        $this->recommendation = $recommendation;
        $this->tenant = Tenant::find($tenantId);
        $this->loadAvailableGpus();
    }

    public function selectGpu(string $gpuTier): void
    {
        if (!$this->tenant) return;

        $this->tenant->update(['selected_gpu_tier' => $gpuTier]);
        session(['selected_gpu_tier' => $gpuTier]);

        $this->dispatch('gpu-selected', gpuTier: $gpuTier)->to(GpuSelectionStep::class);
        $this->dispatch('go-to-step', step: 3);
    }

    public function render()
    {
        return view('livewire.onboarding.recommendation-step');
    }

    private function loadAvailableGpus(): void
    {
        $this->availableGpus = config('runpod.gpu_tiers');
        $requiredVram = $this->recommendation['estimated_vram_required_gb'] ?? 8;

        $this->compatibleGpus = collect($this->availableGpus)
            ->filter(fn ($gpu) => $gpu['vram_gb'] >= $requiredVram)
            ->toArray();
    }
}
