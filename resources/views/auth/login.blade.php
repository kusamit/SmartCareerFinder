@extends('layouts.app')
@section('title', 'Login')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth-login.css') }}">
@endpush

@section('content')

<div class="bg-layer"></div>

<div class="auth-wrapper">

    <!-- LEFT SIDE (UNCHANGED CONTENT) -->
    <div class="left-info">

        <div class="left-title">
            Find better jobs,<br>
            <span>faster and smarter</span>
        </div>

        <div class="left-desc">
            Sign in to access personalized job recommendations, track applications,
            and connect with best suited jobs.
        </div>



    </div>

    <!-- LOGIN CARD -->
    <div class="auth-card">

        <div class="title">Welcome to Smart Career</div>
        <div class="subtitle">Sign in to continue your journey</div>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div style="margin-bottom: 14px;">
                <label class="label">Email</label>
                <input type="email" name="email"
                       value="{{ old('email') }}"
                       class="input"
                       placeholder="you@example.com"
                       required>
            </div>

            <div style="margin-bottom: 10px;">
                <label class="label">Password</label>
                <input type="password" name="password"
                       class="input"
                       placeholder="••••••••"
                       required>
            </div>

            <div class="remember-wrap">
                <label class="remember-label">
                    <input type="checkbox" name="remember" class="remember-checkbox">
                    <span>Remember me</span>
                </label>

                <a href="#" class="link">Forgot password?</a>
            </div>

            <button class="btn" type="submit">
                Sign in
            </button>
        </form>

<!-- Security note removed (avoid extra decorative “...” designs) -->
<div class="security-note">
</div>

        <p style="text-align:center; margin-top:18px; font-size:13px; color:#64748b;">
            Don’t have an account?
            <a href="{{ route('register') }}" class="link">Create account</a>
        </p>

    </div>

</div>

@endsection