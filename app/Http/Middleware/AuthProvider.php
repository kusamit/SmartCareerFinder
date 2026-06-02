<?php
// app/Http/Middleware/AuthProvider.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthProvider
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'provider') {
            return redirect()->route('login')->with('error', 'Please login as a Job Provider.');
        }
        return $next($request);
    }
}
