<?php

namespace App\Http\Livewire\Onboarding;

use App\Models\Tenant;
use App\Services\AiRecommender;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UseCaseStep extends Component
{
    public ?string $companyName = '';
    public string $useCase = '';
    public bool $loading = false;
    public ?string $error = null;

    public function mount(): void
    {
        $tenant = $this->getCurrentTenant();
        if ($tenant) {
            $this->companyName = $tenant->company_name ?? '';
            $this->useCase = $tenant->use_case ?? '';
        }
    }

    public function submit(): void
    {
        $this->validate([
            'useCase' => 'required|string|min:10|max:1000',
            'companyName' => 'nullable|string|max:255',
        ]);

        $this->loading = true;
        $this->error = null;

        $tenant = $this->getOrCreateTenant();
        $tenant->update([
            'company_name' => $this->companyName,
            'use_case' => $this->useCase,
        ]);

        $recommender = app(AiRecommender::class);
        $recommendation = $recommender->recommend($this->useCase);

        $tenant->update([
            'recommended_model_id' => $recommendation['model_id'],
        ]);

        $this->loading = false;

        $this->dispatch('recommendation-ready', recommendation: $recommendation, tenantId: $tenant->id)->to(RecommendationStep::class);
        $this->dispatch('go-to-step', step: 2);
    }

    public function render()
    {
        return view('livewire.onboarding.use-case-step');
    }

    private function getCurrentTenant(): ?Tenant
    {
        return Auth::user()?->tenants()->latest()->first();
    }

    private function getOrCreateTenant(): Tenant
    {
        return Auth::user()->tenants()->firstOrCreate(
            ['id' => session('current_tenant_id')],
            ['subscription_status' => 'pending'],
        );
    }
}
