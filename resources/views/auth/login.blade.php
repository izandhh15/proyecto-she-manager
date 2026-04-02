<x-guest-layout>
    <div class="space-y-8">
        <div class="space-y-3">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/6 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.26em] text-text-secondary">
                Acceso al juego
            </div>

            <div class="space-y-2">
                <h2 class="font-heading text-4xl leading-none tracking-tight text-white sm:text-5xl">
                    Entra al banquillo.
                </h2>
                <p class="max-w-lg text-sm leading-6 text-text-body sm:text-base">
                    Accede a tus partidas y enseña plantilla, mercado, finanzas y competición con una pantalla de entrada limpia, rápida y lista para demo.
                </p>
            </div>
        </div>

        <x-auth-session-status class="rounded-2xl border border-success-tint-border bg-success-tint px-4 py-3 text-sm text-accent-green" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <x-input-label for="email" class="text-xs font-semibold uppercase tracking-[0.22em] text-text-muted" :value="__('auth.Email')" />
                <x-text-input
                    id="email"
                    class="mt-0 w-full bg-surface-900/80 px-4 py-3 text-base"
                    type="email"
                    name="email"
                    :value="old('email')"
                    placeholder="tu@club.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <x-input-label for="password" class="text-xs font-semibold uppercase tracking-[0.22em] text-text-muted" :value="__('auth.Password')" />

                <x-text-input
                    id="password"
                    class="mt-0 w-full bg-surface-900/80 px-4 py-3 text-base"
                    type="password"
                    name="password"
                    placeholder="Introduce tu contraseña"
                    required
                    autocomplete="current-password"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <input id="remember_me" type="hidden" name="remember" value="true">

            <div class="rounded-2xl border border-white/8 bg-surface-900/70 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-accent-gold">Qué vas a enseñar</p>
                <ul class="mt-3 space-y-2 text-sm text-text-secondary">
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-accent-blue"></span>
                        <span>Plantillas y progresión de equipo con acceso directo a la partida.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-accent-green"></span>
                        <span>Finanzas, mercado y scouting sin pasos intermedios ni pantalla rota.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-accent-primary"></span>
                        <span>Modo torneo y competiciones listas para abrir y compartir en directo.</span>
                    </li>
                </ul>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                @if (Route::has('password.request'))
                    <a class="text-sm text-text-secondary underline decoration-text-muted/60 underline-offset-4 transition hover:text-text-primary" href="{{ route('password.request') }}">
                        {{ __('auth.Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="w-full justify-center sm:w-auto sm:min-w-[220px]">
                    {{ __('auth.Log in') }}
                </x-primary-button>
            </div>

            @if (Route::has('register'))
                <x-primary-button-link href="{{ route('register') }}" color="amber" class="w-full">
                    {{ __('auth.Register') }}
                </x-primary-button-link>
            @endif
        </form>
    </div>
</x-guest-layout>
