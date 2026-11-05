<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to one or more roles, e.g.:
 *   Route::middleware('role:System Administrator')->group(...)
 *   Route::middleware('role:System Administrator,HR Administrator')->group(...)
 *
 * Enforces RBAC server-side, per section 9 of the architecture guide:
 * "The backend remains the real authorization boundary."
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role || ! in_array($user->role->name, $roles, true)) {
            return response()->json([
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
