@extends('layouts.app')
@section('title', 'My Profile')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto fade-up">
    <h1 class="text-2xl font-bold mb-2">My Profile</h1>
    <p class="text-slate-400 text-sm mb-8">Keep your profile updated to improve job match accuracy.</p>

    {{-- Profile Summary Card --}}
    @if($user->profile_summary)
    <div class="card p-5 mb-6 border-indigo-600/30 bg-indigo-900/10">
        <div class="text-xs text-indigo-400 font-semibold uppercase tracking-widest mb-2">🤖 Your AI Profile Summary</div>
        <p class="text-slate-300 text-sm italic">{{ $user->profile_summary }}</p>
    </div>
    @endif

    <div class="card p-8">
        <form method="POST" action="{{ route('seeker.profile.update') }}" class="space-y-5">
            @csrf
            @if($errors->any())
            <div class="bg-red-900/40 border border-red-600/40 text-red-300 px-4 py-3 rounded-xl text-sm">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                </div>
                <div>
                    <label class="label">Location</label>
                    <input type="text" name="location" value="{{ old('location', $user->location) }}" class="input" placeholder="e.g. Kathmandu, Nepal">
                </div>
            </div>

            <div>
                <label class="label">Skills <span class="normal-case text-slate-500 font-normal">(comma separated)</span></label>
                <input type="text" name="skills" value="{{ old('skills', $user->skills) }}" class="input" placeholder="e.g. Python, Machine Learning, Django, SQL">
                <p class="text-slate-500 text-xs mt-1">These are used to match you with relevant jobs</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label">Education</label>
                    <input type="text" name="education" value="{{ old('education', $user->education) }}" class="input" placeholder="e.g. BSc Computer Science">
                </div>
                <div>
                    <label class="label">Years of Experience</label>
                    <input type="number" name="experience_years" value="{{ old('experience_years', $user->experience_years) }}" class="input" placeholder="0" min="0" max="50">
                </div>
            </div>

            <div>
                <label class="label">Preferred Role</label>
                <input type="text" name="preferred_role" value="{{ old('preferred_role', $user->preferred_role) }}" class="input" placeholder="e.g. Backend Developer, Data Analyst">
            </div>

            <button type="submit" class="btn-primary w-full justify-center flex">
                💾 Save & Regenerate Profile
            </button>
        </form>
    </div>

    {{-- Info box --}}
    <div class="mt-4 card p-4 bg-slate-800/30">
        <p class="text-slate-500 text-xs">
            <span class="text-indigo-400 font-semibold">How it works:</span>
            Your profile is converted to natural language text and used to find matching jobs using keyword scoring across skills, experience, and location.
        </p>
    </div>
</div>
@endsection
