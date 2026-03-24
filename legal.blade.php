<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0B1120">

        <title>Aviso Legal - {{ config('app.name') }}</title>

        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">

        <script>(function(){var t=localStorage.getItem('virtua-theme');if(t==='light'){document.documentElement.classList.add('light');document.querySelector('meta[name=theme-color]')?.setAttribute('content','#ffffff');}})()</script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface-900 text-text-primary">
        <div class="min-h-screen flex flex-col">
            <header class="py-8 flex justify-center">
                <a href="{{ url('/') }}" class="inline-block">
                    <x-application-logo />
                </a>
            </header>

            <main class="flex-1 max-w-3xl mx-auto w-full px-4 sm:px-6 pb-12">
                <div class="bg-surface-800 border border-border-default rounded-xl p-6 sm:p-10">
                    <h1 class="font-heading text-2xl sm:text-3xl font-bold uppercase tracking-wide text-text-primary mb-8">Aviso Legal</h1>

                    <div class="space-y-5 text-sm sm:text-base leading-relaxed text-text-body">
                        <p>SheManager es un proyecto de software de codigo abierto desarrollado por Pablo Roman con fines educativos y de entretenimiento. Este proyecto no tiene animo de lucro y se distribuye de forma gratuita.</p>

                        <div>
                            <p class="font-semibold text-text-primary mb-2">No esta afiliado, patrocinado ni respaldado por:</p>
                            <ul class="space-y-1.5 text-text-secondary">
                                <li class="flex items-start gap-2">
                                    <span class="text-text-faint mt-0.5">&bull;</span>
                                    La Liga de Futbol Profesional
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-text-faint mt-0.5">&bull;</span>
                                    La Real Federacion Espanola de Futbol
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-text-faint mt-0.5">&bull;</span>
                                    Ningun club de futbol
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-text-faint mt-0.5">&bull;</span>
                                    Ningun jugador o personal deportivo
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-text-faint mt-0.5">&bull;</span>
                                    FIFA, UEFA, FIFPro, Soccerdonna, Transfermarkt ni ninguna otra entidad
                                </li>
                            </ul>
                        </div>

                        <p>Los nombres de clubes, competiciones y jugadoras son propiedad de sus respectivos titulares y se utilizan unicamente con fines de identificacion en un contexto de simulacion ficticia.</p>

                        <p>Los valores y estadisticas de jugadoras son <strong class="font-semibold text-text-primary">completamente ficticios</strong> y generados por algoritmos del juego. No representan evaluaciones reales.</p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-border-default">
                        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-text-secondary transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Volver
                        </a>
                    </div>
                </div>
            </main>

            <footer class="bg-surface-800/40">
                <div class="border-t border-border-default/50">
                    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
                        <div class="flex flex-col items-center gap-3">
                            <div class="-skew-x-12 bg-text-faint/15 px-3 py-0.5">
                                <span class="skew-x-12 inline-block text-lg font-extrabold text-text-faint tracking-tight" style="font-family: 'Barlow Semi Condensed', sans-serif;">SheManager</span>
                            </div>
                            <p class="text-xs text-text-faint">
                                &copy; {{ date('Y') }} Pablo Roman | <a href="https://github.com/pabloroman/virtua-fc" target="_blank" class="hover:text-text-muted transition-colors">Proyecto Open Source</a>
                            </p>
                            <x-theme-toggle />
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
