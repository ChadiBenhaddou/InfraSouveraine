<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                    <span class="text-5xl">🎉</span>
                </div>

                <h1 class="text-4xl font-bold text-gray-900 mb-4">Votre serveur IA est en cours de déploiement !</h1>
                <p class="text-lg text-gray-600 mb-6">
                    Votre paiement a été accepté. Nous provisionnons maintenant votre pod dédié.
                </p>

                <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">GPU</dt>
                            <dd class="font-medium">{{ config("runpod.gpu_tiers.{$tenant->selected_gpu_tier}.display", $tenant->selected_gpu_tier) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Modèle</dt>
                            <dd class="font-medium">{{ config("runpod.recommended_models.{$tenant->recommended_model_id}.display", $tenant->recommended_model_id) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Prix mensuel</dt>
                            <dd class="font-bold text-indigo-600">{{ number_format($tenant->monthly_subscription_price, 2) }} $/mois</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Statut</dt>
                            <dd class="font-medium text-green-600">⚙️ Provisionnement</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-indigo-50 rounded-xl p-4 mb-8">
                    <p class="text-sm text-gray-700">
                        📧 <strong>Consultez vos emails.</strong> Nous vous enverrons vos identifiants de connexion et le lien WebUI dès que le pod sera prêt (généralement 2 à 5 minutes).
                    </p>
                </div>

                @if (config('settings.testing_mode'))
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700">
                        🧪 <strong>Mode Test :</strong> Déploiement simulé. Aucun pod réel n'est créé.
                    </div>
                @endif

                <a href="{{ route('dashboard') }}" class="inline-block py-3 px-8 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Accéder au tableau de bord
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
