<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Jika pengguna belum terautentikasi, redirect ke halaman login
        if (auth()->onceBasic()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
