<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Career Finder') - Smart Career</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    * {
        font-family: 'Sora', sans-serif;
    }

    .mono {
        font-family: 'JetBrains Mono', monospace;
    }

    /* ===== NAV LINK ===== */
    .nav-link {
        display: inline-flex;
        align-items: center;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 500;
        color: #334155;
        border-radius: 12px;
        transition: all 0.25s ease;
        position: relative;
        text-decoration: none;
    }

    .nav-link:hover {
        background: rgba(99, 102, 241, 0.10);
        color: #4f46e5;
        transform: translateY(-2px) scale(1.05);
    }

    .nav-link:active {
        transform: scale(0.95);
    }

    /* underline animation for ALL nav links */
    .nav-link::after {
        content: "";
        position: absolute;
        bottom: 6px;
        left: 25%;
        width: 0%;
        height: 2px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 50%;
    }

    /* ===== LOGIN BUTTON (make it match too) ===== */
    a[href*="login"] {
        display: inline-flex;
        align-items: center;
        padding: 10px 16px;
        border-radius: 12px;
        color: #334155;
        border: 1px solid rgba(99, 102, 241, 0.25);
        transition: all 0.25s ease;
    }

    a[href*="login"]:hover {
        background: rgba(99, 102, 241, 0.10);
        transform: translateY(-2px) scale(1.05);
        color: #4f46e5;
        border-color: rgba(99, 102, 241, 0.5);
    }

    a[href*="login"]:active {
        transform: scale(0.94);
    }

    /* ===== REGISTER BUTTON (already working, improved consistency) ===== */
    a[href*="register"] {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        border-radius: 14px;
        font-weight: 600;
        color: white !important;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
        transition: all 0.25s ease;
    }

    a[href*="register"]:hover {
        transform: translateY(-2px) scale(1.06);
        box-shadow: 0 14px 30px rgba(99, 102, 241, 0.35);
    }

    a[href*="register"]:active {
        transform: scale(0.94);
    }

    /* ===== LOGOUT BUTTON ===== */
    button.btn-outline {
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid rgba(99, 102, 241, 0.25);
        background: transparent;
        color: #334155;
        font-weight: 500;
        transition: all 0.25s ease;
        cursor: pointer;
    }

    button.btn-outline:hover {
        background: rgba(99, 102, 241, 0.10);
        transform: translateY(-2px) scale(1.05);
        color: #4f46e5;
        border-color: rgba(99, 102, 241, 0.5);
    }

    button.btn-outline:active {
        transform: scale(0.94);
    }

    /* ===== NAVBAR GLASS EFFECT ===== */
    nav {
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    /* ===== BRAND ICON ===== */
    .w-8.h-8 {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }
</style>
    @stack('styles')
</head>
<body class="min-h-screen" data-testid="app-root">

{{-- Navbar --}}
@hasSection('navbar')
@yield('navbar')
@else
<nav class="border-b border-indigo-600/20 bg-white/70 dark:bg-black/60 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">J</div>
                <span class="font-bold text-lg tracking-tight">Smart Career<span class="text-indigo-700">Finder</span></span>
            </div>
            <div class="flex items-center gap-1">
                @yield('nav_links')
                @if(session('user_id'))
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button class="btn-outline text-xs py-1.5 px-4">Logout</button>
                </form>
                @else
                <a href="{{ route('login') }}" class="nav-link">Login</a>
                <a href="{{ route('register') }}" class="btn-primary ml-2">Register</a>
                @endif
            </div>
        </div>
    </div>
</nav>
@endif

{{-- Flash messages --}}
@if(session('success') || session('error'))
<div class="max-w-7xl mx-auto px-4 pt-4">
    @if(session('success'))
    <div class="bg-emerald-900/50 border border-emerald-600/50 text-emerald-300 px-5 py-3 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-900/50 border border-red-600/50 text-red-300 px-5 py-3 rounded-xl text-sm">
        {{ session('error') }}
    </div>
    @endif
</div>
@endif

{{-- Main --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

<!-- <footer class="border-t border-slate-800 mt-16 py-8 text-center text-slate-600 text-xs">
    © {{ date('Y') }} JobBridge
</footer> -->

@stack('scripts')

<script>
  // Toggle dark mode: prefers system, can be changed by setting <html data-theme="dark">
  (function () {
    const root = document.documentElement;
    if (!root.getAttribute('data-theme')) {
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (prefersDark) root.setAttribute('data-theme', 'dark');
    }
  })();
</script>
</body>
</html>