<?php

namespace App\Providers;

use App\Http\Livewire\OnboardingWizard;
use App\Services\RunPodApi;
use App\Services\CostCalculator;
use App\Services\AiRecommender;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RunPodApi::class, function ($app) {
            return new RunPodApi();
        });

        $this->app->singleton(CostCalculator::class, function ($app) {
            return new CostCalculator();
        });

        $this->app->singleton(AiRecommender::class, function ($app) {
            return new AiRecommender();
        });
    }

    public function boot(): void
    {
        Livewire::component('onboarding-wizard', OnboardingWizard::class);
    }
}
