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

@push('styles')
<link rel="stylesheet" href="{{ asset('css/jobs-show.css') }}">
@endpush

@section('content')
<div class="max-w-5xl mx-auto fade-up">
    {{-- Header Navigation --}}
    <div class="mb-6">
        @if(session('user_id') && isset($user) && $user->role === 'seeker')
            <a href="{{ route('seeker.jobs') }}" class="inline-flex items-center text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm font-semibold transition-colors no-underline">
                &larr; Back to Job Matches
            </a>
        @elseif(session('user_id') && isset($user) && $user->role === 'provider')
            <a href="{{ route('provider.jobs') }}" class="inline-flex items-center text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm font-semibold transition-colors no-underline">
                &larr; Back to My Jobs
            </a>
        @else
            <a href="{{ url('/') }}" class="inline-flex items-center text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm font-semibold transition-colors no-underline">
                &larr; Back to Home
            </a>
        @endif
    </div>

    {{-- Main Job Header Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 mb-8 shadow-sm transition-all duration-300">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $job->isOpen() ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30' : 'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/30' }}">
                        {{ $job->status }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 capitalize">
                        {{ $job->type }}
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">{{ $job->title }}</h1>
                <div class="text-indigo-600 dark:text-indigo-400 font-bold text-lg mb-6">{{ $job->company }}</div>
                
                {{-- Quick Stats Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/60 text-sm">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] font-bold uppercase tracking-wider mb-1">Location</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $job->location }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] font-bold uppercase tracking-wider mb-1">Salary Range</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $job->salary_range ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px] font-bold uppercase tracking-wider mb-1">Experience Required</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $job->experience_required ?? 'Not specified' }}</span>
                    </div>
                </div>
            </div>

            {{-- Match Score & Application Action --}}
            @if(session('user_id') && isset($user) && $user->role === 'seeker')
                @php
                    $score        = $matchScore ?? 0;
                    $applied      = \App\Models\Application::where('job_id', $job->id)->where('user_id', $user->id)->exists();
                    $details      = $user->matchDetails($job, $score);
                    $matchDataArr = [
                        'name'             => $user->name,
                        'job_title'        => $job->title,
                        'score'            => $score,
                        'matched_skills'   => array_values($details['matched_skills']),
                        'unmatched_skills' => array_values($details['unmatched_skills']),
                        'location_match'   => $details['location_match'],
                        'role_match'       => $details['role_match'],
                        'seeker_location'  => $details['seeker_location'],
                        'job_location'     => $details['job_location'],
                        'seeker_role'      => $details['seeker_role'],
                        'exp_match'        => $details['exp_match'],
                        'exp_message'      => $details['exp_message'],
                        'portfolio_match'  => $details['portfolio_match'],
                        'composite'        => $details['composite'],
                    ];
                    
                    // Explicit classes to prevent Tailwind purging issues
                    if ($score >= 70) {
                        $scoreColorClass = 'text-emerald-600 dark:text-emerald-400';
                        $barColorClass   = 'bg-emerald-500';
                    } elseif ($score >= 40) {
                        $scoreColorClass = 'text-amber-600 dark:text-amber-400';
                        $barColorClass   = 'bg-amber-500';
                    } else {
                        $scoreColorClass = 'text-slate-600 dark:text-slate-400';
                        $barColorClass   = 'bg-slate-500';
                    }
                @endphp
                <div class="shrink-0 text-center p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 w-full md:w-auto md:min-w-[200px]">
                    {{-- Clickable score zone --}}
                    <div class="cursor-pointer p-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-all duration-200 group mb-3"
                         data-match='@json($matchDataArr)'
                         title="Click to see match breakdown (matches & non-matches)">
                        <div class="text-5xl font-extrabold mono {{ $scoreColorClass }} mb-1 group-hover:scale-105 transition-transform duration-200">{{ $score }}%</div>
                        <div class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Match Score</div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden mb-1">
                            <div class="h-full rounded-full {{ $barColorClass }} transition-all duration-500" style="width: {{ $score }}%"></div>
                        </div>
                    </div>

                    @if($applied)
                        <div class="w-full py-3 px-4 rounded-xl font-bold text-sm text-center bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                            Applied
                        </div>
                    @elseif($job->isOpen())
                        <form method="POST" action="{{ route('seeker.jobs.apply', $job->id) }}">
                            @csrf
                            <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-sm text-center bg-indigo-600 hover:bg-indigo-700 text-white shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer border-none">
                                Apply Now
                            </button>
                        </form>
                    @else
                        <div class="w-full py-3 px-4 rounded-xl font-bold text-sm text-center bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                            Closed
                        </div>
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
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 shadow-sm">
                <h2 class="text-xl font-bold mb-5 pb-3 border-b border-slate-100 dark:border-slate-800 text-slate-950 dark:text-white tracking-tight">
                    Job Description
                </h2>
                <div class="prose dark:prose-invert text-slate-650 dark:text-slate-350 text-sm leading-relaxed ql-editor-display">
                    {!! $job->description !!}
                </div>
            </div>

            {{-- Requirements --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 shadow-sm">
                <h2 class="text-xl font-bold mb-5 pb-3 border-b border-slate-100 dark:border-slate-800 text-slate-950 dark:text-white tracking-tight">
                    Requirements &amp; Qualifications
                </h2>
                <div class="prose dark:prose-invert text-slate-650 dark:text-slate-350 text-sm leading-relaxed ql-editor-display">
                    {!! $job->requirements !!}
                </div>
            </div>
        </div>

        {{-- Right Side info (Key Skills, metadata) --}}
        <div class="space-y-8">
            {{-- Key Skills --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Key Skills</h3>
                @if($job->key_skills)
                    <div class="flex flex-wrap gap-2">
                        @foreach($job->skillsArray() as $skill)
                            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50/80 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-900/30">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-450 dark:text-slate-500 text-sm italic">No specific skills listed.</span>
                @endif
            </div>

            {{-- Metadata info --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm text-xs text-slate-500 dark:text-slate-400 space-y-3 font-medium">
                <div class="flex justify-between py-1 border-b border-slate-55 dark:border-slate-800">
                    <span>Posted</span>
                    <span class="text-slate-800 dark:text-slate-300 font-semibold">{{ $job->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-55 dark:border-slate-800">
                    <span>Last updated</span>
                    <span class="text-slate-800 dark:text-slate-300 font-semibold">{{ $job->updated_at->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>Job ID</span>
                    <span class="text-slate-800 dark:text-slate-300 font-semibold">#{{ $job->id }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
