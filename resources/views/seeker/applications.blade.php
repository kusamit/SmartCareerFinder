@extends('layouts.app')
@section('title', 'My Applications')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<style>
    body { background: #f1f4f9 !important; }

    /* ===== PAGE HEADER ===== */
    .page-header { margin-bottom: 28px; }
    .page-title { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px; }
    .page-sub   { font-size: 13px; color: #64748b; margin-top: 3px; }

    /* ===== APPLICATION CARD ===== */
    .app-card {
        background: #fff; border: 1.5px solid #f1f5f9; border-radius: 20px;
        padding: 20px 24px; margin-bottom: 12px; display: flex; align-items: center; gap: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: all 0.2s ease;
    }
    .app-card:hover { border-color: #c7d2fe; box-shadow: 0 6px 20px rgba(99,102,241,0.1); transform: translateY(-1px); }

    .app-info { flex: 1; min-width: 0; }
    .job-link { font-size: 16px; font-weight: 800; color: #0f172a; text-decoration: none; transition: color 0.2s; }
    .job-link:hover { color: #4f46e5; }
    .company-meta { font-size: 13px; color: #64748b; margin-top: 3px; }
    .applied-date { font-size: 11.5px; color: #94a3b8; margin-top: 5px; }

    /* ===== SCORE COLUMN ===== */
    .score-col { text-align: center; flex-shrink: 0; padding: 0 16px; border-left: 1px solid #f1f5f9;
        cursor: pointer; border-radius: 12px; transition: background 0.2s, transform 0.15s; }
    .score-col:hover { background: #f8fafc; transform: scale(1.05); }
    .score-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 3px; }
    .score-val { font-size: 20px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
    .score-unmatch { font-size: 11px; font-weight: 700; color: #e11d48; }
    .score-sub-lbl { font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
    .mini-bar-wrap { height: 4px; background: #fee2e2; border-radius: 999px; overflow: hidden; margin: 5px auto; width: 56px; }
    .mini-bar-fill { height: 100%; border-radius: 999px; transition: width 0.5s ease; }
    .score-high .mini-bar-fill { background: linear-gradient(90deg, #10b981, #059669); }
    .score-mid  .mini-bar-fill { background: linear-gradient(90deg, #fbbf24, #d97706); }
    .score-low  .mini-bar-fill { background: linear-gradient(90deg, #94a3b8, #64748b); }

    .score-high { color: #059669; }
    .score-mid  { color: #d97706; }
    .score-low  { color: #64748b; }

    .score-hint {
        font-size: 9px; color: #c7d2fe; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em;
        margin-top: 4px; text-align: center;
    }

    /* ===== STATUS COLUMN ===== */
    .status-col { text-align: center; flex-shrink: 0; padding-left: 16px; border-left: 1px solid #f1f5f9; width: 130px; }

    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 700;
        text-transform: capitalize;
    }
    .status-applied { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .status-applied::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #0284c7; }

    .status-reviewed { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .status-reviewed::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #d97706; }

    .status-shortlisted { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-shortlisted::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #16a34a; }

    .status-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .status-rejected::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #dc2626; }

    .job-closed-tag {
        display: block; font-size: 10px; font-weight: 700; color: #dc2626;
        text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        background: #fff; border: 2px dashed #e2e8f0; border-radius: 20px;
        padding: 72px 24px; text-align: center;
    }
    .empty-state h3 { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .empty-state p  { font-size: 13px; color: #94a3b8; margin-bottom: 24px; }

    .btn-find {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 11px 20px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff !important; font-weight: 700; font-size: 14px;
        border-radius: 12px; border: none; cursor: pointer; text-decoration: none;
        transition: all 0.25s; box-shadow: 0 6px 20px rgba(79,70,229,0.3);
    }
    .btn-find:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,70,229,0.4); color: #fff; }
</style>
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="page-header">
        <div class="page-title">My Applications</div>
        <div class="page-sub">Track all your job applications in one place</div>
    </div>

    @forelse($applications as $app)
    @php
        $statusMap = [
            'applied'     => 'status-applied',
            'reviewed'    => 'status-reviewed',
            'shortlisted' => 'status-shortlisted',
            'rejected'    => 'status-rejected'
        ];
        $statusClass = $statusMap[$app->status] ?? 'bg-slate-100 text-slate-700';
        $score       = $app->match_score;
        $scoreClass  = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-mid' : 'score-low');
        $details     = $user->matchDetails($app->job, $score);
        $matchDataArr = [
            'name'             => $user->name,
            'job_title'        => $app->job->title,
            'score'            => $score,
            'matched_skills'   => array_values($details['matched_skills']),
            'unmatched_skills' => array_values($details['unmatched_skills']),
            'location_match'   => $details['location_match'],
            'role_match'       => $details['role_match'],
            'seeker_location'  => $details['seeker_location'],
            'job_location'     => $details['job_location'],
            'seeker_role'      => $details['seeker_role'],
            'exp_match'        => $details['exp_match'],
            'exp_message'      => $details['exp_message'],
            'portfolio_match'  => $details['portfolio_match'],
            'composite'        => $details['composite'],
        ];
    @endphp
    <div class="app-card">
        {{-- Info --}}
        <div class="app-info">
            <a href="{{ route('jobs.show', $app->job->id) }}" class="job-link">{{ $app->job->title }}</a>
            <div class="company-meta">{{ $app->job->company }} &nbsp;&middot;&nbsp; {{ $app->job->location }}</div>
            <div class="applied-date">Applied {{ $app->created_at->diffForHumans() }}</div>
        </div>

        {{-- Score (Clickable) --}}
        <div class="score-col {{ $scoreClass }}" data-match='@json($matchDataArr)' title="Click to view match details">
            <div class="score-sub-lbl">Match / Unmatched</div>
            <div style="display:flex; align-items:baseline; justify-content:center; gap:3px;">
                <span class="score-val">{{ $score }}%</span>
                <span class="score-unmatch">/ {{ 100 - $score }}%</span>
            </div>
            <div class="mini-bar-wrap">
                <div class="mini-bar-fill" style="width: {{ $score }}%"></div>
            </div>
            <div class="score-hint">View Details</div>
        </div>

        {{-- Status --}}
        <div class="status-col">
            <span class="badge-status {{ $statusClass }}">{{ $app->status }}</span>
            @if(!$app->job->isOpen())
                <span class="job-closed-tag">Closed</span>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">
        <h3>No applications yet</h3>
        <p>Start finding your dream job today.</p>
        <a href="{{ route('seeker.jobs') }}" class="btn-find">Find Jobs Now</a>
    </div>
    @endforelse

</div>
@endsection
