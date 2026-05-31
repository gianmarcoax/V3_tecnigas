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
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        // Admin has access to everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            // Redirect to dashboard with error or just abort
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
