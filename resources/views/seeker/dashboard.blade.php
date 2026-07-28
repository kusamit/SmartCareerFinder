@extends('layouts.app')
@section('title', 'Seeker Dashboard')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seeker-dashboard.css') }}">
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== HEADER ===== --}}
    <div class="page-header">
        <div class="header-left">
            <div>
                <div class="page-title">Hello, {{ $user->name }}</div>
                <div class="page-sub">Find jobs that match your skills</div>
            </div>
        </div>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="stats-grid">
        <a href="{{ route('seeker.jobs') }}" class="stat-card indigo">
            <div class="stat-label">Open Jobs</div>
            <div class="stat-value">{{ $totalJobs }}</div>
        </a>
        <a href="{{ route('seeker.applications') }}" class="stat-card emerald">
            <div class="stat-label">Applied Jobs</div>
            <div class="stat-value">{{ array_sum($statusCounts) }}</div>
        </a>
        <a href="{{ route('seeker.profile') }}" class="stat-card green">
            <div class="stat-label">Profile Completion</div>
            <div class="stat-value">{{ $profileCompleteness }}%</div>
        </a>
        <a href="{{ route('seeker.profile') }}" class="stat-card amber">
            <div class="stat-label">CV Status</div>
            <div class="stat-value" style="font-size: 26px; line-height: 1.35; padding-top: 2px;">{{ $user->cv_path ? 'Uploaded' : 'None' }}</div>
        </a>
    </div>

    {{-- ===== MAIN GRID ===== --}}
    <div class="dashboard-grid">

        {{-- LEFT: Applications --}}
        <div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h2 class="section-title" style="margin-bottom:0; font-size: 16px; font-weight: 700; color: #0f172a;">Recent Applications</h2>
                <a href="{{ route('seeker.applications') }}" style="font-size:13px; color:#6366f1; font-weight:600; text-decoration:none;">View all</a>
            </div>

            @forelse($applications as $app)
            @php
                $statusMap = [
                    'applied' => 'status-applied',
                    'reviewed' => 'status-reviewed',
                    'shortlisted' => 'status-shortlisted',
                    'rejected' => 'status-rejected'
                ];
                $statusClass = $statusMap[$app->status] ?? 'bg-slate-100 text-slate-700';

                $matchScore = $app->match_score;
                $matchClass = $matchScore >= 70 ? 'match-high' : ($matchScore >= 40 ? 'match-mid' : 'match-low');

                $statusData = [
                    'status'    => $app->status,
                    'job_title' => $app->job->title,
                    'company'   => $app->job->company,
                ];

                $details = $user->matchDetails($app->job, $matchScore);
                $finalComp = $details['composite'] ?? null;
                $matchDataArr = [
                    'name'             => $user->name,
                    'job_title'        => $app->job->title,
                    'score'            => $matchScore,
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

                $recs = [];
                $recText = '';
                if (!empty($details['unmatched_skills'])) {
                    $recs = \App\Services\Recommendation::categorizeSkills($details['unmatched_skills']);
                    $recCategories = array_column($recs, 'category');
                    $recText = implode(' & ', array_slice($recCategories, 0, 2));
                }
            @endphp
            <div class="app-row">
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 2px;">
                        <a href="{{ route('jobs.show', $app->job->id) }}" class="app-row-title" style="margin-bottom: 0;">{{ $app->job->title }}</a>
                        @if(!empty($recs))
                            <span class="recommendation-pill hover-popover" data-recs="{{ json_encode($recs) }}" style="display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; color:#16a34a; background:none; border:none; padding:2px 4px; cursor:pointer;" title="Hover or click to view skill recommendations">
                                Recommendation
                            </span>
                        @endif
                    </div>
                    <div class="app-row-meta">{{ $app->job->company }} &nbsp;·&nbsp; {{ $app->job->location }} &nbsp;·&nbsp; Applied {{ $app->created_at->diffForHumans() }}</div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="match-pill {{ $matchClass }} cursor-pointer" data-match="{{ json_encode($matchDataArr) }}" title="Click to view match details" style="cursor: pointer;">{{ $matchScore }}% match</div>
                    <button class="track-status-btn" data-app-status='@json($statusData)' title="Click to track your application">
                        <span class="badge-status {{ $statusClass }}">{{ $app->status }}</span>
                        <span class="track-hint">Track</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <p style="margin-bottom: 0;">No applications yet. <a href="{{ route('seeker.jobs') }}" style="color:#6366f1; text-decoration:none; font-weight:600;">Find jobs</a></p>
            </div>
            @endforelse
        </div>

        {{-- RIGHT: Sidebar widgets --}}
        <div>
            {{-- Quick Actions --}}
            <span class="sidebar-section-title">Quick Actions</span>
            <div style="display:flex; flex-direction:column; margin-bottom: 24px;">
                <a href="{{ route('seeker.jobs') }}" class="quick-action-card indigo">
                    <div>
                        <div class="quick-action-title">Find Matching Jobs</div>
                        <div class="quick-action-desc">Based on your profile</div>
                    </div>
                </a>
                <a href="{{ route('seeker.profile.view') }}" class="quick-action-card emerald">
                    <div>
                        <div class="quick-action-title">Profile</div>
                        <div class="quick-action-desc">View & manage your profile</div>
                    </div>
                </a>
                <a href="{{ route('seeker.applications') }}" class="quick-action-card sky">
                    <div>
                        <div class="quick-action-title">My Applications</div>
                        <div class="quick-action-desc">Track application statuses</div>
                    </div>
                </a>
            </div>

            {{-- Application Status Breakdown --}}
            @php
                $totalApplications = array_sum($statusCounts);
            @endphp
            @if($totalApplications > 0)
            <div class="dashboard-card" style="padding: 20px;">
                <span class="sidebar-section-title">Application Status</span>
                @php
                    $statuses = [
                        'applied' => ['label' => 'Applied', 'color' => '#6366f1'],
                        'reviewed' => ['label' => 'Reviewed', 'color' => '#f59e0b'],
                        'shortlisted' => ['label' => 'Shortlisted', 'color' => '#10b981'],
                        'rejected' => ['label' => 'Rejected', 'color' => '#f43f5e'],
                    ];
                @endphp
                @foreach($statuses as $statusKey => $statusData)
                    @php
                        $count = $statusCounts[$statusKey] ?? 0;
                        $pct = $totalApplications > 0 ? round(($count / $totalApplications) * 100) : 0;
                        $filterUrl = route('seeker.applications') . '?status=' . $statusKey;
                    @endphp
                    <a href="{{ $filterUrl }}" style="display:block; text-decoration:none; margin-bottom:12px; padding:6px 8px; border-radius:10px; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                {{ $statusData['label'] }}
                            </span>
                            <span style="display:flex; align-items:center; gap:5px;">
                                <span>{{ $count }} ({{ $pct }}%)</span>
                            </span>
                        </div>
                        <div class="progress-bar-container" style="height: 6px; margin-top: 0; background-color: #f1f5f9;">
                            <div class="progress-bar-fill" style="width: {{ $pct }}%; background: {{ $statusData['color'] }};"></div>
                        </div>
                    </a>
                @endforeach
            </div>
            @endif

            {{-- Profile Completeness Widget --}}
            <div class="dashboard-card" style="padding: 20px;">
                <span class="sidebar-section-title">Profile Completeness</span>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 14px; font-weight: 700; color: #0f172a;">
                    <span>Progress</span>
                    <span style="color: {{ $profileCompleteness === 100 ? '#10b981' : '#4f46e5' }};">{{ $profileCompleteness }}%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: {{ $profileCompleteness }}%; background: {{ $profileCompleteness === 100 ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #6366f1, #8b5cf6)' }};"></div>
                </div>
                <p style="font-size: 11.5px; color: #64748b; margin-top: 10px; line-height: 1.4;">
                    Complete your profile to ensure you get matches tailored to your experience.
                </p>
            </div>

            @if(!$user->skills)
            <div class="profile-alert">
                <div class="profile-alert-title">Complete your profile</div>
                <div class="profile-alert-desc">Add your skills and experience to get more accurate AI matches.</div>
                <a href="{{ route('seeker.profile') }}" class="profile-alert-link">Update now</a>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
