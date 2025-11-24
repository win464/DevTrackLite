<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     * Expect roles as a pipe- or comma-separated list, e.g. 'admin' or 'admin|manager'
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if (! $roles) {
            // no specific role required
            return $next($request);
        }

        $allowed = preg_split('/[|,]/', $roles, flags: PREG_SPLIT_NO_EMPTY);

        if (! in_array($user->role ?? 'viewer', $allowed, true)) {
            abort(403, 'Forbidden. Insufficient role.');
        }

        return $next($request);
    }
}
