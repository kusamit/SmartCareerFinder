<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Career Finder | Find Your Next Role</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="min-h-screen flex flex-col justify-between p-6 sm:p-8 lg:p-12">

    {{-- ===== HEADER NAV ===== --}}
    <header class="max-w-7xl w-full mx-auto mb-10 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="font-bold text-lg tracking-tight text-slate-800">Smart Career<span class="text-indigo-600">Finder</span></span>
        </div>
        
        <nav class="flex items-center gap-3">
            @if (Route::has('login'))
                @auth
                    @if(Auth::user()->role === 'provider')
                        <a href="{{ route('provider.dashboard') }}" class="btn-glow text-white text-xs font-bold py-2 px-5 rounded-xl">Dashboard</a>
                    @else
                        <a href="{{ route('seeker.dashboard') }}" class="btn-glow text-white text-xs font-bold py-2 px-5 rounded-xl">Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-indigo-600 text-xs font-semibold px-4 py-2 transition-colors">Log In</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-glow text-white text-xs font-bold py-2.5 px-5 rounded-xl">Register</a>
                    @endif
                @endauth
            @endif
        </nav>
    </header>

    {{-- ===== MAIN HERO ===== --}}
    <main class="max-w-7xl w-full mx-auto flex-1 flex flex-col gap-12 justify-center">
        
        <div class="hero-section px-8 py-16 sm:px-16 sm:py-24 text-center sm:text-left flex flex-col sm:flex-row items-center gap-12 justify-between">
            <div class="max-w-2xl relative z-10">
                <span class="inline-block bg-indigo-500/10 text-indigo-300 text-xs font-bold px-3.5 py-1.5 rounded-full border border-indigo-500/20 mb-6">
                    POWERED BY AI EMBEDDINGS
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight tracking-tight mb-6">
                    Find your dream job <br class="hidden sm:inline">with smart AI matching.
                </h1>
                <p class="text-slate-300 text-base sm:text-lg mb-8 leading-relaxed">
                    Upload your CV or list your skills, and let our semantic search engine align you with top opportunities instantly. No complex keyword hunting required.
                </p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4">
                    @auth
                        @if(Auth::user()->role === 'provider')
                            <a href="{{ route('provider.dashboard') }}" class="btn-glow text-white text-sm font-bold py-3.5 px-7 rounded-xl">Go to Dashboard</a>
                        @else
                            <a href="{{ route('seeker.dashboard') }}" class="btn-glow text-white text-sm font-bold py-3.5 px-7 rounded-xl">Find Jobs Now</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="btn-glow text-white text-sm font-bold py-3.5 px-7 rounded-xl">Get Started</a>
                        <a href="{{ route('login') }}" class="btn-outline-custom text-sm font-bold py-3.5 px-7 rounded-xl">Log In</a>
                    @endif
                </div>
            </div>
            
            {{-- Illustrative UI --}}
            <div class="w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md relative z-10 hidden lg:block">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
                <div class="h-4 bg-white/10 rounded-lg w-3/4 mb-4"></div>
                <div class="h-4 bg-white/5 rounded-lg w-1/2 mb-6"></div>
                <div class="space-y-3">
                    <div class="p-3 bg-white/5 rounded-xl border border-white/10 flex items-center justify-between">
                        <div class="h-3 bg-white/10 rounded w-1/3"></div>
                        <span class="text-xs font-bold text-emerald-400">92% Match</span>
                    </div>
                    <div class="p-3 bg-white/5 rounded-xl border border-white/10 flex items-center justify-between">
                        <div class="h-3 bg-white/10 rounded w-1/2"></div>
                        <span class="text-xs font-bold text-emerald-400">88% Match</span>
                    </div>
                    <div class="p-3 bg-white/5 rounded-xl border border-white/10 flex items-center justify-between">
                        <div class="h-3 bg-white/10 rounded w-1/4"></div>
                        <span class="text-xs font-bold text-amber-400">65% Match</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== FEATURES SECTION ===== --}}
        <div class="grid md:grid-cols-3 gap-6">
            <div class="feature-card p-8">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold mb-6">1</div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Upload or Type</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Upload your document or manually list your experience, skills, and qualifications.
                </p>
            </div>
            
            <div class="feature-card p-8">
                <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center text-xl font-bold mb-6">2</div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Semantic AI Scan</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Our platform converts job profiles and applications into vector embeddings for match scoring.
                </p>
            </div>

            <div class="feature-card p-8">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold mb-6">3</div>
                <h3 class="text-lg font-bold text-slate-800 mb-3">Instant Fit Score</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Identify your percentage fit and matching details immediately, then apply with one click.
                </p>
            </div>
        </div>

    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="max-w-7xl w-full mx-auto mt-16 pt-8 border-t border-slate-200 text-center text-slate-400 text-xs">
        &copy; {{ date('Y') }} Smart Career Finder. All rights reserved.
    </footer>

</body>
</html>
