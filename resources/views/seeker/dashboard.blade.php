@extends('layouts.app')
@section('title', 'Seeker Dashboard')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<style>
    body { background: #f8fafc !important; }

    /* ===== PAGE HEADER ===== */
    .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px;
    }
    .header-left {
        display: flex; align-items: center; gap: 16px;
    }
    .seeker-avatar {
        width: 56px; height: 56px; border-radius: 16px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 800;
        box-shadow: 0 8px 20px rgba(99,102,241,0.25);
        flex-shrink: 0;
    }
    .page-title { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
    .page-sub   { font-size: 13.5px; color: #64748b; margin-top: 2px; }

    /* ===== STAT CARDS ===== */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
    @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

    .stat-card {
        background: #fff;
        border-radius: 20px;
        padding: 22px 24px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        display: block;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
    .stat-card::before {
        content: '';
        position: absolute; top: 0; left: 0;
        width: 4px; height: 100%;
    }
    .stat-card.indigo::before { background: linear-gradient(180deg, #6366f1, #8b5cf6); }
    .stat-card.emerald::before { background: linear-gradient(180deg, #10b981, #059669); }
    .stat-card.green::before { background: linear-gradient(180deg, #22c55e, #16a34a); }
    .stat-card.rose::before { background: linear-gradient(180deg, #f43f5e, #be123c); }
    .stat-card.amber::before { background: linear-gradient(180deg, #f59e0b, #d97706); }

    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8; margin-bottom: 10px; }
    .stat-value { font-size: 36px; font-weight: 800; letter-spacing: -1px; line-height: 1; }
    .stat-card.indigo .stat-value { color: #4f46e5; }
    .stat-card.emerald .stat-value { color: #059669; }
    .stat-card.green .stat-value { color: #16a34a; }
    .stat-card.rose .stat-value { color: #e11d48; }
    .stat-card.amber .stat-value { color: #d97706; }

    /* ===== MAIN GRID ===== */
    .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }

    /* ===== CHARTS & CARDS ===== */
    .dashboard-card {
        background: #fff;
        border-radius: 24px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        padding: 24px;
        margin-bottom: 24px;
    }
    .dashboard-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }

    /* ===== APPLICATION ROW ===== */
    .app-row {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 10px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    }
    .app-row:hover { border-color: #c7d2fe; box-shadow: 0 4px 16px rgba(99,102,241,0.08); transform: translateY(-1px); }

    .app-row-title { font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none; transition: color 0.15s; }
    .app-row-title:hover { color: #4f46e5; }
    .app-row-meta { font-size: 12.5px; color: #94a3b8; margin-top: 3px; }

    /* ===== STATUS BADGES ===== */
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
        text-transform: capitalize;
    }
    .status-applied { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

    .status-reviewed { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

    .status-shortlisted { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

    .status-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .badge-status[data-app-status] { cursor: pointer; transition: opacity 0.15s, transform 0.15s; }
    .badge-status[data-app-status]:hover { opacity: 0.8; transform: scale(1.04); }

    /* ===== TRACK STATUS BUTTON (dashboard) ===== */
    .track-status-btn {
        display: inline-flex; flex-direction: row; align-items: center; gap: 6px;
        background: none; border: none; cursor: pointer; padding: 0;
    }
    .track-hint {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: 9px; font-weight: 700; color: #6366f1;
        text-transform: uppercase; letter-spacing: 0.08em;
        background: #eef2ff; border: 1px solid #c7d2fe;
        padding: 2px 7px; border-radius: 999px;
        transition: background 0.15s; white-space: nowrap;
    }
    .track-status-btn:hover .badge-status { opacity: 0.85; transform: scale(1.04); }
    .track-status-btn:hover .track-hint { background: #e0e7ff; }

    /* ===== MATCH BADGES ===== */
    .match-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
    }
    .match-high { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .match-mid  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .match-low  { background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; }

    /* ===== QUICK ACTIONS ===== */
    .quick-action-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 16px;
        display: flex; align-items: center; gap: 14px;
        text-decoration: none; transition: all 0.2s;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .quick-action-card:hover { border-color: #c7d2fe; box-shadow: 0 4px 14px rgba(99,102,241,0.08); transform: translateY(-1px); }
    .quick-action-card:last-child { margin-bottom: 0; }

    .quick-action-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 700;
    }
    .quick-action-card.indigo .quick-action-icon { background: #e0e7ff; color: #4f46e5; }
    .quick-action-card.emerald .quick-action-icon { background: #d1fae5; color: #059669; }
    .quick-action-card.sky .quick-action-icon { background: #e0f2fe; color: #0284c7; }

    .quick-action-title { font-size: 13.5px; font-weight: 700; color: #0f172a; }
    .quick-action-desc  { font-size: 11px; color: #94a3b8; margin-top: 1px; }

    /* ===== PROGRESS BARS ===== */
    .progress-bar-container { background: #e2e8f0; border-radius: 999px; height: 8px; overflow: hidden; width: 100%; margin-top: 8px; }
    .progress-bar-fill { background: linear-gradient(90deg, #6366f1, #8b5cf6); height: 100%; border-radius: 999px; }

    /* ===== SIDEBAR SECTIONS ===== */
    .sidebar-section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 14px; display: block; }

    /* ===== PROFILE ALERT ===== */
    .profile-alert {
        background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 18px;
        padding: 16px 20px; margin-top: 16px;
    }
    .profile-alert-title { font-size: 13px; font-weight: 700; color: #b45309; margin-bottom: 2px; }
    .profile-alert-desc  { font-size: 11.5px; color: #78350f; line-height: 1.5; }
    .profile-alert-link  { font-size: 11.5px; font-weight: 700; color: #b45309; text-decoration: none; display: inline-block; margin-top: 8px; }
    .profile-alert-link:hover { text-decoration: underline; }

    /* ===== EMPTY STATE ===== */
    .empty-state { text-align: center; padding: 32px 24px; color: #94a3b8; font-size: 13px; background: #fff; border: 2px dashed #e2e8f0; border-radius: 20px; }
</style>
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== HEADER ===== --}}
    <div class="page-header">
        <div class="header-left">
            <div class="seeker-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
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
                <a href="{{ route('seeker.applications') }}" style="font-size:13px; color:#6366f1; font-weight:600; text-decoration:none;">View all →</a>
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
            @endphp
            <div class="app-row">
                <div>
                    <a href="{{ route('jobs.show', $app->job->id) }}" class="app-row-title">{{ $app->job->title }}</a>
                    <div class="app-row-meta">{{ $app->job->company }} &nbsp;·&nbsp; {{ $app->job->location }} &nbsp;·&nbsp; Applied {{ $app->created_at->diffForHumans() }}</div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="match-pill {{ $matchClass }}">{{ $matchScore }}% match</div>
                    <button class="track-status-btn" data-app-status='@json($statusData)' title="Click to track your application">
                        <span class="badge-status {{ $statusClass }}">{{ $app->status }}</span>
                        <span class="track-hint">Track &rarr;</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <p style="margin-bottom: 0;">No applications yet. <a href="{{ route('seeker.jobs') }}" style="color:#6366f1; text-decoration:none; font-weight:600;">Find jobs →</a></p>
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
                                @if($count > 0)<span style="font-size:10px; color:#6366f1;">&rarr;</span>@endif
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
                <a href="{{ route('seeker.profile') }}" class="profile-alert-link">Update now →</a>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
