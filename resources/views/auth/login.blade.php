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
                    Accede a tus partidas y enseÃ±a plantilla, mercado, finanzas y competicion con una pantalla de entrada limpia, rapida y lista para demo.
                </p>
            </div>
        </div>

        <x-auth-session-status class="rounded-2xl border border-success-tint-border bg-success-tint px-4 py-3 text-sm text-accent-green" :status="session('status')" />

        <a
            href="{{ route('auth.google.redirect') }}"
            class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-white/12 bg-slate-100/90 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-200"
        >
            <span aria-hidden="true">Google</span>
            <span>{{ __('auth.continue_with_google') }}</span>
        </a>

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
                    placeholder="Introduce tu contrasena"
                    required
                    autocomplete="current-password"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <input id="remember_me" type="hidden" name="remember" value="true">

            <div class="rounded-2xl border border-white/8 bg-surface-900/70 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-accent-gold">Que vas a enseÃ±ar</p>
                <ul class="mt-3 space-y-2 text-sm text-text-secondary">
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-accent-blue"></span>
                        <span>Plantillas y progresion de equipo con acceso directo a la partida.</span>
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
        </form>
    </div>
</x-guest-layout>

