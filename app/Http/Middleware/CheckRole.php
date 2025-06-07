<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            // This should ideally be handled by the 'auth' middleware first
            return redirect('login');
        }

        $user = Auth::user();
        if (!$user->hasAnyRole($roles)) {
            // You can redirect to a specific error page or just abort
            abort(403, 'Unauthorized action. You do not have the required role.');
        }

        return $next($request);
    }
}
