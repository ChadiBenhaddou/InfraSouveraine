<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InfraSouveraine — @yield('title', 'Tableau de bord')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-10">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <img src="{{ asset('infra-blue.png') }}" alt="InfraSouveraine" class="h-14 w-auto">
                    </a>
                    @auth
                        <div class="hidden md:flex items-center gap-1">
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition">Tableau de bord</a>
                            <a href="{{ route('onboarding.wizard') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition">Déployer</a>
                            <a href="{{ route('test-hours') }}" class="px-4 py-2 text-sm font-medium text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition">⏱️ Heures de test</a>
                        </div>
                    @endauth
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <span class="text-sm text-gray-500">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition">Déconnexion</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <main>
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
