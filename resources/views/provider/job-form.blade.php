@extends('layouts.app')
@section('title', isset($job) ? 'Edit Job' : 'Post Job')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto fade-up">
    <div class="mb-8">
        <a href="{{ route('provider.jobs') }}" class="text-slate-400 hover:text-white text-sm mb-4 block">← Back to Jobs</a>
        <h1 class="text-2xl font-bold">{{ isset($job) ? 'Edit Job' : 'Post a New Job' }}</h1>
        <p class="text-slate-400 text-sm mt-1">Fill in the details below to {{ isset($job) ? 'update' : 'create' }} this posting</p>
    </div>

    <div class="card p-8">
        @if($errors->any())
        <div class="bg-red-900/40 border border-red-600/40 text-red-300 px-4 py-3 rounded-xl text-sm mb-6">
            @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST"
            action="{{ isset($job) ? route('provider.jobs.update', $job->id) : route('provider.jobs.store') }}"
            class="space-y-5">
            @csrf
            @if(isset($job)) @method('PUT') @endif

            <div>
                <label class="label">Job Title *</label>
                <input type="text" name="title" value="{{ old('title', $job->title ?? '') }}" class="input" placeholder="e.g. Senior Backend Developer" required>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label">Location *</label>
                    <input type="text" name="location" value="{{ old('location', $job->location ?? '') }}" class="input" placeholder="e.g. Kathmandu / Remote" required>
                </div>
                <div>
                    <label class="label">Job Type *</label>
                    <select name="type" class="input" required>
                        @foreach(['full-time','part-time','remote','contract','internship'] as $t)
                        <option value="{{ $t }}" {{ old('type', $job->type ?? '') === $t ? 'selected' : '' }} class="bg-slate-900">
                            {{ ucfirst($t) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label">Experience Required</label>
                    <input type="text" name="experience_required" value="{{ old('experience_required', $job->experience_required ?? '') }}" class="input" placeholder="e.g. 2+ years">
                </div>
                <div>
                    <label class="label">Salary Range</label>
                    <input type="text" name="salary_range" value="{{ old('salary_range', $job->salary_range ?? '') }}" class="input" placeholder="e.g. NPR 50,000–80,000">
                </div>
            </div>

            <div>
                <label class="label">Key Skills <span class="normal-case text-slate-500 font-normal">(comma separated — used for matching)</span></label>
                <input type="text" name="key_skills" value="{{ old('key_skills', $job->key_skills ?? '') }}" class="input" placeholder="e.g. Python, Django, REST API, PostgreSQL">
            </div>

            <div>
                <label class="label">Job Description *</label>
                <textarea name="description" rows="5" class="input resize-none" placeholder="Describe the role, responsibilities, and what a typical day looks like..." required>{{ old('description', $job->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="label">Requirements *</label>
                <textarea name="requirements" rows="4" class="input resize-none" placeholder="List the qualifications, experience, and technical requirements..." required>{{ old('requirements', $job->requirements ?? '') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center flex">
                    {{ isset($job) ? '💾 Update Job' : '🚀 Post Job' }}
                </button>
                <a href="{{ route('provider.jobs') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
