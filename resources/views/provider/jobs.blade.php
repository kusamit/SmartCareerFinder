@extends('layouts.app')
@section('title', 'My Jobs')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@section('content')
<div class="fade-up">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold">My Job Postings</h1>
        <a href="{{ route('provider.jobs.create') }}" class="btn-primary">Post New Job</a>
    </div>

    @forelse($jobs as $job)
    <div class="card p-6 mb-4">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h3 class="font-bold text-lg">{{ $job->title }}</h3>
                    <span class="badge {{ $job->isOpen() ? 'bg-emerald-900/50 text-emerald-300 border border-emerald-700/50' : 'bg-red-900/50 text-red-300 border border-red-700/50' }}">
                        {{ ucfirst($job->status) }}
                    </span>
                    <span class="badge bg-slate-700 text-slate-400 capitalize">{{ $job->type }}</span>
                </div>
                    <div class="text-slate-400 text-sm mb-2">
                        {{ $job->location }}
                        @if($job->salary_range) &nbsp;·&nbsp; {{ $job->salary_range }} @endif
                        @if($job->experience_required) &nbsp;·&nbsp; {{ $job->experience_required }} @endif
                    </div>

                <p class="text-slate-300 text-sm line-clamp-2 mb-3">{{ $job->description }}</p>
                @if($job->key_skills)
                <div class="flex flex-wrap gap-1.5">
                    @foreach($job->skillsArray() as $skill)
                    <span class="badge bg-slate-800 text-slate-400 border border-slate-700">{{ $skill }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="shrink-0 text-center">
                <div class="text-3xl font-bold text-indigo-400">{{ $job->applications_count }}</div>
                <div class="text-slate-500 text-xs mb-3">Applicants</div>
                <div class="text-slate-500 text-xs">{{ $job->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-slate-700/50">
            <a href="{{ route('provider.jobs.applicants', $job->id) }}" class="btn-primary text-xs py-1.5 px-4">
                View Applicants
            </a>
            <a href="{{ route('provider.jobs.edit', $job->id) }}" class="btn-outline text-xs py-1.5 px-4">Edit</a>

            <form method="POST" action="{{ route('provider.jobs.status', $job->id) }}" class="inline">
                @csrf @method('PATCH')
                <button class="btn-outline text-xs py-1.5 px-4 {{ $job->isOpen() ? 'text-red-400 border-red-800 hover:border-red-500' : 'text-emerald-400 border-emerald-800 hover:border-emerald-500' }}">
                    {{ $job->isOpen() ? 'Close Job' : 'Reopen Job' }}
                </button>
            </form>

            <form method="POST" action="{{ route('provider.jobs.destroy', $job->id) }}" class="inline"
                onsubmit="return confirm('Delete this job?')">
                @csrf @method('DELETE')
                <button class="btn-outline text-xs py-1.5 px-4 text-red-400 border-red-900 hover:border-red-600">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="card p-16 text-center">
        <div class="text-5xl mb-4">&nbsp;</div>
        <h3 class="text-xl font-semibold mb-2">No jobs posted yet</h3>

        <a href="{{ route('provider.jobs.create') }}" class="btn-primary inline-flex">Post Your First Job</a>
    </div>
    @endforelse
</div>
@endsection
