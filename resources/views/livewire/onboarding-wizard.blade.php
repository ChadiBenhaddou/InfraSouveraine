<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        @if (config('settings.testing_mode'))
            <div class="mb-8 bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <div class="flex items-center gap-3 text-amber-800">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong>Mode Test</strong> — Aucun paiement réel, déploiement simulé à des fins de démonstration.
                    </div>
                </div>
            </div>
        @endif

        {{-- Step indicator --}}
        <div class="mb-10">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                @foreach ([['1', 'Cas d\'usage'], ['2', 'Recommandation'], ['3', 'Budget'], ['4', 'Paiement'], ['5', 'Terminé']] as $i => $item)
                    @php $stepNum = $i + 1; @endphp
                    @php $visited = $currentStep >= $stepNum; @endphp
                    <div class="flex items-center">
                        <div wire:click="goToStep({{ $stepNum }})"
                            class="flex items-center justify-center w-10 h-10 rounded-xl text-sm font-bold transition-all cursor-pointer
                            {{ $currentStep > $stepNum ? 'bg-blue-900 text-white shadow-md hover:bg-blue-800' : ($currentStep === $stepNum ? 'bg-amber-500 text-blue-950 shadow-lg shadow-amber-200' : 'bg-gray-100 text-gray-400') }}">
                            {{ $currentStep > $stepNum ? '✓' : $item[0] }}
                        </div>
                        <span wire:click="goToStep({{ $stepNum }})"
                            class="ml-2.5 text-sm font-medium cursor-pointer {{ $currentStep === $stepNum ? 'text-blue-900' : 'text-gray-400 hover:text-blue-700' }} hidden sm:inline">{{ $item[1] }}</span>
                    </div>
                    @if ($i < 4)
                        <div class="flex-1 h-0.5 mx-3 {{ $currentStep > $stepNum ? 'bg-blue-900' : 'bg-gray-200' }} rounded-full"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Step 1: Use Case --}}
        @if ($currentStep === 1)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-10">
                <div class="max-w-2xl mx-auto">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-blue-950 mb-2">Décrivez votre projet</h2>
                    <p class="text-gray-500 mb-8">Dites-nous comment vous comptez utiliser l'IA. Nous recommanderons le modèle et le GPU parfaits.</p>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom de l'entreprise / projet</label>
                            <input type="text" wire:model="companyName"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-900 focus:ring-blue-900 px-4 py-3"
                                placeholder="Votre entreprise ou projet">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description de votre cas d'usage</label>
                            <textarea wire:model="useCase" rows="4"
                                class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-blue-900 focus:ring-blue-900 px-4 py-3"
                                placeholder="Ex : Analyse de documents juridiques confidentiels pour mon cabinet d'avocats. Nous avons besoin de analyser des contrats avec une confidentialité totale..."></textarea>
                            @error('useCase') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-3">Ou choisissez un cas typique :</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button type="button" wire:click="$set('useCase', 'Analyse de documents juridiques confidentiels pour mon cabinet d\'avocats')"
                                    class="p-4 text-left text-sm border-2 border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50/50 transition font-medium text-gray-700 hover:text-blue-900">
                                    ⚖️ Analyse juridique
                                </button>
                                <button type="button" wire:click="$set('useCase', 'Assistant de rédaction créative pour rédiger des histoires et du contenu marketing')"
                                    class="p-4 text-left text-sm border-2 border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50/50 transition font-medium text-gray-700 hover:text-blue-900">
                                    ✍️ Rédaction créative
                                </button>
                                <button type="button" wire:click="$set('useCase', 'Assistant IA de codage pour mon équipe de développeurs')"
                                    class="p-4 text-left text-sm border-2 border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50/50 transition font-medium text-gray-700 hover:text-blue-900">
                                    💻 Assistant codage
                                </button>
                            </div>
                        </div>

                        <button wire:click="submitUseCase" wire:loading.attr="disabled"
                            class="w-full py-3.5 px-6 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold transition disabled:opacity-50 shadow-lg shadow-blue-900/20">
                            <span wire:loading.remove wire:target="submitUseCase">Trouver ma configuration idéale →</span>
                            <span wire:loading wire:target="submitUseCase">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: Recommendation --}}
        @if ($currentStep === 2 && $recommendation)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-10">
                <div class="max-w-4xl mx-auto">
                    <div class="text-center mb-10">
                        <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h2 class="text-3xl font-bold text-blue-950 mb-2">Modèle recommandé</h2>
                        <p class="text-gray-500">Basé sur votre cas d'usage, voici la configuration optimale :</p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-100 mb-10">
                        <div class="flex items-center gap-2 text-sm font-semibold text-blue-600 mb-2 uppercase tracking-wider">Recommandation IA</div>
                        <div class="text-3xl font-bold text-blue-950 mb-2">
                            {{ config("runpod.recommended_models.{$recommendation['model_id']}.display", $recommendation['model_id']) }}
                        </div>
                        <p class="text-gray-600">{{ $recommendation['reasoning'] ?? '' }}</p>
                    </div>

                    <h3 class="text-xl font-bold text-blue-950 mb-6">Choisissez votre puissance de calcul</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach ($compatibleGpus as $tier => $gpu)
                            <button wire:click="selectGpu('{{ $tier }}')"
                                class="text-left p-6 bg-white border-2 border-gray-100 rounded-2xl hover:border-blue-200 hover:shadow-lg transition-all group">

                                <div class="flex items-start justify-between mb-4">
                                    <h4 class="font-bold text-blue-950 text-lg">{{ $gpu['display'] }}</h4>
                                    <span class="text-lg font-bold text-amber-600 whitespace-nowrap ml-2">{{ number_format($gpu['hourly_rate'], 2) }} $/h</span>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">VRAM</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-900 rounded-full" style="width: {{ min(100, ($gpu['vram_gb'] / 80) * 100) }}%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-800">{{ $gpu['vram_gb'] }} Go</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">Tokens/s estimés</span>
                                        <span class="font-semibold text-gray-800">{{ number_format($gpu['tps_estimate']) }} TPS</span>
                                    </div>
                                    <div class="border-t border-gray-100 pt-3 mt-3 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-400">vs GPT-4o</span>
                                            <span class="font-semibold {{ $gpu['performance_vs_gpt4'] >= 1 ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ number_format($gpu['performance_vs_gpt4'] * 100, 0) }}%
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-400">vs Claude 3.5 Sonnet</span>
                                            <span class="font-semibold {{ $gpu['performance_vs_claude35'] >= 1 ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ number_format($gpu['performance_vs_claude35'] * 100, 0) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-8 text-center">
                        <button wire:click="goToStep(1)"
                            class="text-sm text-gray-400 hover:text-blue-700 transition font-medium">
                            ← Modifier mon cas d'usage
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 3: Pricing & Schedule --}}
        @if ($currentStep === 3 && $pricing)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-10">
                <div class="max-w-4xl mx-auto">
                    <div class="text-center mb-10">
                        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h2 class="text-3xl font-bold text-blue-950 mb-2">Planifiez vos horaires</h2>
                        <p class="text-gray-500">Choisissez quand votre serveur IA sera en ligne. Vous ne payez que pour les heures sélectionnées.</p>
                    </div>

                    {{-- Schedule Selector --}}
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 mb-8">
                        <h3 class="font-bold text-blue-950 mb-4">Horaires hebdomadaires</h3>
                        <x-weekly-schedule-selector :schedule="$weeklySchedule" />
                    </div>

                    {{-- Pricing Summary --}}
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <h3 class="font-bold text-blue-950 mb-4">Configuration</h3>
                            <dl class="space-y-3 text-sm">
                                @php $modelDisplay = config("runpod.recommended_models.{$recommendation['model_id']}.display", $recommendation['model_id']); @endphp
                                <x-detail-row label="Modèle" :value="$modelDisplay" />
                                <x-detail-row label="GPU" :value="config('runpod.gpu_tiers.' . $selectedGpuTier . '.display')" />
                                <x-detail-row label="VRAM" :value="config('runpod.gpu_tiers.' . $selectedGpuTier . '.vram_gb') . ' Go'" />
                                <x-detail-row label="Tokens/s" :value="config('runpod.gpu_tiers.' . $selectedGpuTier . '.tps_estimate') . ' TPS'" />
                            </dl>
                        </div>

                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                            <h3 class="font-bold text-blue-950 mb-4">Détail des coûts mensuels</h3>
                            <dl class="space-y-3 text-sm">
                                <x-detail-row label="Compute GPU ({{ $pricing['monthly_hours'] }}h)" :value="number_format($pricing['compute_monthly_cost'] ?? 0, 2) . ' $'" />
                                <x-detail-row label="Stockage ({{ $pricing['storage_gb'] }} Go)" :value="number_format($pricing['storage_monthly_cost'] ?? 0, 2) . ' $'" />
                                <div class="border-t border-blue-200/50 pt-2">
                                    <x-detail-row label="Coût de base" :value="number_format($pricing['base_monthly_cost'], 2) . ' $'" />
                                </div>
                                <x-detail-row label="Marge opérationnelle" :value="'+' . number_format($pricing['benefit_margin_amount'], 2) . ' $'" class="text-green-600" />
                                <x-detail-row label="Frais de plateforme" :value="'+' . number_format($pricing['fixed_platform_markup'], 2) . ' $'" class="text-green-600" />
                                <div class="border-t border-blue-300 pt-3 mt-2">
                                    <div class="flex items-center justify-between">
                                        <dt class="text-base font-bold text-blue-950">Total mensuel</dt>
                                        <dd class="text-2xl font-extrabold text-blue-900">{{ number_format($pricing['monthly_subscription_price'], 2) }} $</dd>
                                    </div>
                                </div>
                            </dl>
                            @php
                                $savingPercent = $pricing['monthly_hours'] > 0 ? round((1 - $pricing['monthly_hours'] / 730) * 100) : 0;
                            @endphp
                            @if ($savingPercent > 0)
                                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 text-center">
                                    🌿 Économisez <strong>{{ $savingPercent }}%</strong> vs un abonnement 24/7
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="goToStep(2)"
                            class="flex-1 py-3.5 px-6 bg-white border-2 border-gray-200 hover:border-blue-200 text-gray-700 hover:text-blue-700 rounded-xl font-bold transition">
                            ← Modifier le GPU
                        </button>
                        <button wire:click="reviewAndCheckout"
                            class="flex-1 py-3.5 px-6 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold transition shadow-lg shadow-blue-900/20">
                            Continuer vers le paiement →
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 4: Checkout --}}
        @if ($currentStep === 4)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-10">
                <div class="max-w-md mx-auto text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-blue-950 mb-2">Finalisez votre abonnement</h2>
                    <p class="text-gray-500 mb-8">Votre paiement est sécurisé par PayPal.</p>

                    <div class="bg-gray-50 rounded-2xl p-6 mb-8 text-left">
                        <dl class="space-y-3 text-sm">
                            <x-detail-row label="Forfait" :value="config('runpod.gpu_tiers.' . ($checkoutData['gpu_tier'] ?? '') . '.display') ?? ($checkoutData['gpu_tier'] ?? '')" />
                            <x-detail-row label="Heures/semaine" :value="($checkoutData['hours_per_week'] ?? 0) . 'h' " />
                            <x-detail-row label="Facturation" value="Mensuelle" />
                            <div class="border-t border-gray-200 pt-3 mt-3">
                                <div class="flex items-center justify-between">
                                    <dt class="text-lg font-bold text-blue-950">Total</dt>
                                    <dd class="text-2xl font-extrabold text-blue-900">{{ number_format($checkoutData['monthly_price'] ?? 0, 2) }} $/mois</dd>
                                </div>
                            </div>
                        </dl>
                    </div>

                    @if (config('settings.testing_mode'))
                        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 text-left">
                            <strong>🧪 Mode Test :</strong> Aucun paiement réel. Cliquez ci-dessous pour simuler le déploiement complet.
                        </div>
                        <button wire:click="startTestDeployment" wire:loading.attr="disabled"
                            class="w-full py-3.5 px-6 bg-amber-500 hover:bg-amber-400 text-blue-950 rounded-xl font-bold transition shadow-lg shadow-amber-500/25">
                            <span wire:loading.remove wire:target="startTestDeployment">🚀 Lancer le déploiement de test</span>
                            <span wire:loading wire:target="startTestDeployment">Déploiement en cours...</span>
                        </button>
                    @elseif ($hasTestHours)
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700 text-left">
                            <strong>⏱️ Solde : {{ number_format($testHoursBalance, 1) }}h</strong> — 1 heure sera consommée pour ce déploiement.
                        </div>
                        <button wire:click="deployWithTestHours" wire:loading.attr="disabled"
                            class="w-full py-3.5 px-6 bg-amber-500 hover:bg-amber-400 text-blue-950 rounded-xl font-bold transition shadow-lg shadow-amber-500/25 mb-3">
                            <span wire:loading.remove wire:target="deployWithTestHours">⏱️ Déployer avec mes heures de test (1h)</span>
                            <span wire:loading wire:target="deployWithTestHours">Déploiement en cours...</span>
                        </button>
                        <div class="relative my-4">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                            <div class="relative flex justify-center text-sm"><span class="bg-white px-3 text-gray-400">ou</span></div>
                        </div>
                        <button wire:click="startCheckout" wire:loading.attr="disabled"
                            class="w-full py-3.5 px-6 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold transition shadow-lg shadow-blue-900/20">
                            <span wire:loading.remove wire:target="startCheckout">S'abonner avec PayPal →</span>
                            <span wire:loading wire:target="startCheckout">Redirection...</span>
                        </button>
                    @else
                        <button wire:click="startCheckout" wire:loading.attr="disabled"
                            class="w-full py-3.5 px-6 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold transition shadow-lg shadow-blue-900/20">
                            <span wire:loading.remove wire:target="startCheckout">Payer avec PayPal →</span>
                            <span wire:loading wire:target="startCheckout">Redirection...</span>
                        </button>
                        <p class="text-xs text-gray-400 mt-4">Sécurisé par PayPal. Vos informations de paiement ne sont jamais stockées sur nos serveurs.</p>
                    @endif

                    <div class="mt-6">
                        <button wire:click="goToStep(3)"
                            class="w-full py-3.5 px-6 bg-white border-2 border-gray-200 hover:border-blue-200 text-gray-700 hover:text-blue-700 rounded-xl font-bold transition">
                            ← Modifier les horaires
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 5: Success --}}
        @if ($currentStep === 5)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-10 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-3xl font-bold text-blue-950 mb-3">Déploiement en cours</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Votre serveur IA est en cours de provisionnement. Vous recevrez un email avec vos identifiants sous 2 à 5 minutes.</p>

                <div class="bg-blue-50 rounded-2xl p-6 inline-block max-w-sm mx-auto">
                    <div class="flex items-center justify-center gap-3 text-blue-800">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        <span class="font-medium">Provisionnement en cours...</span>
                    </div>
                </div>

                @if (config('settings.testing_mode'))
                    <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 max-w-md mx-auto">
                        🧪 <strong>Mode Test :</strong> Un déploiement simulé a été créé. Rendez-vous sur le tableau de bord.
                    </div>
                @else
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700 max-w-md mx-auto">
                        ⏱️ Votre serveur IA est en cours de provisionnement. Vous recevrez un email sous 2 à 5 minutes.
                    </div>
                @endif

                <a href="{{ route('dashboard') }}" class="mt-8 inline-flex items-center gap-2 py-3.5 px-8 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold transition shadow-lg shadow-blue-900/20">
                    Accéder au tableau de bord
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
