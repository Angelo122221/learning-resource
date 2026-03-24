<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is logged in
        // 2. Check if the 'is_admin' column in the database is true
        if (!$request->user() || !$request->user()->is_admin) {
            // If not an admin, boot them to the standard dashboard
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}