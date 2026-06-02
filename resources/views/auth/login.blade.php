@extends('layouts.app')
@section('title', 'Login')

@section('content')
<style>
    body {
        background: #f8fafc;
        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto;
    }

    .py-8
    {
        padding-top: 0px;
        padding-bottom: 0px;
    }

    /* subtle background */
    .bg-layer {
        position: fixed;
        inset: 0;
        /* background-image: radial-gradient(#e2e8f0 1px, transparent 1px); */
        background-size: 22px 22px;
        opacity: 0.6;
        pointer-events: none;
    }

    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px;
        padding-top: 0px;
        gap: 90px;
    }

    /* LEFT SIDE */
    .left-info {
        max-width: 480px;
    }

    .left-title {
        font-size: 44px;
        font-weight: 900;
        line-height: 1.1;
        color: #0f172a;
        margin-bottom: 16px;
        letter-spacing: -0.5px;
    }

    .left-title span {
        color: #4f46e5;
    }

    .left-desc {
        font-size: 15px;
        color: #64748b;
        line-height: 1.7;
        margin-bottom: 28px;
    }

    /* FEATURE DESIGN */
    .feature-grid {
        display: grid;
        gap: 14px;
    }

    .feature {
        display: flex;
        gap: 14px;
        padding: 18px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .feature::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #4f46e5, #818cf8);
    }

    .feature:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border-color: #c7d2fe;
    }

    .icon {
        width: 10px;
        height: 10px;
        margin-top: 6px;
        border-radius: 50%;
        background: #4f46e5;
        box-shadow: 0 0 0 5px rgba(79,70,229,0.10);
        flex-shrink: 0;
    }

    .feature-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .feature-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.2px;
    }

    .feature-text {
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }

    /* LOGIN CARD */
    .auth-card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 36px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.08);
    }

    .title {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .subtitle {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 22px;
    }

    .label {
        font-size: 13px;
        color: #334155;
        margin-bottom: 6px;
        display: block;
        font-weight: 500;
    }

    .input {
        width: 100%;
        padding: 13px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: 0.2s;
    }

    .input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79,70,229,0.12);
        background: #fff;
    }

    .btn {
        width: 100%;
        padding: 13px;
        border-radius: 12px;
        background: #4f46e5;
        color: #fff;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: 0.25s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(79,70,229,0.2);
    }

    .link {
        color: #4f46e5;
        font-size: 13px;
        text-decoration: none;
        font-weight: 500;
    }

    .link:hover {
        text-decoration: underline;
    }

    .error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        padding: 12px;
        border-radius: 12px;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .security-note {
        margin-top: 18px;
        font-size: 12px;
        color: #94a3b8;
        text-align: center;
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .auth-wrapper {
            flex-direction: column;
            gap: 40px;
            padding: 30px;
        }

        .left-info {
            display: none;
        }
    }
</style>

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

        <!-- YOUR ORIGINAL CONTENT (NOT CHANGED) -->
        <div class="feature-grid">

            <div class="feature">
                <div class="icon"></div>
                <div class="feature-content">
                    <div class="feature-title">AI Job Matching</div>
                    <div class="feature-text">Smart recommendations based on your skills and Experience</div>
                </div>
            </div>

            <div class="feature">
                <div class="icon" style="background:#10b981; box-shadow:0 0 0 5px rgba(16,185,129,0.10);"></div>
                <div class="feature-content">
                    <div class="feature-title">Instant Applications</div>
                    <div class="feature-text">Apply in seconds with pre-filled profiles</div>
                </div>
            </div>

            <div class="feature">
                <div class="icon" style="background:#f59e0b; box-shadow:0 0 0 5px rgba(245,158,11,0.10);"></div>
                <div class="feature-content">
                    <div class="feature-title">Smart Search Engine</div>
                    <div class="feature-text">Recommendation based on User Profile Informations.</div>
                </div>
            </div>

            <div class="feature">
                <div class="icon" style="background:#ef4444; box-shadow:0 0 0 5px rgba(239,68,68,0.10);"></div>
                <div class="feature-content">
                    <div class="feature-title">CV based Job Search</div>
                    <div class="feature-text">Recommendation based on User CV.</div>
                </div>
            </div>

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

            <div style="display:flex; justify-content:space-between; align-items:center; margin:12px 0 18px;">
    <label style="font-size:13px; color:#64748b;">
                    <input type="checkbox" name="remember"> Remember me
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