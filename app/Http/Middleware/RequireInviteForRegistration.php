<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireInviteForRegistration
{
    public function handle(Request $request, Closure $next): Response
    {
        // Public registration is always enabled.
        return $next($request);
    }
}
