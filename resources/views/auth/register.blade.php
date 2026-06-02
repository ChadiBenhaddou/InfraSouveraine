<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('infra-white.png') }}" alt="InfraSouveraine" class="h-16 w-auto">
            </div>
            <h2 class="text-center text-3xl font-bold text-white">Créer votre compte</h2>
            <p class="mt-2 text-center text-sm text-blue-200/70">
                Déjà un compte ? <a href="{{ route('login') }}" class="font-medium text-amber-400 hover:text-amber-300">Connectez-vous</a>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl py-8 px-4 shadow-2xl sm:px-10">
                <form class="space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-blue-200">Nom complet</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}"
                            class="mt-1 block w-full bg-white/10 border border-white/20 text-white rounded-xl shadow-sm focus:border-amber-500 focus:ring-amber-500 placeholder-gray-400">
                        @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-blue-200">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                            class="mt-1 block w-full bg-white/10 border border-white/20 text-white rounded-xl shadow-sm focus:border-amber-500 focus:ring-amber-500 placeholder-gray-400">
                        @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-blue-200">Mot de passe</label>
                        <input id="password" name="password" type="password"
                            class="mt-1 block w-full bg-white/10 border border-white/20 text-white rounded-xl shadow-sm focus:border-amber-500 focus:ring-amber-500 placeholder-gray-400">
                        @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-blue-200">Confirmer le mot de passe</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full bg-white/10 border border-white/20 text-white rounded-xl shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                    <button type="submit"
                        class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-400 text-blue-950 rounded-xl font-bold transition shadow-lg shadow-amber-500/25">
                        Créer mon compte
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
