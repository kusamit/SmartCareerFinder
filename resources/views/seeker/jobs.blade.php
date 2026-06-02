@extends('layouts.app')
@section('title', 'Find Jobs')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@section('content')
<div class="fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold">Job Matches</h1>
            <p class="text-slate-400 text-sm mt-0.5">
                @if(session('cv_mode'))
                    Results based on your uploaded CV
                @else
                    Results based on your profile · {{ $scored->count() }} jobs found
                @endif
            </p>
        </div>

        {{-- CV Upload --}}
        <div class="card p-4 min-w-[260px]">
            <div class="text-xs text-slate-400 font-semibold uppercase tracking-widest mb-2">📎 Upload CV to match</div>
            <form method="POST" action="{{ route('seeker.jobs.cv') }}" enctype="multipart/form-data" class="flex gap-2">
                @csrf
                <input type="file" name="cv" accept=".pdf,.doc,.docx,.txt" class="text-xs text-slate-400 flex-1 min-w-0 file:bg-indigo-600 file:text-white file:border-0 file:rounded-lg file:px-3 file:py-1 file:text-xs file:cursor-pointer file:mr-2" required>
                <button type="submit" class="btn-primary text-xs py-1.5 px-4 shrink-0">Find</button>
            </form>
            @error('cv')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Mode Banner --}}
    @if(session('cv_mode'))
    <div class="bg-indigo-900/30 border border-indigo-600/40 rounded-xl px-5 py-3 mb-6 flex items-center gap-2 text-sm">
        <span class="text-indigo-300">✨ Showing results matched to your uploaded CV</span>
        <a href="{{ route('seeker.jobs') }}" class="ml-auto text-slate-400 hover:text-white text-xs">Reset →</a>
    </div>
    @endif

    {{-- Job List --}}
    @forelse($scored as $job)
    @php $score = $job->match_score; $color = $score >= 70 ? 'emerald' : ($score >= 40 ? 'amber' : 'slate'); @endphp
    <div class="card p-6 mb-4 hover:border-slate-600 transition-all group">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            {{-- Job Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h3 class="font-bold text-lg">{{ $job->title }}</h3>
                    @if($job->status === 'open')
                    <span class="badge bg-emerald-900/60 text-emerald-300">Open</span>
                    @endif
                </div>
                <div class="text-slate-400 text-sm mb-3">
                    🏢 {{ $job->company }} &nbsp;·&nbsp; 📍 {{ $job->location }} &nbsp;·&nbsp;
                    <span class="capitalize">{{ $job->type }}</span>
                    @if($job->salary_range)
                    &nbsp;·&nbsp; 💰 {{ $job->salary_range }}
                    @endif
                </div>

                <p class="text-slate-300 text-sm mb-3 line-clamp-2">{{ $job->description }}</p>

                {{-- Skills --}}
                @if($job->key_skills)
                <div class="flex flex-wrap gap-1.5">
                    @foreach($job->skillsArray() as $skill)
                    <span class="badge bg-slate-700/80 text-slate-300 border border-slate-600/50">{{ $skill }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Match Score + Apply --}}
            <div class="shrink-0 text-center sm:text-right sm:min-w-[120px]">
                <div class="text-3xl font-bold mono text-{{ $color }}-400">{{ $score }}%</div>
                <div class="text-slate-500 text-xs mb-3">Match Score</div>
                <div class="match-bar w-full sm:w-24 mx-auto sm:mx-0 mb-4">
                    <div class="match-fill bg-{{ $color }}-500" style="width: {{ $score }}%"></div>
                </div>

                @if($job->isOpen())
                <form method="POST" action="{{ route('seeker.jobs.apply', $job->id) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs py-2 px-4 w-full">
                        Apply Now
                    </button>
                </form>
                @else
                <span class="badge bg-red-900/50 text-red-300">Closed</span>
                @endif
            </div>
        </div>

        {{-- Experience + location info --}}
        @if($job->experience_required)
        <div class="mt-4 pt-4 border-t border-slate-700/50 text-xs text-slate-500">
            🎯 Experience required: {{ $job->experience_required }}
        </div>
        @endif
    </div>
    @empty
    <div class="card p-16 text-center">
        <div class="text-5xl mb-4">🔍</div>
        <h3 class="text-xl font-semibold mb-2">No jobs found</h3>
        <p class="text-slate-400">Update your profile to see matching jobs, or check back later.</p>
    </div>
    @endforelse
</div>
@endsection
