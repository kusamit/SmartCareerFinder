@extends('layouts.app')
@section('title', 'Find Jobs')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seeker-jobs.css') }}">
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
        <a href="{{ route('seeker.jobs') }}" class="banner-alert-link">Reset Filters</a>
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

                {{-- ===== MORE JOB DETAILS ROW ===== --}}
                <div class="job-details-row">

                    {{-- Salary --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Salary</div>
                            <div class="detail-chip-value">{{ $job->salary_range ?: 'Not specified' }}</div>
                        </div>
                    </div>

                    {{-- Experience --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Experience</div>
                            <div class="detail-chip-value">{{ $job->experience_required ?: 'Open to all' }}</div>
                        </div>
                    </div>

                    {{-- Posted date --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Posted</div>
                            <div class="detail-chip-value">{{ $job->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Location</div>
                            <div class="detail-chip-value">{{ $job->location }}</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- RIGHT: Match Score & Action --}}
            <div class="card-side {{ $scoreClass }}">
                {{-- Clickable score zone --}}
                <div class="score-click-zone" data-match="{{ json_encode($matchDataArr) }}" title="Click to see match breakdown">
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

        {{-- View Details Button --}}
        <div class="card-extra" style="display:flex; align-items:center; justify-content:flex-end; gap:16px; flex-wrap:wrap;">
            <a href="{{ route('jobs.show', $job->id) }}"
               style="display:inline-flex; align-items:center; gap:5px; padding:5px 14px; background:#dcfce7; color:#15803d; font-weight:700; font-size:12px; border-radius:8px; border:1px solid #bbf7d0; text-decoration:none; transition:all 0.2s;"
               onmouseover="this.style.background='#16a34a'; this.style.color='#fff'; this.style.borderColor='#16a34a';"
               onmouseout="this.style.background='#dcfce7'; this.style.color='#15803d'; this.style.borderColor='#bbf7d0';">
               View Full Details
            </a>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div style="font-size:40px; margin-bottom:12px;"></div>
        <!-- <h3>You've applied to all available jobs!</h3> -->
        <p>No new open jobs to show right now. Check your <a href="{{ route('seeker.applications') }}" style="color:#6366f1; font-weight:700;">Applications</a> to track your progress, or check back later for new listings.</p>
    </div>
    @endforelse

</div>
@endsection
