<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B1120">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <script>(function(){var t=localStorage.getItem('virtua-theme');if(t==='light'){document.documentElement.classList.add('light');document.querySelector('meta[name=theme-color]')?.setAttribute('content','#ffffff');}})()</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface-900 text-text-primary">
    <div class="relative min-h-screen overflow-hidden bg-surface-900">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.2),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(147,51,234,0.2),_transparent_28%),linear-gradient(180deg,_rgba(11,17,32,0.94),_rgba(15,23,42,0.98))]"></div>
        <div class="absolute inset-0 opacity-35 [background-image:linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] [background-size:72px_72px] [mask-image:radial-gradient(circle_at_center,black,transparent_82%)]"></div>
        <div class="pointer-events-none absolute -left-24 top-28 h-64 w-64 rounded-full bg-accent-blue/20 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 right-0 h-72 w-72 rounded-full bg-accent-primary/18 blur-3xl"></div>

        @if(config('beta.enabled'))
            <div class="relative z-20 border-b border-amber-400/20 bg-amber-400/10 px-4 py-2 text-center text-xs text-amber-200">
                <span class="font-semibold">{{ __('beta.badge') }}</span>
                <span class="mx-2 text-amber-400/80">/</span>
                {{ __('beta.login_notice') }}
                @if(config('beta.feedback_url'))
                    <span class="mx-2 text-amber-400/80">/</span>
                    <a href="{{ config('beta.feedback_url') }}" target="_blank" class="font-semibold underline decoration-amber-300/60 underline-offset-4 hover:text-amber-100">
                        {{ __('beta.send_feedback') }}
                    </a>
                @endif
            </div>
        @endif

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-4">
                    <x-application-logo />
                    <div class="hidden sm:block">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-accent-gold">Women's Football Manager</p>
                        <p class="mt-1 text-sm text-text-secondary">Plantillas, mercado, finanzas y torneos en una sola experiencia.</p>
                    </div>
                </a>

                <x-theme-toggle />
            </header>

            <main class="flex flex-1 items-center py-10 lg:py-14">
                <div class="grid w-full items-center gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(420px,0.9fr)] lg:gap-14">
                    <section class="order-2 max-w-2xl lg:order-1">
                        <div class="inline-flex items-center gap-2 rounded-full border border-accent-blue/20 bg-accent-blue/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-accent-blue">
                            Demo Ready
                        </div>

                        <h1 class="mt-6 font-heading text-5xl leading-none tracking-tight text-white sm:text-6xl">
                            Enseña tu universo de futbol con una entrada a la altura.
                        </h1>

                        <p class="mt-5 max-w-xl text-base leading-7 text-text-body sm:text-lg">
                            SheManager reune carrera de club, gestion economica, scouting y modo torneo con una interfaz preparada para enseñar plantilla, calendario y decisiones de despacho sin fricciones.
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-white/6 p-4 backdrop-blur">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-accent-gold">Modo carrera</p>
                                <p class="mt-3 text-2xl font-heading font-bold text-white">Club</p>
                                <p class="mt-2 text-sm text-text-secondary">Plantilla, presupuesto, fichajes y evolucion temporada a temporada.</p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/6 p-4 backdrop-blur">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-accent-blue">Mercado</p>
                                <p class="mt-3 text-2xl font-heading font-bold text-white">Scouting</p>
                                <p class="mt-2 text-sm text-text-secondary">Seguimiento, contratos, valoraciones y decisiones deportivas con contexto.</p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/6 p-4 backdrop-blur">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-accent-primary">Competicion</p>
                                <p class="mt-3 text-2xl font-heading font-bold text-white">Torneos</p>
                                <p class="mt-2 text-sm text-text-secondary">Selecciones, ligas y pantallas listas para compartir en directo.</p>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-3 text-sm text-text-secondary sm:grid-cols-2">
                            <div class="flex items-start gap-3 rounded-2xl border border-white/8 bg-surface-800/55 p-4">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-accent-green"></span>
                                <span>Interfaz oscura, clara y estable para enseñar el proyecto sin depender de hacks ni servidores colgados.</span>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl border border-white/8 bg-surface-800/55 p-4">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-accent-gold"></span>
                                <span>Acceso rapido a partidas, finanzas, plantilla, mercado y flujo de temporada desde el primer clic.</span>
                            </div>
                        </div>
                    </section>

                    <section class="order-1 lg:order-2">
                        <div {{ $attributes->merge(['class' => 'mx-auto w-full max-w-xl rounded-[28px] border border-white/10 bg-surface-800/82 p-6 shadow-2xl shadow-surface-900/40 backdrop-blur-xl sm:p-8']) }}>
                            <x-flash-message type="warning" :message="session('warning')" class="mb-4" />

                            {{ $slot }}
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
