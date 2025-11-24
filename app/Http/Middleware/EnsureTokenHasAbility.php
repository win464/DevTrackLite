<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

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

        $bearer = $request->bearerToken();

        if (! $bearer) {
            abort(401, 'Unauthenticated.');
        }

        // Resolve the personal access token from the bearer token and check abilities directly.
        $pat = PersonalAccessToken::findToken($bearer);

        if (! $pat) {
            abort(401, 'Invalid token.');
        }

        if (! $pat->can($ability)) {
            abort(403, 'Token missing ability.');
        }

        return $next($request);
    }
}
