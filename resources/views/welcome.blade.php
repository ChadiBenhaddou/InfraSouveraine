<x-guest-layout>
    {{-- Hero --}}
    <section class="relative min-h-screen bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900 overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyek0zNiAyNHYySDI0di0yaDEyeiIvPjwvZz48L2c+PC9zdmc+')] opacity-40"></div>
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32">
            <nav class="flex items-center justify-between mb-32">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('infra-white.png') }}" alt="InfraSouveraine" class="h-16 w-auto">
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition">Connexion</a>
                    <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-blue-950 px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-amber-500/25">Commencer</a>
                </div>
            </nav>

            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 bg-blue-800/40 border border-blue-700/30 rounded-full px-4 py-1.5 mb-8">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span class="text-blue-200 text-sm">Infrastructure souveraine — hébergé en Europe</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6 tracking-tight">
                    Votre IA,<br>
                    <span class="bg-gradient-to-r from-amber-300 to-amber-500 text-transparent bg-clip-text">souveraine et confidentielle</span>
                </h1>
                <p class="text-xl text-blue-200/80 max-w-2xl mx-auto mb-12 leading-relaxed">
                    Déployez votre propre serveur LLM isolé sur un GPU dédié. Aucune donnée ne quitte votre pod. Aucun entraînement. Aucune fuite.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-blue-950 px-8 py-4 rounded-xl text-lg font-bold transition shadow-xl shadow-amber-500/30 inline-flex items-center justify-center gap-2">
                        Déployer mon serveur IA
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#features" class="border border-blue-400/30 text-blue-200 hover:bg-blue-800/30 px-8 py-4 rounded-xl text-lg font-medium transition inline-flex items-center justify-center gap-2">
                        En savoir plus
                    </a>
                </div>
            </div>

            {{-- Trust bar --}}
            <div class="mt-24 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">100%</div>
                    <div class="text-blue-300 text-sm">Données privées</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">&lt; 5 min</div>
                    <div class="text-blue-300 text-sm">Déploiement</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">5</div>
                    <div class="text-blue-300 text-sm">Tiers GPU</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">10+</div>
                    <div class="text-blue-300 text-sm">Modèles LLM</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="bg-white py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Pourquoi InfraSouveraine ?</h2>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto">Une infrastructure pensée pour les professionnels exigeants en matière de confidentialité et de performance.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:border-blue-100 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-950 mb-3">Isolation totale</h3>
                    <p class="text-gray-500 leading-relaxed">Votre pod est strictement dédié. Aucun partage de ressources. Aucune donnée transite par un tiers.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:border-blue-100 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-950 mb-3">Déploiement flash</h3>
                    <p class="text-gray-500 leading-relaxed">De la souscription à un LLM opérationnel en moins de 5 minutes. Entièrement automatisé.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:border-blue-100 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-950 mb-3">Prix mensuel fixe</h3>
                    <p class="text-gray-500 leading-relaxed">Un abonnement prévisible, sans surprise. Compute GPU, stockage et bande passante inclus.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Models strip --}}
    <section class="bg-gray-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Modèles disponibles</h2>
                <p class="text-gray-500">Les meilleurs modèles open-source, préconfigurés et prêts à l'emploi.</p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach (['Llama 3 70B', 'Mistral 7B', 'Mixtral 8x7B', 'CodeLlama 34B', 'DeepSeek Coder', 'Phi-3 Mini', 'Gemma 2 9B', 'Qwen 2 72B'] as $model)
                    <span class="bg-white border border-gray-200 rounded-xl px-5 py-3 text-sm font-medium text-gray-700 shadow-sm">{{ $model }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-blue-950 py-20">
        <div class="max-w-3xl mx-auto text-center px-4">
            <h2 class="text-4xl font-bold text-white mb-4">Prêt à déployer votre IA souveraine ?</h2>
            <p class="text-blue-200/80 text-lg mb-10">Rejoignez les professionnels qui font confiance à InfraSouveraine pour leurs données sensibles.</p>
            <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-blue-950 px-8 py-4 rounded-xl text-lg font-bold transition shadow-xl shadow-amber-500/25 inline-flex items-center gap-2">
                Commencer maintenant
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-blue-950 border-t border-blue-800/50 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-blue-400 text-sm">
            &copy; {{ date('Y') }} InfraSouveraine. Hébergement LLM souverain. Tous droits réservés.
        </div>
    </footer>
</x-guest-layout>
