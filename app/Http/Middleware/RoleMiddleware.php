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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        // if user unauthenticated or has no role
        if (!$user || !$user->role) {
            return response()->json(['message' => 'Unauthorized: no role assigned'], 403);
        }

        $allowedRoles = [];

        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode(',', $role));
        }

        // if user role is not existing
        if (!in_array($user->role->name, $allowedRoles)) {
            return response()->json(['message' => 'Insufficient permission'], 403);
        }

        return $next($request);
    }
}