<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = auth()->user();

        // if user unauthenticated or has no role
        if (!$user || !$user->role) {
            return response()->json(['message' => 'Unauthorized: no role assigned'], 403);
        }

        // if user role is not existing
        if ($user->role->name !== $role) {
            return response()->json(['message' => 'Insufficient permission'], 403);
        }

        return $next($request);
    }
}
