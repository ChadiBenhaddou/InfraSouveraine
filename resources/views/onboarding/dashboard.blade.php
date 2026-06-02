<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-950">Tableau de bord</h1>
                <p class="text-gray-500 mt-1">Gérez votre infrastructure IA souveraine.</p>
            </div>
            @if (!$tenant)
                <a href="{{ route('onboarding.wizard') }}" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/20">
                    Déployer un serveur
                </a>
            @endif
        </div>

        @if (config('settings.testing_mode'))
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <div class="flex items-center gap-3 text-amber-800 text-sm">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div><strong>Mode Test</strong> — Données simulées à des fins de démonstration.</div>
                </div>
            </div>
        @endif

        @if (!$tenant)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h2 class="text-xl font-bold text-blue-950 mb-2">Aucun serveur déployé</h2>
                <p class="text-gray-500 mb-6">Vous n'avez pas encore configuré votre serveur IA.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('onboarding.wizard') }}" class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-blue-900/20">
                        Déployer mon premier serveur
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('test-hours') }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-blue-950 px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-amber-500/25">
                        ⏱️ Acheter des heures de test
                    </a>
                </div>
            </div>
        @elseif ($tenant && !$tenant->canDeploy())
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8 text-center">
                <p class="font-bold text-amber-800 mb-2">Aucun abonnement actif</p>
                <p class="text-amber-600 text-sm mb-4">Vous pouvez acheter des heures de test pour déployer un serveur sans abonnement.</p>
                <a href="{{ route('test-hours') }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-blue-950 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-amber-500/25">
                    ⏱️ Acheter des heures de test
                </a>
            </div>
        @else
            {{-- Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Niveau GPU</p>
                    <p class="text-lg font-bold text-blue-950">{{ config("runpod.gpu_tiers.{$tenant->selected_gpu_tier}.display", $tenant->selected_gpu_tier) }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Abonnement mensuel</p>
                    <p class="text-lg font-bold text-amber-600">{{ number_format($tenant->monthly_subscription_price, 2) }} $</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Statut</p>
                    <p class="inline-flex items-center gap-1.5 text-sm font-bold px-3 py-1.5 rounded-lg {{ $tenant->isSubscriptionActive() ? 'bg-green-100 text-green-700' : ($tenant->hasTestHours() ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $tenant->isSubscriptionActive() ? 'bg-green-500' : ($tenant->hasTestHours() ? 'bg-blue-500' : 'bg-yellow-500') }}"></span>
                        {{ $tenant->isSubscriptionActive() ? 'Actif' : ($tenant->hasTestHours() ? 'Test' : 'En attente') }}
                    </p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">{{ $tenant->hasTestHours() ? 'Heures de test' : 'Modèle' }}</p>
                    <p class="text-lg font-bold text-blue-950 truncate">
                        @if ($tenant->hasTestHours())
                            ⏱️ {{ number_format($tenant->test_hours_balance, 1) }}h
                        @else
                            {{ config("runpod.recommended_models.{$tenant->recommended_model_id}.display", $tenant->recommended_model_id) }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Pod card --}}
            @if ($pod)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-blue-950">Votre pod</h2>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold
                            {{ $pod->isRunning() ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $pod->isRunning() ? 'bg-green-500' : 'bg-yellow-500' }}"></span>
                            @if (strtoupper($pod->status->value ?? $pod->status) === 'RUNNING') En ligne
                            @elseif (strtoupper($pod->status->value ?? $pod->status) === 'CREATING') Création...
                            @elseif (strtoupper($pod->status->value ?? $pod->status) === 'STOPPED') Arrêté
                            @else {{ $pod->status->value ?? $pod->status }}
                            @endif
                        </span>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">ID du pod</p>
                                <p class="font-mono text-sm text-gray-800 bg-gray-50 px-3 py-2 rounded-lg">{{ $pod->runpod_pod_id }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Modèle déployé</p>
                                <p class="font-medium text-gray-800">{{ config("runpod.recommended_models.{$pod->model_id}.display", $pod->model_id) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Provisionné le</p>
                                <p class="font-medium text-gray-800">{{ $pod->provisioned_at?->format('d/m/Y à H:i') ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">URL de connexion</p>
                                @if ($pod->webui_url)
                                    <a href="{{ $pod->webui_url }}" target="_blank" class="text-blue-600 hover:underline text-sm break-all font-medium">{{ $pod->webui_url }}</a>
                                @else
                                    <p class="text-gray-400 text-sm">En attente...</p>
                                @endif
                            </div>
                            @if ($pod->credentialUrl())
                                <div class="pt-2">
                                    <a href="{{ $pod->credentialUrl() }}" target="_blank"
                                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-blue-950 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-amber-500/25">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Accéder à mon IA
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="font-bold text-amber-800">Provisionnement en cours</p>
                    <p class="text-amber-600 text-sm mt-1">Cela prend généralement 2 à 5 minutes. Vous recevrez un email dès que votre serveur sera prêt.</p>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
