@extends('layouts.app')
@section('title', 'Find Jobs')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<style>
    body { background: #f1f4f9 !important; }

    /* ===== PAGE HEADER ===== */
    .page-header-wrapper {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-bottom: 28px;
    }
    @media (max-width: 768px) { .page-header-wrapper { flex-direction: column; } }

    .page-title { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px; }
    .page-sub   { font-size: 13px; color: #64748b; margin-top: 2px; }

    /* ===== CV UPLOAD PANEL ===== */
    .cv-upload-card {
        background: #fff; border: 1.5px solid #f1f5f9; border-radius: 20px;
        padding: 16px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        min-w-[320px]; flex-shrink: 0;
    }
    @media (max-width: 768px) { .cv-upload-card { width: 100%; min-width: 0; } }

    .cv-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8; margin-bottom: 8px; }
    
    .file-input-wrapper { display: flex; gap: 8px; align-items: center; }
    .file-input {
        font-family: 'Sora', sans-serif; font-size: 12px; color: #64748b; flex: 1; min-width: 0;
    }
    .file-input::file-selector-button {
        background: #6366f1; color: #fff; font-weight: 600; font-size: 11px;
        border: none; border-radius: 8px; padding: 6px 12px; cursor: pointer; margin-right: 8px;
        transition: background 0.2s;
    }
    .file-input::file-selector-button:hover { background: #4f46e5; }

    .btn-find {
        padding: 7px 16px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff !important; font-size: 12px; font-weight: 700; border-radius: 9px;
        border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(99,102,241,0.2);
        transition: all 0.2s;
    }
    .btn-find:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,0.3); }

    /* ===== ALERT BANNER ===== */
    .banner-alert {
        background: #e0e7ff; border: 1.5px solid #c7d2fe; border-radius: 16px;
        padding: 12px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;
    }
    .banner-alert-text { font-size: 13px; font-weight: 600; color: #4338ca; }
    .banner-alert-link { font-size: 12px; font-weight: 700; color: #4f46e5; text-decoration: none; margin-left: auto; }
    .banner-alert-link:hover { text-decoration: underline; }

    /* ===== JOB CARD ===== */
    .job-card {
        background: #fff; border: 1.5px solid #f1f5f9; border-radius: 20px;
        padding: 24px; margin-bottom: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: all 0.25s ease; position: relative; overflow: hidden;
    }
    .job-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
        background: linear-gradient(180deg, #6366f1, #8b5cf6); opacity: 0; transition: opacity 0.2s;
    }
    .job-card:hover { border-color: #c7d2fe; box-shadow: 0 8px 28px rgba(99,102,241,0.12); transform: translateY(-2px); }
    .job-card:hover::before { opacity: 1; }

    .card-top { display: flex; align-items: flex-start; gap: 20px; }
    @media (max-width: 640px) { .card-top { flex-direction: column; } }

    .card-body { flex: 1; min-width: 0; }
    .card-side { flex-shrink: 0; text-align: right; width: 130px; }
    @media (max-width: 640px) { .card-side { width: 100%; text-align: left; margin-top: 14px; border-top: 1px solid #f1f5f9; pt-14: 14px; } }

    .job-title {
        font-size: 17px; font-weight: 800; color: #0f172a; text-decoration: none;
        transition: color 0.2s; display: block; margin-bottom: 6px;
    }
    .job-title:hover { color: #4f46e5; }

    .card-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-bottom: 8px; }
    .status-open {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 11px; border-radius: 999px; font-size: 11px; font-weight: 700;
        background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
    }
    .status-open::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #16a34a; display: inline-block; }
    .type-badge {
        display: inline-flex; padding: 3px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 600;
        background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; text-transform: capitalize;
    }

    .card-meta { font-size: 13px; color: #64748b; margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 4px 8px; }
    .card-meta-sep { color: #cbd5e1; }

    .card-desc { font-size: 13.5px; color: #64748b; line-height: 1.65; margin-bottom: 14px; }

    /* ===== SKILL TAGS ===== */
    .skill-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .skill-tag-item {
        padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;
        background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;
    }

    /* ===== MATCH SCORE ===== */
    .match-score-num { font-size: 30px; font-weight: 900; line-height: 1; }
    .match-score-lbl { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 2px; }
    .match-unmatch-pct { font-size: 12px; font-weight: 700; color: #e11d48; }
    .match-bar-wrap { height: 5px; background: #fee2e2; border-radius: 999px; overflow: hidden; margin: 8px 0 16px; }
    .match-bar-fill { height: 100%; border-radius: 999px; }

    .score-high { color: #059669; }
    .score-high .match-bar-fill { background: linear-gradient(90deg, #10b981, #059669); }
    .score-mid  { color: #d97706; }
    .score-mid .match-bar-fill  { background: linear-gradient(90deg, #fbbf24, #d97706); }
    .score-low  { color: #64748b; }
    .score-low .match-bar-fill  { background: linear-gradient(90deg, #94a3b8, #64748b); }

    /* ===== APPLY ACTION ===== */
    .btn-apply {
        display: inline-flex; align-items: center; justify-content: center; width: 100%;
        padding: 10px 16px; background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff !important; font-size: 13px; font-weight: 700; border-radius: 10px;
        border: none; cursor: pointer; text-decoration: none; box-shadow: 0 4px 12px rgba(79,70,229,0.2);
        transition: all 0.2s;
    }
    .btn-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79,70,229,0.3); }

    .status-badge-closed {
        display: inline-flex; width: 100%; justify-content: center; padding: 8px 12px;
        border-radius: 10px; font-size: 12px; font-weight: 700; background: #fee2e2; color: #b91c1c;
    }

    /* ===== CARD EXTRA INFO ===== */
    .card-extra { font-size: 11.5px; color: #94a3b8; border-top: 1px solid #f1f5f9; margin-top: 16px; padding-top: 12px; }

    /* ===== CLICKABLE SCORE SIDE ===== */
    .score-click-zone {
        cursor: pointer;
        border-radius: 10px;
        padding: 8px;
        margin: -8px -8px 6px;
        transition: background 0.2s, transform 0.15s;
    }
    .score-click-zone:hover { background: rgba(0,0,0,0.04); transform: scale(1.03); }
    .score-view-hint {
        font-size: 9px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.08em; text-align: center; margin-top: 4px;
        color: #c7d2fe;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        background: #fff; border: 2px dashed #e2e8f0; border-radius: 20px;
        padding: 72px 24px; text-align: center;
    }
    .empty-state h3 { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .empty-state p  { font-size: 13px; color: #94a3b8; }
</style>
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="page-header-wrapper">
        <div>
            <div class="page-title">Job Matches</div>
            <div class="page-sub">
                @if(session('cv_mode'))
                    Results matched to your uploaded CV
                @else
                    Results matched to your profile &middot; {{ $scored->count() }} jobs found
                @endif
            </div>
        </div>

        {{-- CV Upload Panel --}}
        <div class="cv-upload-card">
            <div class="cv-label">Upload CV to Match</div>
            <form method="POST" action="{{ route('seeker.jobs.cv') }}" enctype="multipart/form-data">
                @csrf
                <div class="file-input-wrapper">
                    <input type="file" name="cv" accept=".pdf,.doc,.docx,.txt" class="file-input" required>
                    <button type="submit" class="btn-find">Find</button>
                </div>
            </form>
            @error('cv')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ===== ALERT BANNER ===== --}}
    @if(session('cv_mode'))
    <div class="banner-alert">
        <span class="banner-alert-text">Showing results matched to your uploaded CV</span>
        <a href="{{ route('seeker.jobs') }}" class="banner-alert-link">Reset Filters →</a>
    </div>
    @endif

    {{-- ===== JOB LIST ===== --}}
    @forelse($scored as $job)
    @php
        $score      = $job->match_score;
        $scoreClass = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-mid' : 'score-low');
        $details    = $user->matchDetails($job, $score);
        $matchDataArr = [
            'name'             => $user->name,
            'job_title'        => $job->title,
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
    <div class="job-card">
        <div class="card-top">

            {{-- LEFT: Details --}}
            <div class="card-body">
                <a href="{{ route('jobs.show', $job->id) }}" class="job-title">{{ $job->title }}</a>

                <div class="card-badges">
                    @if($job->status === 'open')
                        <span class="status-open">Open</span>
                    @endif
                    <span class="type-badge">{{ $job->type }}</span>
                </div>

                <div class="card-meta">
                    <span>{{ $job->company }}</span>
                    <span class="card-meta-sep">&middot;</span>
                    <span>{{ $job->location }}</span>
                    @if($job->salary_range)
                        <span class="card-meta-sep">&middot;</span>
                        <span>{{ $job->salary_range }}</span>
                    @endif
                </div>

                <p class="card-desc">
                    {{ Str::limit(strip_tags($job->description), 140) }}
                </p>

                @if($job->key_skills)
                <div class="skill-tags">
                    @foreach($job->skillsArray() as $skill)
                    <span class="skill-tag-item">{{ $skill }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- RIGHT: Match Score & Action --}}
            <div class="card-side {{ $scoreClass }}">
                {{-- Clickable score zone --}}
                <div class="score-click-zone" data-match='@json($matchDataArr)' title="Click to see match breakdown">
                    <div style="display:flex; align-items:baseline; gap:4px; flex-wrap:wrap; justify-content:center;">
                        <span class="match-score-num">{{ $score }}%</span>
                        <span class="match-unmatch-pct">/ {{ 100 - $score }}%</span>
                    </div>
                    <div class="match-score-lbl" style="font-size:9px;">Match / Unmatched</div>
                    <div class="match-bar-wrap">
                        <div class="match-bar-fill" style="width: {{ $score }}%"></div>
                    </div>
                    <div class="score-view-hint">View Details</div>
                </div>

                @if($job->isOpen())
                <form method="POST" action="{{ route('seeker.jobs.apply', $job->id) }}">
                    @csrf
                    <button type="submit" class="btn-apply">Apply Now</button>
                </form>
                @else
                <span class="status-badge-closed">Closed</span>
                @endif
            </div>
        </div>

        {{-- Experience + View Details Button --}}
        <div class="card-extra" style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            @if($job->experience_required)
            <span>Experience required: {{ $job->experience_required }}</span>
            @endif

            <a href="{{ route('jobs.show', $job->id) }}"
               style="display:inline-flex; align-items:center; gap:5px; padding:5px 14px; background:#dcfce7; color:#15803d; font-weight:700; font-size:12px; border-radius:8px; border:1px solid #bbf7d0; text-decoration:none; transition:all 0.2s;"
               onmouseover="this.style.background='#16a34a'; this.style.color='#fff'; this.style.borderColor='#16a34a';"
               onmouseout="this.style.background='#dcfce7'; this.style.color='#15803d'; this.style.borderColor='#bbf7d0';">
               View Full Details &rarr;
            </a>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div style="font-size:40px; margin-bottom:12px;">🎉</div>
        <h3>You've applied to all available jobs!</h3>
        <p>No new open jobs to show right now. Check your <a href="{{ route('seeker.applications') }}" style="color:#6366f1; font-weight:700;">Applications</a> to track your progress, or check back later for new listings.</p>
    </div>
    @endforelse

</div>
@endsection
