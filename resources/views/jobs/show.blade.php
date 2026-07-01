@extends('layouts.app')
@section('title', $job->title)

@section('nav_links')
@if(session('user_id'))
    @php $user = \App\Models\User::find(session('user_id')); @endphp
    @if($user && $user->role === 'seeker')
        <a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
        <a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
        <a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
        <a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
    @elseif($user && $user->role === 'provider')
        <a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
        <a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
        <a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
    @endif
@endif
@endsection

@section('content')
<div class="max-w-4xl mx-auto fade-up">
    {{-- Header Navigation --}}
    <div class="mb-6">
        @if(session('user_id') && isset($user) && $user->role === 'seeker')
            <a href="{{ route('seeker.jobs') }}" class="text-slate-400 hover:text-indigo-400 text-sm flex items-center gap-1.5 transition-colors">
                ← Back to Job Matches
            </a>
        @elseif(session('user_id') && isset($user) && $user->role === 'provider')
            <a href="{{ route('provider.jobs') }}" class="text-slate-400 hover:text-indigo-400 text-sm flex items-center gap-1.5 transition-colors">
                ← Back to My Jobs
            </a>
        @else
            <a href="{{ url('/') }}" class="text-slate-400 hover:text-indigo-400 text-sm flex items-center gap-1.5 transition-colors">
                ← Back to Home
            </a>
        @endif
    </div>

    {{-- Main Job Header Card --}}
    <div class="card p-8 mb-8 border-indigo-500/20 bg-gradient-to-br from-indigo-900/10 via-transparent to-transparent">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <span class="badge {{ $job->isOpen() ? 'bg-emerald-900/50 text-emerald-300 border border-emerald-700/50' : 'bg-red-900/50 text-red-300 border border-red-700/50' }}">
                        {{ ucfirst($job->status) }}
                    </span>
                    <span class="badge bg-slate-800 text-slate-400 border border-slate-700 capitalize">{{ $job->type }}</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white mb-2">{{ $job->title }}</h1>
                <div class="text-slate-300 font-medium text-lg mb-4">🏢 {{ $job->company }}</div>
                
                {{-- Quick Stats Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-800/30 border border-slate-700/30 text-sm">
                    <div>
                        <span class="text-slate-500 block mb-0.5">Location</span>
                        <span class="text-white font-medium">{{ $job->location }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Salary Range</span>
                        <span class="text-white font-medium">{{ $job->salary_range ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Experience Required</span>
                        <span class="text-white font-medium">{{ $job->experience_required ?? 'Not specified' }}</span>
                    </div>
                </div>
            </div>

            {{-- Match Score & Application Action --}}
            @if(session('user_id') && isset($user) && $user->role === 'seeker')
                @php
                    $score = $user->matchScore($job);
                    $color = $score >= 70 ? 'emerald' : ($score >= 40 ? 'amber' : 'slate');
                    $applied = \App\Models\Application::where('job_id', $job->id)->where('user_id', $user->id)->exists();
                @endphp
                <div class="shrink-0 text-center md:text-right md:min-w-[160px] p-6 rounded-2xl bg-indigo-950/20 border border-indigo-900/40">
                    <div class="text-4xl font-bold mono text-{{ $color }}-400 mb-1">{{ $score }}%</div>
                    <div class="text-slate-400 text-xs mb-4">Match Score</div>
                    <div class="match-bar w-full max-w-[120px] mx-auto md:mr-0 mb-5">
                        <div class="match-fill bg-{{ $color }}-500" style="width: {{ $score }}%"></div>
                    </div>

                    @if($applied)
                        <span class="btn-outline w-full justify-center text-emerald-400 border-emerald-800 cursor-default flex items-center gap-1.5">
                            ✓ Applied
                        </span>
                    @elseif($job->isOpen())
                        <form method="POST" action="{{ route('seeker.jobs.apply', $job->id) }}">
                            @csrf
                            <button type="submit" class="btn-primary w-full justify-center flex">
                                Apply Now
                            </button>
                        </form>
                    @else
                        <span class="badge bg-red-900/50 text-red-300 w-full justify-center py-2.5">Closed</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Description & Requirements --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Job Description --}}
            <div class="card p-8">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b border-slate-700/50 text-white flex items-center gap-2">
                    📄 Job Description
                </h2>
                <div class="prose prose-invert text-slate-300 text-sm leading-relaxed ql-editor-display">
                    {!! $job->description !!}
                </div>
            </div>

            {{-- Requirements --}}
            <div class="card p-8">
                <h2 class="text-xl font-bold mb-4 pb-2 border-b border-slate-700/50 text-white flex items-center gap-2">
                    📋 Requirements & Qualifications
                </h2>
                <div class="prose prose-invert text-slate-300 text-sm leading-relaxed ql-editor-display">
                    {!! $job->requirements !!}
                </div>
            </div>
        </div>

        {{-- Right Side info (Key Skills, metadata) --}}
        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">🔑 Key Skills</h3>
                @if($job->key_skills)
                    <div class="flex flex-wrap gap-2">
                        @foreach($job->skillsArray() as $skill)
                            <span class="badge bg-indigo-900/40 text-indigo-300 border border-indigo-700/40">{{ $skill }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-500 text-sm">No specific skills listed.</span>
                @endif
            </div>

            <div class="card p-6 text-xs text-slate-500 space-y-2">
                <div>Posted: {{ $job->created_at->format('M d, Y') }}</div>
                <div>Last updated: {{ $job->updated_at->diffForHumans() }}</div>
                <div>Job ID: #{{ $job->id }}</div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling to ensure Quill HTML renders correctly (bullets, indentations, etc.) */
    .ql-editor-display ul {
        list-style-type: disc !important;
        padding-left: 1.5em !important;
        margin-top: 0.5em !important;
        margin-bottom: 0.5em !important;
    }
    .ql-editor-display ol {
        list-style-type: decimal !important;
        padding-left: 1.5em !important;
        margin-top: 0.5em !important;
        margin-bottom: 0.5em !important;
    }
    .ql-editor-display li {
        margin-bottom: 0.25em !important;
    }
    .ql-editor-display h1 {
        font-size: 1.5em !important;
        font-weight: bold !important;
        margin-top: 1em !important;
        margin-bottom: 0.5em !important;
    }
    .ql-editor-display h2 {
        font-size: 1.25em !important;
        font-weight: bold !important;
        margin-top: 0.8em !important;
        margin-bottom: 0.4em !important;
    }
    .ql-editor-display p {
        margin-bottom: 0.75em !important;
    }
</style>
@endsection
