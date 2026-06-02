@extends('layouts.app')
@section('title', 'Seeker Dashboard')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@section('content')
<div class="fade-up">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-300 font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold">Hello, {{ $user->name }} 👋</h1>
                <p class="text-slate-400 text-sm">Find jobs that match your skills</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">Open Jobs</div>
            <div class="text-3xl font-bold text-indigo-400">{{ $totalJobs }}</div>
        </div>
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">Applied</div>
            <div class="text-3xl font-bold text-emerald-400">{{ $applications->count() }}</div>
        </div>
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">Profile</div>
            <div class="text-3xl font-bold text-amber-400">{{ $user->skills ? '✓' : '!' }}</div>
        </div>
        <div class="card p-5">
            <div class="text-slate-400 text-xs uppercase tracking-widest mb-1">CV</div>
            <div class="text-3xl font-bold text-sky-400">{{ $user->cv_path ? '✓' : '—' }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Recent Applications --}}
        <div class="lg:col-span-2">
            <h2 class="text-lg font-semibold mb-4">Recent Applications</h2>
            @forelse($applications as $app)
            <div class="card p-5 mb-3 flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">{{ $app->job->title }}</div>
                    <div class="text-slate-400 text-xs">{{ $app->job->company }} · {{ $app->job->location }}</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs mono text-indigo-400 font-semibold">{{ $app->match_score }}% match</div>
                    @php
                        $colors = ['applied'=>'bg-blue-900/50 text-blue-300','reviewed'=>'bg-amber-900/50 text-amber-300','shortlisted'=>'bg-emerald-900/50 text-emerald-300','rejected'=>'bg-red-900/50 text-red-300'];
                    @endphp
                    <span class="badge {{ $colors[$app->status] ?? 'bg-slate-700 text-slate-300' }} mt-1">{{ $app->status }}</span>
                </div>
            </div>
            @empty
            <div class="card p-8 text-center text-slate-500">
                <div class="text-4xl mb-2">📋</div>
                <p>No applications yet. <a href="{{ route('seeker.jobs') }}" class="text-indigo-400 hover:underline">Find jobs →</a></p>
            </div>
            @endforelse
        </div>

        {{-- Quick Actions --}}
        <div>
            <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('seeker.jobs') }}" class="card p-5 flex items-center gap-4 hover:border-indigo-500/50 transition-all block group">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 flex items-center justify-center text-xl group-hover:bg-indigo-600/30 transition-colors">🎯</div>
                    <div>
                        <div class="font-semibold text-sm">Find Matching Jobs</div>
                        <div class="text-slate-400 text-xs">Based on your profile</div>
                    </div>
                </a>
                <a href="{{ route('seeker.profile') }}" class="card p-5 flex items-center gap-4 hover:border-indigo-500/50 transition-all block group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600/20 flex items-center justify-center text-xl group-hover:bg-emerald-600/30 transition-colors">✏️</div>
                    <div>
                        <div class="font-semibold text-sm">Update Profile</div>
                        <div class="text-slate-400 text-xs">Improve match score</div>
                    </div>
                </a>
                <a href="{{ route('seeker.applications') }}" class="card p-5 flex items-center gap-4 hover:border-indigo-500/50 transition-all block group">
                    <div class="w-10 h-10 rounded-xl bg-sky-600/20 flex items-center justify-center text-xl group-hover:bg-sky-600/30 transition-colors">📂</div>
                    <div>
                        <div class="font-semibold text-sm">My Applications</div>
                        <div class="text-slate-400 text-xs">Track your status</div>
                    </div>
                </a>
            </div>

            @if(!$user->skills)
            <div class="mt-4 card p-4 border-amber-600/40 bg-amber-900/20">
                <div class="text-amber-300 font-semibold text-sm mb-1">⚠️ Complete your profile</div>
                <p class="text-slate-400 text-xs">Add your skills and experience to get better job matches.</p>
                <a href="{{ route('seeker.profile') }}" class="text-amber-400 text-xs hover:underline mt-2 block">Update now →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
