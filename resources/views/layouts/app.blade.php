<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Career Finder')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

    /* ── Quill rich-text display — shared across all pages ── */
    .ql-editor-display { font-size: 14px; line-height: 1.75; word-break: break-word; }
    .ql-editor-display p { margin: 0 0 0.6em; }
    .ql-editor-display p:last-child { margin-bottom: 0; }
    .ql-editor-display ul, .ql-editor-display ol { padding-left: 1.6em; margin: 0.4em 0 0.8em; }
    .ql-editor-display ul { list-style-type: disc; }
    .ql-editor-display ol { list-style-type: decimal; }
    .ql-editor-display li { margin-bottom: 0.3em; }
    .ql-editor-display .ql-indent-1 { padding-left: 3em; }
    .ql-editor-display .ql-indent-2 { padding-left: 6em; }
    .ql-editor-display .ql-indent-3 { padding-left: 9em; }
    .ql-editor-display h1 { font-size: 1.5em; font-weight: 800; margin: 0.8em 0 0.4em; }
    .ql-editor-display h2 { font-size: 1.25em; font-weight: 700; margin: 0.7em 0 0.35em; }
    .ql-editor-display h3 { font-size: 1.1em; font-weight: 700; margin: 0.6em 0 0.3em; }
    .ql-editor-display strong, .ql-editor-display b { font-weight: 700; }
    .ql-editor-display em, .ql-editor-display i { font-style: italic; }
    .ql-editor-display u { text-decoration: underline; }
    .ql-editor-display s { text-decoration: line-through; }
    .ql-editor-display blockquote {
        border-left: 3px solid #6366f1; padding: 6px 14px;
        margin: 0.6em 0; background: rgba(99,102,241,0.05);
        border-radius: 0 8px 8px 0; font-style: italic; color: #475569;
    }
    .ql-editor-display a { color: #6366f1; text-decoration: underline; text-underline-offset: 2px; }
    .ql-editor-display a:hover { color: #4f46e5; }
    .ql-editor-display code { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-family: monospace; font-size: 0.9em; }
    .ql-editor-display pre { background: #0f172a; color: #e2e8f0; padding: 14px 18px; border-radius: 10px; overflow-x: auto; font-size: 13px; }
</style>
    @stack('styles')
</head>
<body class="min-h-screen" data-testid="app-root">

{{-- Navbar --}}
@hasSection('navbar')
@yield('navbar')
@else
@php
    $dashboardRoute = '/';
    if (session('user_role') === 'provider') {
        $dashboardRoute = route('provider.dashboard');
    } elseif (session('user_role') === 'seeker') {
        $dashboardRoute = route('seeker.dashboard');
    }
@endphp
<nav class="border-b border-indigo-600/20 bg-white/70 dark:bg-black/60 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ $dashboardRoute }}" class="flex items-center gap-2 text-slate-900 dark:text-white hover:opacity-90 transition-opacity no-underline">
                <span class="font-bold text-lg tracking-tight">Smart Career<span class="text-indigo-700">Finder</span></span>
            </a>
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
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 text-emerald-900 px-6 py-4 rounded-2xl shadow-sm flex items-start gap-3.5 transition-all duration-300">
        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">✓</span>
        <div class="flex-1">
            <h4 class="font-bold text-sm text-emerald-950 mb-0.5">Success</h4>
            <p class="text-xs text-emerald-800/90 leading-relaxed font-semibold m-0">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-gradient-to-r from-rose-50 to-red-50/80 border border-rose-200/80 text-rose-900 px-6 py-4 rounded-2xl shadow-sm flex items-start gap-3.5 transition-all duration-300">
        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">✕</span>
        <div class="flex-1">
            <h4 class="font-bold text-sm text-rose-950 mb-0.5">Error</h4>
            <p class="text-xs text-rose-800/90 leading-relaxed font-semibold m-0">{{ session('error') }}</p>
        </div>
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
{{-- ===== Composite Match Details Modal ===== --}}
<style>
  #matchDetailsModal { font-family: 'Sora', sans-serif; }
  .mdl-overlay { transition: opacity 0.3s; }
  .mdl-box { transition: transform 0.3s, opacity 0.3s; }

  /* Scoring breakdown rows */
  .score-row { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid #f1f5f9; }
  .score-row:last-child { border-bottom:none; }
  .score-row-label { flex:1; font-size:12px; font-weight:600; color:#475569; }
  .score-row-bar { width:72px; height:5px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
  .score-row-fill { height:100%; border-radius:999px; }
  .fill-emerald { background: linear-gradient(90deg,#10b981,#059669); }
  .fill-rose    { background: linear-gradient(90deg,#fb7185,#e11d48); }
  .fill-slate   { background: #cbd5e1; }
  .score-row-pts { font-size:11px; font-weight:700; min-width:50px; text-align:right; }
  .pts-match   { color:#059669; }
  .pts-miss    { color:#94a3b8; }
  .pts-partial { color:#d97706; }
  .score-row-badge { font-size:10px; font-weight:700; padding:1px 7px; border-radius:999px; }
  .badge-yes { background:#dcfce7; color:#15803d; }
  .badge-no  { background:#fee2e2; color:#b91c1c; }

  /* Modal scroll */
  #matchDetailsContainer { max-height:90vh; overflow-y:auto; }
</style>

<div id="matchDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-7 max-w-2xl w-full mx-4 shadow-2xl transform translate-y-4 scale-95 transition-all duration-300" id="matchDetailsContainer">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-5">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modalTitle">Match Analysis</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" id="modalSub">Candidate &amp; Job requirements alignment</p>
            </div>
            <button onclick="closeMatchModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold p-1">&times;</button>
        </div>

        {{-- Top: Matched / Unmatched score pills --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl p-4 flex flex-col justify-center">
                <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mono" id="modalScoreMatched">0%</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mt-1">Final Match Score</div>
            </div>
            <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-2xl p-4 flex flex-col justify-center">
                <div class="text-3xl font-extrabold text-rose-600 dark:text-rose-400 mono" id="modalScoreUnmatched">0%</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mt-1">Unmatched</div>
            </div>
        </div>

        {{-- ===== SCORING BREAKDOWN TABLE ===== --}}
        <div class="mb-6">
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3 pb-1 border-b border-slate-100">Scoring Breakdown (How the score is calculated)</div>
            <div id="scoreBreakdownRows">
                {{-- Rows injected by JS --}}
            </div>
            <div class="flex items-center justify-between pt-3 mt-1 border-t border-slate-200">
                <span class="text-sm font-bold text-slate-700">Total Score</span>
                <span class="text-lg font-extrabold text-slate-900 mono" id="modalTotalScore">0 / 100 pts</span>
            </div>
        </div>

        {{-- ===== Matched vs Unmatched Details ===== --}}
        <div class="grid md:grid-cols-2 gap-6 text-sm">
            {{-- Left: Matched --}}
            <div class="space-y-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 border-b border-emerald-100 dark:border-emerald-900/40 pb-1">Matched Details</div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block mb-2">Matching Skills</span>
                    <div class="flex flex-wrap gap-1.5" id="modalMatchedSkills"></div>
                </div>
                <div class="space-y-2 pt-1" id="modalMatchedRows"></div>
            </div>

            {{-- Right: Unmatched --}}
            <div class="space-y-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-rose-100 dark:border-rose-900/40 pb-1">Unmatched / Missing</div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block mb-2">Missing Required Skills</span>
                    <div class="flex flex-wrap gap-1.5" id="modalUnmatchedSkills"></div>
                </div>
                <div class="space-y-2 pt-1" id="modalUnmatchedRows"></div>
            </div>
        </div>

        {{-- ===== Recommendations Section ===== --}}
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800" id="modalRecommendationsSection">
            <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 border-b border-indigo-100 dark:border-indigo-900/40 pb-1 mb-3">
                Recommended Course Domains & Skills
            </div>
            <div id="modalRecommendations" class="space-y-3">
                {{-- Recommended courses will be injected here --}}
            </div>
        </div>

    </div>
</div>

{{-- ===== Application Status Tracking Modal ===== --}}
<style>
  #appStatusModal {
      position: fixed; inset: 0; z-index: 9999;
      display: flex; align-items: center; justify-content: center;
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(4px);
      opacity: 0; pointer-events: none;
      transition: opacity 0.3s;
  }
  #appStatusModal.open { opacity: 1; pointer-events: all; }
  #appStatusContainer {
      background: #fff; border: 1.5px solid #e2e8f0;
      border-radius: 28px; padding: 28px 28px 32px;
      max-width: 440px; width: 100%; margin: 0 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      transform: translateY(16px) scale(0.96);
      transition: transform 0.3s, opacity 0.3s;
      position: relative;
  }
  #appStatusModal.open #appStatusContainer { transform: translateY(0) scale(1); }

  .ast-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; }
  .ast-title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px; }
  .ast-sub { font-size: 12px; color: #64748b; margin: 0; }
  .ast-close { background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer; padding: 0 0 0 12px; line-height: 1; }
  .ast-close:hover { color: #0f172a; }

  /* Timeline */
  .ast-timeline { position: relative; padding-left: 36px; }
  .ast-timeline::before {
      content: ''; position: absolute; left: 15px; top: 16px; bottom: 16px;
      width: 2px; background: #e2e8f0; border-radius: 2px;
  }
  .ast-step { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px; position: relative; }
  .ast-step:last-child { margin-bottom: 0; }

  .ast-icon {
      position: absolute; left: -36px; top: 0;
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 14px; font-weight: 800; flex-shrink: 0;
      border: 2px solid #e2e8f0; background: #f8fafc; color: #94a3b8;
      transition: all 0.3s;
  }
  .ast-icon.active  { background: #4f46e5; border-color: #4f46e5; color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,0.35); }
  .ast-icon.success { background: #10b981; border-color: #10b981; color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,0.35); }
  .ast-icon.danger  { background: #ef4444; border-color: #ef4444; color: #fff; box-shadow: 0 4px 12px rgba(239,68,68,0.35); }

  .ast-step-title { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 3px; }
  .ast-step-desc  { font-size: 12px; color: #64748b; margin: 0; line-height: 1.5; }

  .ast-step.pending .ast-step-title { color: #94a3b8; }
  .ast-step.pending .ast-step-desc  { color: #cbd5e1; }

  /* Status summary badge at top */
  .ast-status-pill {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700;
      margin-bottom: 20px; text-transform: capitalize;
  }
  .ast-status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
  .ast-pill-applied     { background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; }
  .ast-pill-applied::before { background:#0284c7; }
  .ast-pill-reviewed    { background:#fef3c7; color:#b45309; border:1px solid #fde68a; }
  .ast-pill-reviewed::before { background:#d97706; }
  .ast-pill-shortlisted { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
  .ast-pill-shortlisted::before { background:#16a34a; }
  .ast-pill-rejected    { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; }
  .ast-pill-rejected::before { background:#dc2626; }
</style>

<div id="appStatusModal">
    <div id="appStatusContainer">
        <div class="ast-header">
            <div>
                <h3 class="ast-title">Application Tracker</h3>
                <p class="ast-sub" id="statusModalJob">Job Title &middot; Company</p>
            </div>
            <button class="ast-close" onclick="closeStatusModal()">&times;</button>
        </div>

        {{-- Current Status Pill --}}
        <div id="statusPillWrap"></div>

        {{-- Timeline --}}
        <div class="ast-timeline">

            {{-- Step 1: Applied --}}
            <div class="ast-step" id="step-applied">
                <div class="ast-icon" id="step-icon-applied">1</div>
                <div>
                    <p class="ast-step-title">Applied</p>
                    <p class="ast-step-desc">Your application was submitted successfully.</p>
                </div>
            </div>

            {{-- Step 2: Reviewed --}}
            <div class="ast-step" id="step-reviewed">
                <div class="ast-icon" id="step-icon-reviewed">2</div>
                <div>
                    <p class="ast-step-title">Reviewed</p>
                    <p class="ast-step-desc">The recruiter has viewed and assessed your profile.</p>
                </div>
            </div>

            {{-- Step 3: Decision --}}
            <div class="ast-step" id="step-decision">
                <div class="ast-icon" id="step-icon-decision">3</div>
                <div>
                    <p class="ast-step-title" id="step-title-decision">Decision</p>
                    <p class="ast-step-desc" id="step-desc-decision">Pending recruiter decision.</p>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
  (function () {
    const root = document.documentElement;
    if (!root.getAttribute('data-theme')) {
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (prefersDark) root.setAttribute('data-theme', 'dark');
    }
  })();

  // ─── Build one scoring breakdown row ──────────────────────────────────────
  function buildScoreRow(label, pts, max, matched) {
    const pct      = max > 0 ? Math.min(100, Math.round((pts / max) * 100)) : 0;
    const fillCls  = matched ? 'fill-emerald' : (pts > 0 ? 'fill-slate' : 'fill-rose');
    const ptsCls   = matched ? 'pts-match' : 'pts-miss';
    const badge    = matched
        ? `<span class="score-row-badge badge-yes">✓</span>`
        : `<span class="score-row-badge badge-no">✗</span>`;

    return `<div class="score-row">
        <span class="score-row-label">${label}</span>
        <div class="score-row-bar"><div class="score-row-fill ${fillCls}" style="width:${pct}%"></div></div>
        <span class="score-row-pts ${ptsCls}">${pts} / ${max} pts</span>
        ${badge}
    </div>`;
  }

  // ─── Build a matched / unmatched detail row ────────────────────────────────
  function matchRow(text, color) {
    const dotColor = color === 'emerald' ? '#10b981'
                   : color === 'amber'   ? '#f59e0b'
                   : '#e11d48';
    return `<div style="display:flex; align-items:flex-start; gap:8px; font-size:12.5px; line-height:1.5; color:#475569; margin-bottom:8px;">
        <span style="width:6px; height:6px; border-radius:50%; background:${dotColor}; flex-shrink:0; margin-top:6px;"></span>
        <span style="flex:1;">${text}</span>
    </div>`;
  }

  // ─── Open modal ───────────────────────────────────────────────────────────
  function openMatchModal(data) {
      const modal     = document.getElementById('matchDetailsModal');
      const container = document.getElementById('matchDetailsContainer');

      // Header
      document.getElementById('modalTitle').innerText = data.name
          ? `${data.name}'s Match Profile` : `Job Match Profile`;
      document.getElementById('modalSub').innerText = data.job_title
          ? `Aligned with: ${data.job_title}` : 'Candidate & Job alignment';

      // ── Scoring Breakdown ─────────────────────────────────────────────────
      const c = data.composite || {};
      const faissW   = c.faiss_weighted  ?? 0;
      const faissMax = c.faiss_max       ?? 70;
      const locPts   = c.location_pts    ?? 0;
      const locMax   = c.location_max    ?? 10;
      const portPts  = c.portfolio_pts   ?? 0;
      const portMax  = c.portfolio_max   ?? 10;
      const domPts   = c.domain_pts      ?? 0;
      const domMax   = c.domain_max      ?? 10;

      const locMatch  = c.location_match  ?? data.location_match  ?? false;
      const portMatch = c.portfolio_match ?? data.portfolio_match ?? false;
      const domMatch  = c.domain_match    ?? data.role_match      ?? false;
      // Which specific preferred role triggered the domain match (new multi-role support)
      const domMatchedRole = c.domain_matched_role ?? data.role_matched_role ?? null;

      // Compute actual total from breakdown components (fixes stale DB score showing 0)
      const actualTotal = Math.min(100, Math.round(faissW + locPts + portPts + domPts));

      // Update score pills using the real computed score
      document.getElementById('modalScoreMatched').innerText   = `${actualTotal}%`;
      document.getElementById('modalScoreUnmatched').innerText = `${100 - actualTotal}%`;

      let rows = '';
      rows += buildScoreRow('AI Semantic Score (FAISS)',  faissW, faissMax, faissW >= faissMax * 0.5);
      rows += buildScoreRow('Location Match',             locPts,  locMax,  locMatch);
      rows += buildScoreRow('Portfolio / Projects',        portPts, portMax, portMatch);
      rows += buildScoreRow('Preferred Job Domain',        domPts,  domMax,  domMatch);

      document.getElementById('scoreBreakdownRows').innerHTML = rows;
      document.getElementById('modalTotalScore').innerText    = `${actualTotal} / 100 pts`;

      // ── Matched Skills ────────────────────────────────────────────────────
      const matchedCont = document.getElementById('modalMatchedSkills');
      matchedCont.innerHTML = '';
      if (data.matched_skills && data.matched_skills.length) {
          data.matched_skills.forEach(s => {
              matchedCont.innerHTML += `<span style="display:inline-flex;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:600;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;">${s}</span>`;
          });
      } else {
          matchedCont.innerHTML = `<span style="font-size:11px;color:#94a3b8;font-style:italic;">No matching skills found</span>`;
      }

      // ── Unmatched Skills ──────────────────────────────────────────────────
      const unmatchedCont = document.getElementById('modalUnmatchedSkills');
      unmatchedCont.innerHTML = '';
      if (data.unmatched_skills && data.unmatched_skills.length) {
          data.unmatched_skills.forEach(s => {
              unmatchedCont.innerHTML += `<span style="display:inline-flex;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:600;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">${s}</span>`;
          });
      } else {
          unmatchedCont.innerHTML = `<span style="font-size:11px;color:#94a3b8;font-style:italic;">No missing skills</span>`;
      }

      // ── Matched detail rows (left) ─────────────────────────────────────────
      let matchedRows = '';
      if (locMatch) {
          const seekerCity = (data.seeker_location || '').trim();
          const jobCity    = (data.job_location    || '').trim();
          matchedRows += matchRow(`City match: Seeker (<strong>${seekerCity}</strong>) ↔ Job (<strong>${jobCity}</strong>)`, 'emerald');
      }
      if (portMatch) matchedRows += matchRow(`Portfolio / Projects confirmed (CV uploaded or linked)`, 'emerald');
      if (domMatch) {
          // Show the specific role that matched (e.g. "Frontend Developer" not the full list)
          const matchedRoleLabel = domMatchedRole || data.seeker_role || '';
          matchedRows += matchRow(`Job domain aligns with preferred role: <strong>${matchedRoleLabel}</strong>`, 'emerald');
      }
      if (data.exp_match !== false) matchedRows += matchRow(data.exp_message || 'Experience requirement met', 'emerald');
      if (!matchedRows) matchedRows = `<span style="font-size:11px;color:#94a3b8;font-style:italic;">No additional matches</span>`;
      document.getElementById('modalMatchedRows').innerHTML = matchedRows;

      // ── Unmatched detail rows (right) ──────────────────────────────────────
      const JOB_TYPE_WORDS = ['remote', 'hybrid', 'onsite', 'on-site', 'work from home', 'wfh', 'anywhere', 'nationwide'];
      let unmatchedRows = '';
      if (!locMatch) {
          const jobLocLower = (data.job_location || '').toLowerCase().trim();
          const isJobTypeWord = JOB_TYPE_WORDS.some(w => jobLocLower === w || jobLocLower.startsWith(w));
          if (isJobTypeWord) {
              unmatchedRows += matchRow(`No city specified for this job (<strong>${data.job_location || 'N/A'}</strong> is a work type, not a city) — location bonus not applicable`, 'amber');
          } else {
              unmatchedRows += matchRow(`City mismatch — Job: <strong>${data.job_location || 'N/A'}</strong>, Seeker: <strong>${data.seeker_location || 'N/A'}</strong>`, 'rose');
          }
      }
      if (!portMatch) unmatchedRows += matchRow(`No portfolio/projects found — upload a CV or add GitHub/portfolio link`, 'rose');
      if (!domMatch) {
          // Show all preferred roles when none matched
          const allRoles = (data.seeker_roles && data.seeker_roles.length)
              ? data.seeker_roles.join(', ')
              : (data.seeker_role || 'N/A');
          unmatchedRows += matchRow(`Job domain doesn't match any preferred role — Preferred: <strong>${allRoles}</strong>`, 'rose');
      }
      if (data.exp_match === false) unmatchedRows += matchRow(data.exp_message || 'Experience requirement not met', 'rose');
      if (!unmatchedRows) unmatchedRows = `<span style="font-size:11px;color:#94a3b8;font-style:italic;">No gaps found</span>`;
      document.getElementById('modalUnmatchedRows').innerHTML = unmatchedRows;

      // ── Recommendations ───────────────────────────────────────────────────
      const recsSec = document.getElementById('modalRecommendationsSection');
      const recsCont = document.getElementById('modalRecommendations');
      if (data.unmatched_skills && data.unmatched_skills.length) {
          recsSec.style.display = 'block';
          const recs = getRecommendationsJS(data.unmatched_skills);
          let html = '';
          recs.forEach(r => {
              const skillsList = r.skills.map(s => `<span style="display:inline-flex;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:600;background:#eeebff;color:#4f46e5;border:1px solid #d5cdff;">${s}</span>`).join(' ');
              html += `
                  <div style="padding: 10px; border-radius: 12px; background: #f8fafc; border: 1px solid #f1f5f9; margin-bottom: 8px;">
                      <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em; margin-bottom: 4px;">${r.category}</div>
                      <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">${r.course}</div>
                      <div style="display: flex; flex-wrap: wrap; gap: 6px;">${skillsList}</div>
                  </div>
              `;
          });
          recsCont.innerHTML = html;
      } else {
          recsSec.style.display = 'none';
      }

      // Show modal
      modal.classList.remove('opacity-0', 'pointer-events-none');
      container.classList.remove('translate-y-4', 'scale-95');
  }

  function closeMatchModal() {
      const modal     = document.getElementById('matchDetailsModal');
      const container = document.getElementById('matchDetailsContainer');
      modal.classList.add('opacity-0', 'pointer-events-none');
      container.classList.add('translate-y-4', 'scale-95');
  }

  // ─── Status Modal Helpers ────────────────────────────────────────────────
  function openStatusModal(data) {
      const modal = document.getElementById('appStatusModal');

      // Set job and company header
      document.getElementById('statusModalJob').innerHTML =
          `<strong>${data.job_title}</strong> &middot; ${data.company}`;

      const status = (data.status || 'applied').toLowerCase();

      // Status pill at top
      const pillClasses = {
          applied:     'ast-pill-applied',
          reviewed:    'ast-pill-reviewed',
          shortlisted: 'ast-pill-shortlisted',
          rejected:    'ast-pill-rejected'
      };
      document.getElementById('statusPillWrap').innerHTML =
          `<div class="ast-status-pill ${pillClasses[status] || 'ast-pill-applied'}">
              Current Status: ${status}
           </div>`;

      // Helper to set icon state
      const setIcon = (iconId, state, label) => {
          const el = document.getElementById(iconId);
          el.className = `ast-icon ${state}`;
          el.innerHTML = label;
      };

      const setStep = (stepId, state) => {
          document.getElementById(stepId).className = `ast-step ${state === 'pending' ? 'pending' : ''}`;
      };

      // Reset decision text
      document.getElementById('step-title-decision').innerText = 'Decision';
      document.getElementById('step-desc-decision').innerText  = 'Pending recruiter decision.';

      if (status === 'applied') {
          setIcon('step-icon-applied',  'active',  '●');  setStep('step-applied',  '');
          setIcon('step-icon-reviewed', '',        '2');   setStep('step-reviewed', 'pending');
          setIcon('step-icon-decision', '',        '3');   setStep('step-decision', 'pending');

      } else if (status === 'reviewed') {
          setIcon('step-icon-applied',  'success', '✓');  setStep('step-applied',  '');
          setIcon('step-icon-reviewed', 'active',  '●');  setStep('step-reviewed', '');
          setIcon('step-icon-decision', '',        '3');   setStep('step-decision', 'pending');

      } else if (status === 'shortlisted') {
          setIcon('step-icon-applied',  'success', '✓');  setStep('step-applied',  '');
          setIcon('step-icon-reviewed', 'success', '✓');  setStep('step-reviewed', '');
          setIcon('step-icon-decision', 'success', '✓');  setStep('step-decision', '');
          document.getElementById('step-title-decision').innerText = 'Shortlisted';
          document.getElementById('step-desc-decision').innerText  = 'Congratulations! You have been shortlisted for interviews.';

      } else if (status === 'rejected') {
          setIcon('step-icon-applied',  'success', '✓');  setStep('step-applied',  '');
          setIcon('step-icon-reviewed', 'success', '✓');  setStep('step-reviewed', '');
          setIcon('step-icon-decision', 'danger',  '✗');  setStep('step-decision', '');
          document.getElementById('step-title-decision').innerText = 'Rejected';
          document.getElementById('step-desc-decision').innerText  = 'We regret to inform you that you were not selected for this role.';
      }

      modal.classList.add('open');
  }

  function closeStatusModal() {
      document.getElementById('appStatusModal').classList.remove('open');
  }

  // ─── Popover Hover/Click recommendations helper functions ────────────────
  const skillToCategory = {
      // Frontend
      'html5': 'Frontend Development',
      'css3': 'Frontend Development',
      'html': 'Frontend Development',
      'css': 'Frontend Development',
      'javascript': 'Frontend Development',
      'javascript (es6+)': 'Frontend Development',
      'es6': 'Frontend Development',
      'react.js': 'Frontend Development',
      'react': 'Frontend Development',
      'vue': 'Frontend Development',
      'vue.js': 'Frontend Development',
      'angular': 'Frontend Development',
      'tailwind': 'Frontend Development',
      'tailwind css': 'Frontend Development',
      'bootstrap': 'Frontend Development',
      'typescript': 'Frontend Development',
      'basic typescript': 'Frontend Development',
      'nextjs': 'Frontend Development',
      'next.js': 'Frontend Development',
      'basic next.js': 'Frontend Development',
      'sass': 'Frontend Development',
      'jquery': 'Frontend Development',
      'redux': 'Frontend Development',
      'redux toolkit': 'Frontend Development',
      'redux toolkit / zustand': 'Frontend Development',
      'zustand': 'Frontend Development',
      'material ui': 'Frontend Development',
      'material ui / shadcn ui': 'Frontend Development',
      'shadcn ui': 'Frontend Development',
      'react query': 'Frontend Development',
      'react query / tanstack query': 'Frontend Development',
      'tanstack query': 'Frontend Development',
      'webpack': 'Frontend Development',
      'vite': 'Frontend Development',
      'webpack / vite': 'Frontend Development',

      // Backend
      'php': 'Backend Development',
      'laravel': 'Backend Development',
      'python': 'Backend Development',
      'django': 'Backend Development',
      'flask': 'Backend Development',
      'node': 'Backend Development',
      'node.js': 'Backend Development',
      'java': 'Backend Development',
      'spring': 'Backend Development',
      'spring boot': 'Backend Development',
      'c#': 'Backend Development',
      'c++': 'Backend Development',
      'ruby': 'Backend Development',
      'rails': 'Backend Development',
      'ruby on rails': 'Backend Development',
      'rest': 'Backend Development',
      'api': 'Backend Development',
      'apis': 'Backend Development',
      'rest api': 'Backend Development',
      'rest apis': 'Backend Development',
      'graphql': 'Backend Development',
      'sql': 'Backend Development',
      'mysql': 'Backend Development',
      'postgresql': 'Backend Development',
      'mongodb': 'Backend Development',
      'nosql': 'Backend Development',

      // DevOps & Cloud
      'docker': 'DevOps & Infrastructure',
      'kubernetes': 'DevOps & Infrastructure',
      'aws': 'DevOps & Infrastructure',
      'gcp': 'DevOps & Infrastructure',
      'azure': 'DevOps & Infrastructure',
      'cloud': 'DevOps & Infrastructure',
      'ci/cd': 'DevOps & Infrastructure',
      'ci/cd fundamentals': 'DevOps & Infrastructure',
      'jenkins': 'DevOps & Infrastructure',
      'ansible': 'DevOps & Infrastructure',
      'terraform': 'DevOps & Infrastructure',
      'vagrant': 'DevOps & Infrastructure',
      'nginx': 'DevOps & Infrastructure',
      'apache': 'DevOps & Infrastructure',
      'git': 'DevOps & Infrastructure',
      'git & github': 'DevOps & Infrastructure',
      'github': 'DevOps & Infrastructure',
      'gitlab': 'DevOps & Infrastructure',
      'bash': 'DevOps & Infrastructure',
      'linux': 'DevOps & Infrastructure',

      // Data Science & ML
      'machine learning': 'Data Science & Machine Learning',
      'data science': 'Data Science & Machine Learning',
      'data analysis': 'Data Science & Machine Learning',
      'pandas': 'Data Science & Machine Learning',
      'numpy': 'Data Science & Machine Learning',
      'tensorflow': 'Data Science & Machine Learning',
      'pytorch': 'Data Science & Machine Learning',
      'nlp': 'Data Science & Machine Learning',
      'deep learning': 'Data Science & Machine Learning',
      'scikit-learn': 'Data Science & Machine Learning',
      'keras': 'Data Science & Machine Learning',
      'tableau': 'Data Science & Machine Learning',
      'power bi': 'Data Science & Machine Learning',
      'excel': 'Data Science & Machine Learning',
      'sheets': 'Data Science & Machine Learning',
      'matplotlib': 'Data Science & Machine Learning',
      'seaborn': 'Data Science & Machine Learning',
      'statistics': 'Data Science & Machine Learning',

      // Design & Marketing
      'ui/ux': 'Design & UX',
      'ui': 'Design & UX',
      'ux': 'Design & UX',
      'figma': 'Design & UX',
      'photoshop': 'Design & UX',
      'illustrator': 'Design & UX',
      'graphic design': 'Design & UX',
      'wordpress': 'Design & UX',
      'content writing': 'Design & UX',
      'digital marketing': 'Design & UX',
      'marketing': 'Design & UX',
      'seo': 'Design & UX',
      'sem': 'Design & UX',
      'social media': 'Design & UX',

      // PM & Methodologies
      'agile/scrum methodology': 'Agile & Project Management',
      'agile': 'Agile & Project Management',
      'scrum': 'Agile & Project Management',
      'agile/scrum': 'Agile & Project Management',
      'project management': 'Agile & Project Management',
      'communication skills': 'Agile & Project Management',
      'problem solving': 'Agile & Project Management',
      'attention to detail': 'Agile & Project Management',
  };

  function getRecommendedCourse(category, skills) {
      const skillsStr = skills.join(', ');
      switch (category) {
          case 'Frontend Development':
              return `Complete Front-end Development Course (covers: ${skillsStr})`;
          case 'Backend Development':
              return `Advanced Backend Engineering Path (covers: ${skillsStr})`;
          case 'DevOps & Infrastructure':
              return `DevOps, Git & CI/CD Masterclass (covers: ${skillsStr})`;
          case 'Data Science & Machine Learning':
              return `Data Science & AI/ML Bootcamp (covers: ${skillsStr})`;
          case 'Design & UX':
              return `UI/UX Design & Digital Marketing Essentials (covers: ${skillsStr})`;
          case 'Agile & Project Management':
              return `Agile, Scrum & Leadership Certification (covers: ${skillsStr})`;
          default:
              return `Specialized Professional Skill Building (covers: ${skillsStr})`;
      }
  }

  function getRecommendationsJS(unmatchedSkills) {
      const categories = {};
      unmatchedSkills.forEach(skill => {
          const normalizedSkill = skill.toLowerCase().trim();
          let matchedCategory = 'Other Professional Skills';

          for (const [key, category] of Object.entries(skillToCategory)) {
              if (normalizedSkill === key || normalizedSkill.includes(key) || key.includes(normalizedSkill)) {
                  matchedCategory = category;
                  break;
              }
          }

          if (!categories[matchedCategory]) {
              categories[matchedCategory] = [];
          }
          categories[matchedCategory].push(skill);
      });

      const result = [];
      for (const [catName, skills] of Object.entries(categories)) {
          result.push({
              category: catName,
              skills: skills,
              course: getRecommendedCourse(catName, skills)
          });
      }
      return result;
  }

  let popoverPinned = false;
  let currentPillEl = null;

  function showRecPopover(el, pin = false) {
      const popover = document.getElementById('recPopover');
      const closeBtn = document.getElementById('recPopoverClose');
      if (!popover) return;

      if (popoverPinned && !pin) return; // Don't override if pinned

      currentPillEl = el;

      if (pin) {
          popoverPinned = true;
          // Pinned mode: full scrollable height
          document.getElementById('recPopoverContent').style.maxHeight = '380px';
          document.getElementById('recPopoverContent').style.overflowY = 'auto';
          if (closeBtn) closeBtn.style.display = 'flex';
      } else {
          popoverPinned = false;
          // Hover preview
          document.getElementById('recPopoverContent').style.maxHeight = '320px';
          document.getElementById('recPopoverContent').style.overflowY = 'auto';
          if (closeBtn) closeBtn.style.display = 'none';
      }

      const recs = JSON.parse(el.getAttribute('data-recs') || '[]');
      let html = '';
      recs.forEach(r => {
          const skillsList = r.skills.map(s =>
              `<span style="display:inline-flex;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:none;color:#0f172a;border:none;margin-right:5px;margin-bottom:5px;">${s}</span>`
          ).join('');
          html += `<div style="padding-bottom:10px;border-bottom:1px solid #f1f5f9;margin-bottom:10px;">
              <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#94a3b8;letter-spacing:0.07em;margin-bottom:6px;">${r.category}</div>
              <div style="display:flex;flex-wrap:wrap;">${skillsList}</div>
          </div>`;
      });
      document.getElementById('recPopoverContent').innerHTML = html;

      // Position: right of pill, pushed upward to align near top of the section
      const rect = el.getBoundingClientRect();
      const popW = 320;
      const popH = pin ? 420 : 360;
      let left = rect.right + 12;
      // Push top upward — align near top of page (below navbar)
      let top = rect.top - 260;
      // Clamp: minimum 60px from top (below navbar), not off-screen bottom
      if (top < 60) top = 60;
      if (top + popH > window.innerHeight - 10) {
          top = window.innerHeight - popH - 10;
      }
      // Fallback: if not enough room to the right, go below the pill instead
      if (rect.right + popW + 16 > window.innerWidth) {
          top = rect.bottom + 6;
          left = Math.min(window.innerWidth - popW - 10, Math.max(10, rect.left));
      }
      popover.style.top  = `${top}px`;
      popover.style.left = `${left}px`;
      popover.style.display = 'block';
      popover.style.pointerEvents = 'auto';
      setTimeout(() => { popover.style.opacity = '1'; }, 10);
  }

  let recHideTimer = null;

  function scheduleHideRecPopover() {
      recHideTimer = setTimeout(() => {
          const popover = document.getElementById('recPopover');
          if (!popover || popoverPinned) return;
          popover.style.opacity = '0';
          popover.style.pointerEvents = 'none';
          const closeBtn = document.getElementById('recPopoverClose');
          if (closeBtn) closeBtn.style.display = 'none';
          setTimeout(() => {
              if (popover.style.opacity === '0') {
                  popover.style.display = 'none';
              }
          }, 200);
      }, 120);
  }

  function closeRecPopoverPinned() {
      popoverPinned = false;
      currentPillEl = null;
      const popover = document.getElementById('recPopover');
      const closeBtn = document.getElementById('recPopoverClose');
      if (!popover) return;
      popover.style.opacity = '0';
      popover.style.pointerEvents = 'none';
      if (closeBtn) closeBtn.style.display = 'none';
      setTimeout(() => { popover.style.display = 'none'; }, 200);
  }

  function cancelHideRecPopover() {
      if (recHideTimer) { clearTimeout(recHideTimer); recHideTimer = null; }
  }

  // Pill hover — show on enter, schedule hide on leave
  document.addEventListener('mouseover', function(e) {
      const pill = e.target.closest('.recommendation-pill');
      if (pill) { cancelHideRecPopover(); showRecPopover(pill); }
  });

  document.addEventListener('mouseout', function(e) {
      const pill = e.target.closest('.recommendation-pill');
      if (pill) { scheduleHideRecPopover(); }
  });

  // Popover hover — keep open while mouse is inside
  document.addEventListener('DOMContentLoaded', function() {
      const recPopoverEl = document.getElementById('recPopover');
      if (recPopoverEl) {
          recPopoverEl.addEventListener('mouseenter', cancelHideRecPopover);
          recPopoverEl.addEventListener('mouseleave', scheduleHideRecPopover);
      }
  });

  // Close on outside click; intercept [data-match], [data-app-status] and .recommendation-pill elements
  document.addEventListener('click', function(e) {
      const modal = document.getElementById('matchDetailsModal');
      if (e.target === modal) { closeMatchModal(); return; }

      const statusModal = document.getElementById('appStatusModal');
      if (e.target === statusModal) { closeStatusModal(); return; }

      const matchBtn = e.target.closest('[data-match]');
      if (matchBtn) {
          try {
              const data = JSON.parse(matchBtn.getAttribute('data-match'));
              openMatchModal(data);
          } catch(err) {
              console.error('Failed to parse match details:', err);
          }
      }

      const statusBtn = e.target.closest('[data-app-status]');
      if (statusBtn) {
          try {
              const data = JSON.parse(statusBtn.getAttribute('data-app-status'));
              openStatusModal(data);
          } catch(err) {
              console.error('Failed to parse status details:', err);
          }
      }

      const recPill = e.target.closest('.recommendation-pill');
      if (recPill) {
          showRecPopover(recPill, true);
          e.stopPropagation();
          return;
      }

      const popover = document.getElementById('recPopover');
      if (popover && !popover.contains(e.target)) {
          closeRecPopoverPinned();
      }
  });
</script>
@stack('scripts')
{{-- Popover for Hover/Click recommendations --}}
<div id="recPopover" style="position:fixed; width:320px; display:none; opacity:0; background:#fff; border:1.5px solid #e0e7ff; border-radius:14px; z-index:999999; box-shadow:0 12px 40px rgba(79,70,229,0.15); padding:0; pointer-events:none; transition:opacity 0.18s ease;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; padding:13px 16px 9px; border-bottom:1.5px solid #e0e7ff; flex-shrink:0;">
        <span style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.09em; color:#16a34a;">Skill Recommendations</span>
        <button id="recPopoverClose" onclick="closeRecPopoverPinned()" style="display:none; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; border:none; background:#ede9fe; color:#4f46e5; font-size:14px; cursor:pointer; line-height:1; font-weight:700; padding:0;" title="Close">&times;</button>
    </div>
    {{-- Scrollable content --}}
    <div id="recPopoverContent" style="padding:12px 16px 14px; display:flex; flex-direction:column; gap:0; max-height:320px; overflow-y:auto;"></div>
</div>
</body>
</html>