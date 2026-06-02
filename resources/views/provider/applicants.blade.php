@extends('layouts.app')
@section('title', 'Applicants')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@section('content')
<div class="fade-up">
    <div class="mb-8">
        <a href="{{ route('provider.jobs') }}" class="text-slate-400 hover:text-white text-sm mb-4 block">← Back to Jobs</a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Applicants</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ $job->title }} · {{ $applications->count() }} applicant(s)</p>
            </div>
            <span class="badge {{ $job->isOpen() ? 'bg-emerald-900/50 text-emerald-300' : 'bg-red-900/50 text-red-300' }} text-sm">
                {{ ucfirst($job->status) }}
            </span>
        </div>
    </div>

    {{-- Job Summary --}}
    <div class="card p-5 mb-8 bg-slate-800/30">
        <div class="grid sm:grid-cols-3 gap-4 text-sm">
            <div><span class="text-slate-500">Location:</span> <span class="text-white">{{ $job->location }}</span></div>
            <div><span class="text-slate-500">Type:</span> <span class="text-white capitalize">{{ $job->type }}</span></div>
            <div><span class="text-slate-500">Experience:</span> <span class="text-white">{{ $job->experience_required ?? '—' }}</span></div>
        </div>
        @if($job->key_skills)
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach($job->skillsArray() as $skill)
            <span class="badge bg-indigo-900/40 text-indigo-300 border border-indigo-700/40">{{ $skill }}</span>
            @endforeach
        </div>
        @endif
    </div>

    @forelse($applications as $app)
    @php
        $score = $app->match_score;
        $color = $score >= 70 ? 'emerald' : ($score >= 40 ? 'amber' : 'slate');
    @endphp
    <div class="card p-5 mb-3 flex items-center gap-5 hover:border-slate-600 transition-all">
        {{-- Avatar --}}
        <div class="w-12 h-12 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 font-bold text-lg shrink-0">
            {{ strtoupper(substr($app->seeker->name, 0, 1)) }}
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="font-semibold">{{ $app->seeker->name }}</div>
            <div class="text-slate-400 text-xs mt-0.5">{{ $app->seeker->email }}</div>
            @if($app->seeker->location)
            <div class="text-slate-500 text-xs">📍 {{ $app->seeker->location }}</div>
            @endif
            @if($app->seeker->skills)
            <div class="flex flex-wrap gap-1 mt-2">
                @foreach(array_slice(explode(',', $app->seeker->skills), 0, 5) as $skill)
                <span class="badge bg-slate-800 text-slate-400 border border-slate-700 text-xs">{{ trim($skill) }}</span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Exp --}}
        <div class="text-center shrink-0 hidden sm:block">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-0.5">Experience</div>
            <div class="font-semibold text-sm">{{ $app->seeker->experience_years ?? '—' }} yr(s)</div>
        </div>

        {{-- Match Score --}}
        <div class="text-center shrink-0">
            <div class="text-2xl font-bold mono text-{{ $color }}-400">{{ $score }}%</div>
            <div class="text-slate-500 text-xs">Match</div>
            <div class="match-bar w-16 mt-1">
                <div class="match-fill bg-{{ $color }}-500" style="width: {{ $score }}%"></div>
            </div>
        </div>

        {{-- Applied date --}}
        <div class="text-center shrink-0 hidden lg:block">
            <div class="text-slate-500 text-xs">Applied</div>
            <div class="text-xs text-slate-300">{{ $app->created_at->format('M d') }}</div>
        </div>
    </div>
    @empty
    <div class="card p-16 text-center">
        <div class="text-5xl mb-4">👥</div>
        <h3 class="text-xl font-semibold mb-2">No applicants yet</h3>
        <p class="text-slate-400 text-sm">Share your job posting to attract candidates.</p>
    </div>
    @endforelse
</div>
@endsection
