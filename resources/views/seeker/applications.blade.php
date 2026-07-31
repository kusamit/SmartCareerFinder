@extends('layouts.app')
@section('title', 'My Applications')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seeker-applications.css') }}">
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="page-header">
        <div class="page-title">My Applications</div>
        <div class="page-sub">Track all your job applications in one place</div>
    </div>

    {{-- ===== SEARCH BAR ===== --}}
    <div style="max-width: 360px; background:#fff; border: 1.5px solid #f1f5f9; border-radius: 16px; padding: 12px 18px; margin-bottom: 20px; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
        <div>
            <label style="font-size: 9.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:4px;">Search by Job Title</label>
            <input type="text" id="searchJobTitle" placeholder="Search by job title..." style="width:100%; border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 10px; font-size:12.5px; color:#1e293b; outline:none; transition:all 0.2s;" oninput="filterApplications()">
        </div>
    </div>

    {{-- ===== STATUS FILTER TABS ===== --}}
    @php
        $allCount        = \App\Models\Application::where('user_id', $user->id)->count();
        $filterCounts    = \App\Models\Application::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as cnt')->groupBy('status')
            ->pluck('cnt', 'status')->toArray();
        $filterTabs = [
            null          => ['label' => 'All',         'color' => '#6366f1', 'count' => $allCount],
            'applied'     => ['label' => 'Applied',     'color' => '#0284c7', 'count' => $filterCounts['applied']     ?? 0],
            'reviewed'    => ['label' => 'Reviewed',    'color' => '#d97706', 'count' => $filterCounts['reviewed']    ?? 0],
            'shortlisted' => ['label' => 'Shortlisted', 'color' => '#16a34a', 'count' => $filterCounts['shortlisted'] ?? 0],
            'rejected'    => ['label' => 'Rejected',    'color' => '#dc2626', 'count' => $filterCounts['rejected']    ?? 0],
        ];
    @endphp
    <div class="filter-bar">
        <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-right:4px;">Filter:</span>
        @foreach($filterTabs as $tabKey => $tab)
            @php
                $isActive = ($tabKey === null && $activeStatus === null) || ($tabKey !== null && $activeStatus === $tabKey);
                $url = $tabKey ? route('seeker.applications') . '?status=' . $tabKey : route('seeker.applications');
            @endphp
            <a href="{{ $url }}" class="filter-tab {{ $isActive ? 'active' : '' }}">
                <span class="filter-dot" style="background:{{ $tab['color'] }};"></span>
                {{ $tab['label'] }}
                <span class="count-badge">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Active filter banner --}}
    @if($activeStatus)
    <div class="filter-active-banner">
        <span>Showing <strong>{{ ucfirst($activeStatus) }}</strong> applications only</span>
        <a href="{{ route('seeker.applications') }}">✕ Clear filter</a>
    </div>
    @endif

    @forelse($applications as $app)
    @php
        $statusMap = [
            'applied'     => 'status-applied',
            'reviewed'    => 'status-reviewed',
            'shortlisted' => 'status-shortlisted',
            'rejected'     => 'status-rejected'
        ];
        $statusClass = $statusMap[$app->status] ?? 'bg-slate-100 text-slate-700';
        $live        = $liveScores[$app->id] ?? null;
        $score       = $live['score']     ?? $app->match_score;   // live-computed; fall back to stored
        $liveComp    = $live['composite'] ?? null;
        $scoreClass  = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-mid' : 'score-low');
        $details     = $user->matchDetails($app->job, $score);
        $finalComp   = $liveComp ?? $details['composite'];
        $matchDataArr = [
            'name'             => $user->name,
            'job_title'        => $app->job->title,
            'score'            => $score,
            'matched_skills'   => array_values($details['matched_skills']),
            'unmatched_skills' => array_values($details['unmatched_skills']),
            'location_match'   => $details['location_match'],
            'role_match'       => $details['role_match'],
            'role_matched_role'=> $details['role_matched_role'] ?? null,
            'seeker_roles'     => $details['seeker_roles'] ?? [],
            'seeker_location'  => $details['seeker_location'],
            'job_location'     => $details['job_location'],
            'seeker_role'      => $details['seeker_role'],
            'exp_match'        => $details['exp_match'],
            'exp_message'      => $details['exp_message'],
            'portfolio_match'  => $details['portfolio_match'],
            'composite'        => $finalComp,
        ];
        $statusData = [
            'status'    => $app->status,
            'job_title' => $app->job->title,
            'company'   => $app->job->company,
        ];

        $recs = \App\Services\Recommendation::categorizeSkills($details['unmatched_skills'] ?? [], $app->job->skillsArray());
        $recCategories = array_column($recs, 'category');
        $recText = implode(' & ', array_slice($recCategories, 0, 2));
    @endphp
    <div class="app-card" style="flex-wrap:wrap;">
        {{-- Info --}}
        <div class="app-info" style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 2px;">
                <a href="{{ route('jobs.show', $app->job->id) }}" class="job-link" style="margin-bottom: 0;">{{ $app->job->title }}</a>
                @if(!empty($recs))
                    <span class="recommendation-pill hover-popover" data-recs="{{ json_encode($recs) }}" style="display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; color:#16a34a; background:none; border:none; padding:2px 4px; cursor:pointer;" title="Hover or click to view skill recommendations">
                        Recommendation
                    </span>
                @endif
            </div>
            <div class="company-meta">{{ $app->job->company }} &nbsp;&middot;&nbsp; {{ $app->job->location }}</div>
            <div class="applied-date">Applied {{ $app->created_at->diffForHumans() }}</div>
        </div>

        {{-- Score (Clickable) --}}
        <div class="score-col {{ $scoreClass }}" data-match="{{ json_encode($matchDataArr) }}" title="Click to view match details">
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
            <button class="track-status-btn" data-app-status='@json($statusData)' title="Click to track your application status">
                <span class="badge-status {{ $statusClass }}">{{ $app->status }}</span>
                <span class="track-hint">Track</span>
            </button>
            @if(!$app->job->isOpen())
                <span class="job-closed-tag">Closed</span>
            @endif
        </div>

        {{-- Full-width: View Job Details Link --}}
        <div style="width:100%; border-top:1px solid #f1f5f9; padding-top:12px; margin-top:4px;">
            <a href="{{ route('jobs.show', $app->job->id) }}"
               style="display:inline-flex; align-items:center; gap:5px; padding:5px 14px; background:#dcfce7; color:#15803d; font-weight:700; font-size:12px; border-radius:8px; border:1px solid #bbf7d0; text-decoration:none; transition:all 0.2s;"
               onmouseover="this.style.background='#16a34a'; this.style.color='#fff'; this.style.borderColor='#16a34a';"
               onmouseout="this.style.background='#dcfce7'; this.style.color='#15803d'; this.style.borderColor='#bbf7d0';">
               View Full Details
            </a>
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

@push('scripts')
<script>
    function filterApplications() {
        const query = document.getElementById('searchJobTitle').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.app-card');

        cards.forEach(card => {
            const jobLink = card.querySelector('.job-link');
            const title = jobLink ? jobLink.textContent.toLowerCase().trim() : '';

            if (!query || title.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
