<div>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-blue-950">Acheter des heures de test</h1>
            <p class="text-gray-500 mt-1">Testez l'infrastructure avec des pods réels, sans abonnement.</p>
        </div>

        @if (session('message'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4 text-green-700 text-sm font-medium">
                {{ session('message') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 text-red-700 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-6 mb-10">
            @foreach ($packs as $hours => $pack)
                <button wire:click="selectPack('{{ $hours }}')" type="button"
                    class="text-left p-6 rounded-2xl border-2 transition-all {{ $selectedPack === $hours ? 'border-amber-500 bg-amber-50 shadow-lg shadow-amber-100' : 'border-gray-100 bg-white hover:border-blue-200 hover:shadow-md' }}">
                    <div class="text-3xl font-extrabold text-blue-950 mb-1">{{ $pack['hours'] }}h</div>
                    <div class="text-lg font-bold text-amber-600 mb-3">{{ number_format($pack['price'] / 100, 2) }} $</div>
                    <div class="text-sm text-gray-500">{{ $pack['desc'] }}</div>
                    @if ($selectedPack === $hours)
                        <div class="mt-3 text-xs font-semibold text-amber-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Sélectionné
                        </div>
                    @endif
                </button>
            @endforeach
        </div>

        @if ($tenant && $balance > 0)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Solde actuel</p>
                        <p class="text-3xl font-extrabold text-blue-900">{{ number_format($balance, 1) }}h</p>
                    </div>
                    <a href="{{ route('onboarding.wizard') }}" class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/20">
                        Déployer un serveur →
                    </a>
                </div>
            </div>
        @endif

        @if ($selectedPack)
            @if (config('settings.testing_mode'))
                <button wire:click="purchase"
                    class="w-full py-3.5 px-6 bg-amber-500 hover:bg-amber-400 text-blue-950 rounded-xl font-bold transition shadow-lg shadow-amber-500/25">
                    🧪 Ajouter gratuitement (mode test)
                </button>
            @else
                <button wire:click="purchase" wire:loading.attr="disabled"
                    class="w-full py-3.5 px-6 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold transition shadow-lg shadow-blue-900/20">
                    <span wire:loading.remove wire:target="purchase">
                        Acheter {{ $packs[$selectedPack]['label'] }} — {{ number_format($packs[$selectedPack]['price'] / 100, 2) }} $
                    </span>
                    <span wire:loading wire:target="purchase">Redirection vers Stripe...</span>
                </button>
                <p class="text-xs text-gray-400 mt-3 text-center">Paiement sécurisé par Stripe.</p>
            @endif

            <button wire:click="$set('selectedPack', null)" class="w-full text-center text-sm text-gray-400 hover:text-gray-600 mt-3 transition">
                Annuler
            </button>
        @elseif (!$selectedPack && !$balance)
            <p class="text-center text-gray-400 text-sm">Sélectionnez un pack ci-dessus pour commencer.</p>
        @endif
    </div>
</div>
