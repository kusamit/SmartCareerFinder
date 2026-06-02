@extends('layouts.app')
@section('title', 'My Applications')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@section('content')
<div class="fade-up">
    <h1 class="text-2xl font-bold mb-2">My Applications</h1>
    <p class="text-slate-400 text-sm mb-8">Track all your job applications in one place</p>

    @forelse($applications as $app)
    @php
        $statusColor = ['applied'=>['bg'=>'bg-blue-900/50','text'=>'text-blue-300'],'reviewed'=>['bg'=>'bg-amber-900/50','text'=>'text-amber-300'],'shortlisted'=>['bg'=>'bg-emerald-900/50','text'=>'text-emerald-300'],'rejected'=>['bg'=>'bg-red-900/50','text'=>'text-red-300']][$app->status] ?? ['bg'=>'bg-slate-700','text'=>'text-slate-300'];
        $score = $app->match_score;
        $color = $score >= 70 ? 'emerald' : ($score >= 40 ? 'amber' : 'slate');
    @endphp
    <div class="card p-5 mb-3 flex items-center gap-4 group hover:border-slate-600 transition-all">
        <div class="flex-1 min-w-0">
            <div class="font-semibold">{{ $app->job->title }}</div>
            <div class="text-slate-400 text-sm">{{ $app->job->company }} · {{ $app->job->location }}</div>
            <div class="text-slate-500 text-xs mt-1">Applied {{ $app->created_at->diffForHumans() }}</div>
        </div>

        <div class="text-center shrink-0">
            <div class="text-xs text-slate-500 mb-1">Match</div>
            <div class="text-lg font-bold mono text-{{ $color }}-400">{{ $score }}%</div>
        </div>

        <div class="shrink-0">
            <span class="badge {{ $statusColor['bg'] }} {{ $statusColor['text'] }} capitalize">{{ $app->status }}</span>
        </div>

        @if(!$app->job->isOpen())
        <span class="badge bg-red-900/40 text-red-400 text-xs">Job Closed</span>
        @endif
    </div>
    @empty
    <div class="card p-16 text-center">
        <div class="text-5xl mb-4">📋</div>
        <h3 class="text-xl font-semibold mb-2">No applications yet</h3>
        <a href="{{ route('seeker.jobs') }}" class="btn-primary inline-flex">Find Jobs Now</a>
    </div>
    @endforelse
</div>
@endsection
