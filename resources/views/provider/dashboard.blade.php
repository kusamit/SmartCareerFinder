@extends('layouts.app')
@section('title', 'Provider Dashboard')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/provider-dashboard.css') }}">
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="page-header">
        <div>
            <div class="page-title">Provider Dashboard</div>
            <div class="page-sub">{{ $user->company_name ?? $user->name }}</div>
        </div>
        <a href="{{ route('provider.jobs.create') }}" class="btn-post">+ Post New Job</a>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="stats-grid">
        <a href="{{ route('provider.jobs') }}" class="stat-card indigo">
            <div class="stat-label">Total Jobs</div>
            <div class="stat-value">{{ $totalJobs }}</div>
        </a>
        <a href="{{ route('provider.jobs', ['status' => 'open']) }}" class="stat-card emerald">
            <div class="stat-label">Open Jobs</div>
            <div class="stat-value">{{ $openJobs }}</div>
        </a>
        <a href="{{ route('provider.jobs', ['status' => 'closed']) }}" class="stat-card rose">
            <div class="stat-label">Closed Jobs</div>
            <div class="stat-value">{{ $closedJobs }}</div>
        </a>
        <a href="{{ route('provider.all_applicants') }}" class="stat-card amber">
            <div class="stat-label">Total Applicants</div>
            <div class="stat-value">{{ $totalApps }}</div>
        </a>
    </div>


    {{-- ===== RECENT JOBS ===== --}}
    <div class="section-header">
        <div class="section-title">Recent Job Postings</div>
        <a href="{{ route('provider.jobs') }}" style="font-size:13px; color:#6366f1; font-weight:600; text-decoration:none;">View all →</a>
    </div>

    @forelse($jobs as $job)
    <div class="job-row">
        <div class="job-row-left">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                <span class="job-row-title">{{ $job->title }}</span>
                @if($job->isOpen())
                    <span class="status-open">Open</span>
                @else
                    <span class="status-closed">Closed</span>
                @endif
                <span class="type-badge">{{ $job->type }}</span>
            </div>
            <div class="job-row-meta">
                {{ $job->location }} &nbsp;·&nbsp; {{ $job->applications_count }} applicant(s) &nbsp;·&nbsp; {{ $job->created_at->diffForHumans() }}
            </div>
        </div>
        <div class="job-row-actions">
            <a href="{{ route('provider.jobs.applicants', $job->id) }}" class="btn-action">Applicants</a>
            <a href="{{ route('provider.jobs.edit', $job->id) }}" class="btn-action">Edit</a>
            <form method="POST" action="{{ route('provider.jobs.status', $job->id) }}" style="display:inline">
                @csrf @method('PATCH')
                <button class="btn-action {{ $job->isOpen() ? 'btn-action-danger' : 'btn-action-success' }}">
                    {{ $job->isOpen() ? 'Close' : 'Open' }}
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <h3>No jobs posted yet</h3>
        <p>Start attracting top talent by posting your first job opening.</p>
        <a href="{{ route('provider.jobs.create') }}" class="btn-post" style="display:inline-flex;">Post Your First Job</a>
    </div>
    @endforelse

</div>
@endsection


