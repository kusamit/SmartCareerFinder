@extends('layouts.app')
@section('title', 'Provider Dashboard')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@section('content')
<div class="fade-up">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold">Provider Dashboard</h1>
            <p class="text-slate-400 text-sm">{{ $user->company_name ?? $user->name }}</p>
        </div>
        <a href="{{ route('provider.jobs.create') }}" class="btn-primary">+ Post New Job</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">Total Jobs</div>
            <div class="text-3xl font-bold text-indigo-400">{{ $totalJobs }}</div>
        </div>
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">Open Jobs</div>
            <div class="text-3xl font-bold text-emerald-400">{{ $openJobs }}</div>
        </div>
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">Total Applicants</div>
            <div class="text-3xl font-bold text-amber-400">{{ $totalApps }}</div>
        </div>
    </div>

    {{-- Recent Jobs --}}
    <h2 class="text-lg font-semibold mb-4">Recent Job Postings</h2>
    @forelse($jobs as $job)
    <div class="card p-5 mb-3 flex items-center gap-4 hover:border-slate-600 transition-all">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-semibold">{{ $job->title }}</span>
                <span class="badge {{ $job->isOpen() ? 'bg-emerald-900/50 text-emerald-300' : 'bg-red-900/50 text-red-300' }}">
                    {{ $job->status }}
                </span>
                <span class="badge bg-slate-700 text-slate-400 capitalize">{{ $job->type }}</span>
            </div>
            <div class="text-slate-400 text-xs mt-1">📍 {{ $job->location }} · {{ $job->applications_count }} applicant(s) · {{ $job->created_at->diffForHumans() }}</div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('provider.jobs.applicants', $job->id) }}" class="btn-outline text-xs py-1.5 px-3">Applicants</a>
            <a href="{{ route('provider.jobs.edit', $job->id) }}" class="btn-outline text-xs py-1.5 px-3">Edit</a>
            <form method="POST" action="{{ route('provider.jobs.status', $job->id) }}">
                @csrf @method('PATCH')
                <button class="btn-outline text-xs py-1.5 px-3 {{ $job->isOpen() ? 'text-red-400 border-red-800 hover:border-red-500' : 'text-emerald-400 border-emerald-800 hover:border-emerald-500' }}">
                    {{ $job->isOpen() ? 'Close' : 'Open' }}
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="card p-16 text-center">
        <div class="text-5xl mb-4">📢</div>
        <h3 class="text-xl font-semibold mb-2">No jobs posted yet</h3>
        <a href="{{ route('provider.jobs.create') }}" class="btn-primary inline-flex">Post Your First Job</a>
    </div>
    @endforelse

    @if($jobs->count() > 0)
    <div class="mt-2 text-right">
        <a href="{{ route('provider.jobs') }}" class="text-indigo-400 text-sm hover:underline">View all jobs →</a>
    </div>
    @endif
</div>
@endsection
