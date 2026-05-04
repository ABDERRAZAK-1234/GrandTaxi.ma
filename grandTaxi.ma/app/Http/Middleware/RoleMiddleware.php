<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict access based on user role.
 * Usage: ->middleware('role:admin') or ->middleware('role:driver')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Block drivers whose account is pending 
        if ($user->status === 'pending') {
            return response()->json([
                'message' => 'Votre compte est en attente de validation par un administrateur.',
                'status'  => 'pending',
            ], 403);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'message' => 'Votre compte a été suspendu.',
            ], 403);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Accès interdit. Rôle requis : ' . implode(' ou ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
