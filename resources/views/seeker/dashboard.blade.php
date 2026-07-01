@extends('layouts.app')
@section('title', 'Seeker Dashboard')

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
    .page-header {
        display: flex; align-items: center; gap: 16px; margin-bottom: 28px;
    }
    .seeker-avatar {
        width: 52px; height: 52px; border-radius: 16px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 800;
        box-shadow: 0 4px 14px rgba(99,102,241,0.3);
        flex-shrink: 0;
    }
    .page-title { font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px; }
    .page-sub   { font-size: 13px; color: #64748b; margin-top: 2px; }

    /* ===== STAT CARDS ===== */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
    @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

    .stat-card {
        background: #fff;
        border-radius: 20px;
        padding: 20px 22px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.09); }
    .stat-card::before {
        content: '';
        position: absolute; top: 0; left: 0;
        width: 4px; height: 100%;
    }
    .stat-card.indigo::before { background: linear-gradient(180deg, #6366f1, #8b5cf6); }
    .stat-card.emerald::before { background: linear-gradient(180deg, #10b981, #059669); }
    .stat-card.amber::before { background: linear-gradient(180deg, #f59e0b, #d97706); }
    .stat-card.sky::before { background: linear-gradient(180deg, #0ea5e9, #0284c7); }

    .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8; margin-bottom: 8px; }
    .stat-value { font-size: 30px; font-weight: 800; letter-spacing: -0.5px; line-height: 1; }
    .stat-card.indigo .stat-value { color: #4f46e5; }
    .stat-card.emerald .stat-value { color: #059669; }
    .stat-card.amber .stat-value { color: #d97706; }
    .stat-card.sky .stat-value { color: #0284c7; }

    /* ===== MAIN GRID ===== */
    .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }

    /* ===== CHARTS & CARDS ===== */
    .dashboard-card {
        background: #fff;
        border-radius: 24px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        padding: 24px;
    }
    .dashboard-card-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 20px; }

    /* ===== APPLICATION ROW ===== */
    .app-row {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 10px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        transition: all 0.2s ease;
    }
    .app-row:hover { border-color: #c7d2fe; box-shadow: 0 4px 16px rgba(99,102,241,0.08); transform: translateY(-1px); }

    .app-row-title { font-size: 14.5px; font-weight: 700; color: #0f172a; }
    .app-row-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }

    /* ===== STATUS BADGES ===== */
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
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

    /* ===== MATCH BADGES ===== */
    .match-percent { font-size: 14px; font-weight: 800; color: #4f46e5; font-family: 'JetBrains Mono', monospace; }

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
        font-size: 18px; font-weight: 700; transition: background 0.2s;
    }
    .quick-action-card.indigo .quick-action-icon { background: #e0e7ff; color: #4f46e5; }
    .quick-action-card.emerald .quick-action-icon { background: #d1fae5; color: #059669; }
    .quick-action-card.sky .quick-action-icon { background: #e0f2fe; color: #0284c7; }

    .quick-action-title { font-size: 13.5px; font-weight: 700; color: #0f172a; }
    .quick-action-desc  { font-size: 11px; color: #94a3b8; margin-top: 1px; }

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
    .empty-state { text-align: center; padding: 24px; color: #94a3b8; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== HEADER ===== --}}
    <div class="page-header">
        <div class="seeker-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
        <div>
            <div class="page-title">Hello, {{ $user->name }}</div>
            <div class="page-sub">Find jobs that match your skills</div>
        </div>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="stats-grid">
        <div class="stat-card indigo">
            <div class="stat-label">Open Jobs</div>
            <div class="stat-value">{{ $totalJobs }}</div>
        </div>
        <div class="stat-card emerald">
            <div class="stat-label">Applied</div>
            <div class="stat-value">{{ $applications->count() }}</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-label">Profile Status</div>
            <div class="stat-value" style="font-size: 24px; line-height: 1.25;">{{ $user->skills ? 'Complete' : 'Pending' }}</div>
        </div>
        <div class="stat-card sky">
            <div class="stat-label">CV Status</div>
            <div class="stat-value" style="font-size: 24px; line-height: 1.25;">{{ $user->cv_path ? 'Uploaded' : 'None' }}</div>
        </div>
    </div>

    {{-- ===== CHART CARD ===== --}}
    <div class="dashboard-card mb-8">
        <div class="dashboard-card-title">Application Activity</div>
        <canvas id="applicationChart" height="100"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('applicationChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Applications',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { family: 'Sora' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Sora' } }
                        }
                    }
                }
            });
        });
    </script>

    {{-- ===== MAIN GRID ===== --}}
    <div class="dashboard-grid">

        {{-- LEFT: Applications --}}
        <div>
            <div style="display:flex; align-items:center; justify-content:between; margin-bottom:16px;">
                <h2 class="section-title" style="margin-bottom:0;">Recent Applications</h2>
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
            @endphp
            <div class="app-row">
                <div>
                    <div class="app-row-title">{{ $app->job->title }}</div>
                    <div class="app-row-meta">{{ $app->job->company }} &nbsp;·&nbsp; {{ $app->job->location }}</div>
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <div class="match-percent">{{ $app->match_score }}% match</div>
                    <span class="badge-status {{ $statusClass }}">{{ $app->status }}</span>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <p>No applications yet. <a href="{{ route('seeker.jobs') }}" style="color:#6366f1; text-decoration:none; font-weight:600;">Find jobs →</a></p>
            </div>
            @endforelse
        </div>

        {{-- RIGHT: Quick Actions --}}
        <div>
            <h2 class="section-title">Quick Actions</h2>
            <div style="display:flex; flex-direction:column;">
                <a href="{{ route('seeker.jobs') }}" class="quick-action-card indigo">
                    <div class="quick-action-icon">🔍</div>
                    <div>
                        <div class="quick-action-title">Find Matching Jobs</div>
                        <div class="quick-action-desc">Based on your profile</div>
                    </div>
                </a>
                <a href="{{ route('seeker.profile') }}" class="quick-action-card emerald">
                    <div class="quick-action-icon">👤</div>
                    <div>
                        <div class="quick-action-title">Update Profile</div>
                        <div class="quick-action-desc">Improve match score</div>
                    </div>
                </a>
                <a href="{{ route('seeker.applications') }}" class="quick-action-card sky">
                    <div class="quick-action-icon">📄</div>
                    <div>
                        <div class="quick-action-title">My Applications</div>
                        <div class="quick-action-desc">Track application statuses</div>
                    </div>
                </a>
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
