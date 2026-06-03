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
                    <span class="text-blue-200 text-sm">Hébergé en Europe — données confidentielles garanties</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6 tracking-tight">
                    L'IA privée<br>
                    <span class="bg-gradient-to-r from-amber-300 to-amber-500 text-transparent bg-clip-text">pour votre entreprise</span>
                </h1>
                <p class="text-xl text-blue-200/80 max-w-3xl mx-auto mb-12 leading-relaxed">
                    Donnez à votre équipe un accès sécurisé à l'IA générative — sans fuite de données, sans réentraînement sur vos documents, sans dépendance à des géants américains.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-blue-950 px-8 py-4 rounded-xl text-lg font-bold transition shadow-xl shadow-amber-500/30 inline-flex items-center justify-center gap-2">
                        Déployer mon IA privée
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('register') }}" class="border border-blue-400/30 text-blue-200 hover:bg-blue-800/30 px-8 py-4 rounded-xl text-lg font-medium transition inline-flex items-center justify-center gap-2">
                        Voir les offres →
                    </a>
                </div>
            </div>

            {{-- Trust bar --}}
            <div class="mt-24 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">100%</div>
                    <div class="text-blue-300 text-sm">Données privées<br>zéro réentraînement</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">€119</div>
                    <div class="text-blue-300 text-sm">Prix mensuel dès<br>20h/semaine</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">&lt; 5 min</div>
                    <div class="text-blue-300 text-sm">Déploiement<br>automatisé</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white">UE</div>
                    <div class="text-blue-300 text-sm">Hébergement<br>souverain</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem / Solution --}}
    <section class="bg-white py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Pourquoi une IA privée ?</h2>
                <p class="text-lg text-gray-500">ChatGPT, Claude et Gemini entraînent leurs modèles sur vos conversations. Pour un usage professionnel, c'est un risque juridique et stratégique.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-red-50 rounded-2xl p-8 border border-red-100">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-950 mb-3">Vos données restent chez vous</h3>
                    <p class="text-gray-500 leading-relaxed">Aucune donnée ne quitte votre pod dédié. Pas de réentraînement, pas de fuite, pas d'analyse par un tiers.</p>
                </div>
                <div class="bg-amber-50 rounded-2xl p-8 border border-amber-100">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-950 mb-3">Analysez vos documents internes</h3>
                    <p class="text-gray-500 leading-relaxed">Importez vos PDFs, contrats, rapports. Posez des questions en langage naturel. L'IA répond à partir de vos seuls documents.</p>
                </div>
                <div class="bg-green-50 rounded-2xl p-8 border border-green-100">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-950 mb-3">Jusqu'à 70% moins cher que ChatGPT Team</h3>
                    <p class="text-gray-500 leading-relaxed">Payez uniquement les heures d'utilisation. À partir de 119 $/mois pour une équipe, au lieu de 300 $/mois sur ChatGPT Team.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Comment ça marche</h2>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto">Votre serveur IA privé est déployé en moins de 5 minutes.</p>
            </div>
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">1</span>
                    </div>
                    <h3 class="font-bold text-blue-950 mb-2">Décrivez votre besoin</h3>
                    <p class="text-sm text-gray-500">Analyse juridique, rédaction, code, SAV — on vous recommande le meilleur modèle.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">2</span>
                    </div>
                    <h3 class="font-bold text-blue-950 mb-2">Choisissez vos horaires</h3>
                    <p class="text-sm text-gray-500">Sélectionnez les plages horaires où votre IA sera disponible. Vous ne payez que ça.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">3</span>
                    </div>
                    <h3 class="font-bold text-blue-950 mb-2">Déploiement automatique</h3>
                    <p class="text-sm text-gray-500">Votre pod GPU est provisionné en moins de 5 min. LLM pré-installé, prêt à l'emploi.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">4</span>
                    </div>
                    <h3 class="font-bold text-blue-950 mb-2">Posez vos questions</h3>
                    <p class="text-sm text-gray-500">Interface chat privée. Importez des documents. L'IA répond — vos données restent sur votre pod.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing comparison --}}
    <section class="bg-white py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Comparez par vous-même</h2>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto">Une transparence totale sur nos prix. Vous ne payez que ce que vous utilisez.</p>
            </div>

            <div class="max-w-4xl mx-auto">
                @php
                    $tiers = config('runpod.gpu_tiers');
                    $marginRate = config('settings.default_benefit_margin', 0.35);
                    $markup = config('settings.fixed_platform_markup', 9.99);
                    $storageCostPerGb = config('settings.storage_cost_per_gb_monthly', 0.10);
                @endphp
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach ($tiers as $tierKey => $tier)
                        @php
                            $weeklyCost40h = ($tier['hourly_rate'] * 40 * 4.33) + (50 * $storageCostPerGb);
                            $price40h = round(($weeklyCost40h * (1 + $marginRate)) + $markup, 0);
                            $weeklyCost20h = ($tier['hourly_rate'] * 20 * 4.33) + (50 * $storageCostPerGb);
                            $price20h = round(($weeklyCost20h * (1 + $marginRate)) + $markup, 0);
                        @endphp
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-lg transition">
                            <h3 class="font-bold text-blue-950 text-lg mb-1">{{ $tier['display'] }}</h3>
                            <p class="text-sm text-gray-400 mb-4">{{ $tier['vram_gb'] }} Go VRAM</p>
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">20h/semaine</span>
                                    <span class="font-bold text-blue-900">{{ number_format($price20h, 0) }} $/mois</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">40h/semaine (bureau)</span>
                                    <span class="font-bold text-blue-900">{{ number_format($price40h, 0) }} $/mois</span>
                                </div>
                                <div class="flex items-center justify-between text-sm border-t border-gray-200 pt-2">
                                    <span class="text-gray-500">24/7</span>
                                    <span class="font-bold text-gray-400">{{ number_format(($tier['hourly_rate'] * 730 + 50 * $storageCostPerGb) * (1 + $marginRate) + $markup, 0) }} $/mois</span>
                                </div>
                            </div>
                            <a href="{{ route('register') }}" class="block w-full text-center py-2.5 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-sm font-bold transition">
                                Choisir
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-200 text-center">
                    <p class="text-amber-800 font-medium">
                        💰 <strong>ChatGPT Team :</strong> 30 $/utilisateur/mois (10 utilisateurs = 300 $/mois).
                        <br class="sm:hidden">
                        <strong>InfraSouveraine :</strong> À partir de <strong>{{ number_format($price20h, 0) }} $/mois</strong> pour toute l'équipe — données privées garanties.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Une infrastructure pensée pour les professionnels</h2>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto">Pas de SaaS générique. Un serveur isolé, rien que pour vous.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="flex gap-4 bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-950 mb-1">Isolation totale</h3>
                        <p class="text-sm text-gray-500">Un GPU dédié. Votre pod est strictement isolé. Aucun partage de ressources.</p>
                    </div>
                </div>
                <div class="flex gap-4 bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-950 mb-1">Hébergement souverain</h3>
                        <p class="text-sm text-gray-500">Infrastructure européenne. Vos données ne traversent pas l'Atlantique.</p>
                    </div>
                </div>
                <div class="flex gap-4 bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-950 mb-1">Documents internes</h3>
                        <p class="text-sm text-gray-500">Importez PDF, DOCX, TXT. L'IA répond à partir de vos documents — sans les partager.</p>
                    </div>
                </div>
                <div class="flex gap-4 bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-950 mb-1">Déploiement en 5 min</h3>
                        <p class="text-sm text-gray-500">De la souscription au chat opérationnel en moins de 5 minutes. Entièrement automatisé.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Models --}}
    <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-blue-950 mb-4">Modèles disponibles</h2>
                <p class="text-gray-500">Les meilleurs modèles open-source, préconfigurés et prêts à l'emploi.</p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach (['Llama 3 70B', 'Mistral 7B', 'Mixtral 8x7B', 'CodeLlama 34B', 'DeepSeek Coder', 'Phi-3 Mini', 'Gemma 2 9B', 'Qwen 2 72B'] as $model)
                    <span class="bg-gray-50 border border-gray-200 rounded-xl px-5 py-3 text-sm font-medium text-gray-700 shadow-sm">{{ $model }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-blue-950 py-20">
        <div class="max-w-3xl mx-auto text-center px-4">
            <h2 class="text-4xl font-bold text-white mb-4">Prêt à passer à l'IA privée ?</h2>
            <p class="text-blue-200/80 text-lg mb-10">Créez votre compte en 30 secondes. Aucune carte bancaire requise pour commencer.</p>
            <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-blue-950 px-8 py-4 rounded-xl text-lg font-bold transition shadow-xl shadow-amber-500/25 inline-flex items-center gap-2">
                Commencer gratuitement
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-blue-950 border-t border-blue-800/50 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-blue-400 text-sm">
            &copy; {{ date('Y') }} InfraSouveraine. IA privée pour entreprises. Hébergement souverain en Europe. Tous droits réservés.
        </div>
    </footer>
</x-guest-layout>
