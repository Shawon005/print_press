<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $hasRole = $user->roles()->whereIn('name', $roles)->exists();
        if (! $hasRole) {
            abort(403, 'Insufficient role permission.');
        }

        return $next($request);
    }
}
