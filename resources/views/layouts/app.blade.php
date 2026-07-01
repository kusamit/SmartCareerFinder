<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Career Finder') - Smart Career</title>
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
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">J</div>
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
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 border-b border-emerald-100 dark:border-emerald-900/40 pb-1">✅ Matched Details</div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block mb-2">Matching Skills</span>
                    <div class="flex flex-wrap gap-1.5" id="modalMatchedSkills"></div>
                </div>
                <div class="space-y-2 pt-1" id="modalMatchedRows"></div>
            </div>

            {{-- Right: Unmatched --}}
            <div class="space-y-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-rose-100 dark:border-rose-900/40 pb-1">❌ Unmatched / Missing</div>
                <div>
                    <span class="text-xs font-semibold text-slate-500 block mb-2">Missing Required Skills</span>
                    <div class="flex flex-wrap gap-1.5" id="modalUnmatchedSkills"></div>
                </div>
                <div class="space-y-2 pt-1" id="modalUnmatchedRows"></div>
            </div>
        </div>

    </div>
</div>

@stack('scripts')

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
    const dotColor = color === 'emerald' ? '#10b981' : '#e11d48';
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

      // Top score pills
      const score = parseInt(data.score) || 0;
      document.getElementById('modalScoreMatched').innerText   = `${score}%`;
      document.getElementById('modalScoreUnmatched').innerText = `${100 - score}%`;

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

      let rows = '';
      rows += buildScoreRow('AI Semantic Score (FAISS)',  faissW, faissMax, faissW >= faissMax * 0.5);
      rows += buildScoreRow('Location Match',             locPts,  locMax,  locMatch);
      rows += buildScoreRow('Portfolio / Projects',        portPts, portMax, portMatch);
      rows += buildScoreRow('Preferred Job Domain',        domPts,  domMax,  domMatch);

      document.getElementById('scoreBreakdownRows').innerHTML = rows;
      document.getElementById('modalTotalScore').innerText    = `${score} / 100 pts`;

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
      if (locMatch)  matchedRows += matchRow(`Location matches: <strong>${data.job_location || ''}</strong>`, 'emerald');
      if (portMatch) matchedRows += matchRow(`Portfolio / Projects confirmed (CV uploaded or linked)`, 'emerald');
      if (domMatch)  matchedRows += matchRow(`Job domain aligns with preferred role: <strong>${data.seeker_role || ''}</strong>`, 'emerald');
      if (data.exp_match !== false) matchedRows += matchRow(data.exp_message || 'Experience requirement met', 'emerald');
      if (!matchedRows) matchedRows = `<span style="font-size:11px;color:#94a3b8;font-style:italic;">No additional matches</span>`;
      document.getElementById('modalMatchedRows').innerHTML = matchedRows;

      // ── Unmatched detail rows (right) ──────────────────────────────────────
      let unmatchedRows = '';
      if (!locMatch)  unmatchedRows += matchRow(`Location mismatch — Job: <strong>${data.job_location || 'N/A'}</strong>, Seeker: <strong>${data.seeker_location || 'N/A'}</strong>`, 'rose');
      if (!portMatch) unmatchedRows += matchRow(`No portfolio/projects found — upload a CV or add GitHub/portfolio link`, 'rose');
      if (!domMatch)  unmatchedRows += matchRow(`Job domain doesn't align — Preferred: <strong>${data.seeker_role || 'N/A'}</strong>`, 'rose');
      if (data.exp_match === false) unmatchedRows += matchRow(data.exp_message || 'Experience requirement not met', 'rose');
      if (!unmatchedRows) unmatchedRows = `<span style="font-size:11px;color:#94a3b8;font-style:italic;">No gaps found</span>`;
      document.getElementById('modalUnmatchedRows').innerHTML = unmatchedRows;

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

  // Close on outside click; intercept [data-match] elements
  document.addEventListener('click', function(e) {
      const modal = document.getElementById('matchDetailsModal');
      if (e.target === modal) { closeMatchModal(); return; }

      const matchBtn = e.target.closest('[data-match]');
      if (matchBtn) {
          try {
              const data = JSON.parse(matchBtn.getAttribute('data-match'));
              openMatchModal(data);
          } catch(err) {
              console.error('Failed to parse match details:', err);
          }
      }
  });
</script>
@stack('scripts')
</body>
</html>