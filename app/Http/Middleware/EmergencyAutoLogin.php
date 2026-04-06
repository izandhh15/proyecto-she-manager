<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EmergencyAutoLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) env('EMERGENCY_AUTO_LOGIN', false)) {
            return $next($request);
        }

        if (! app()->environment('production')) {
            return $next($request);
        }

        // Skip static/health endpoints.
        if (
            $request->is('build/*')
            || $request->is('favicon.ico')
            || $request->is('robots.txt')
            || $request->is('up')
        ) {
            return $next($request);
        }

        if (! Auth::check()) {
            $userId = User::query()->orderBy('id')->value('id');

            if ($userId) {
                // Request-scoped auth avoids session/cookie desync loops on hosted proxies.
                Auth::onceUsingId($userId);
            }
        }

        return $next($request);
    }
}
