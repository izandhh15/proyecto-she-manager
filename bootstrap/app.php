<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        if ((bool) env('EMERGENCY_AUTO_LOGIN', false)) {
            $middleware->prepend(\App\Http\Middleware\EmergencyAutoLogin::class);
        }
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->redirectUsersTo(function (Request $request) {
            $userId = $request->user()?->id;

            $hasGames = \App\Models\Game::query()
                ->where('user_id', $userId)
                ->exists();

            return $hasGames
                ? route('dashboard')
                : route('select-team');
        });

        // Koyeb/Cloudflare forward original scheme and host via proxy headers.
        $middleware->trustProxies(at: '*');
        // Temporary production unblock: avoid 419 while session/auth is stabilized on Koyeb.
        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'forgot-password',
            'reset-password',
            'logout',
            'new-game',
            'game/*',
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'game.owner' => \App\Http\Middleware\EnsureGameOwnership::class,
            'beta.invite' => \App\Http\Middleware\RequireInviteForRegistration::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('auth.session_expired'),
                    'redirect' => route('login'),
                ], 419);
            }

            return redirect()->route('login')
                ->with('warning', __('auth.session_expired'));
        });
    })->create();
