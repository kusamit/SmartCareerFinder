<?php
// app/Http/Middleware/AuthSeeker.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthSeeker
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'seeker') {
            return redirect()->route('login')->with('error', 'Please login as a Job Seeker.');
        }
        return $next($request);
    }
}
