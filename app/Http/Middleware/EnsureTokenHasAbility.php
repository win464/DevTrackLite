<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTokenHasAbility
{
    /**
     * Handle an incoming request that requires a token ability.
     * Usage: ->middleware('ability:admin:ping')
     */
    public function handle(Request $request, Closure $next, string $ability = null)
    {
        if (! $ability) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || ! $request->bearerToken()) {
            abort(401, 'Unauthenticated.');
        }

        if (! $user->tokenCan($ability)) {
            abort(403, 'Token missing ability.');
        }

        return $next($request);
    }
}
