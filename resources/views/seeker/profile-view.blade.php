@extends('layouts.app')
@section('title', 'My Profile')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seeker-profile-view.css') }}">
@endpush

@section('content')
<div class="pv-wrapper fade-up">

    {{-- HERO --}}
    <div class="pv-hero" style="margin-bottom:20px;">
        <div class="pv-hero-inner">
            <div class="pv-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            <div style="flex:1; min-width:0;">
                <div class="pv-name">{{ $user->name }}</div>
                <div class="pv-email">{{ $user->email }}</div>
                @if($user->preferred_role)
                <div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:4px;">
                    @foreach($user->preferredRoleArray() as $role)
                    <span class="pv-role-badge">{{ $role }}</span>
                    @endforeach
                </div>
                @endif
                @if($user->location)
                <div class="pv-location">{{ $user->location }}</div>
                @endif
            </div>
            <div class="pv-hero-actions">
                <a href="{{ route('seeker.profile') }}" class="btn-edit">Edit Profile</a>
                @php
                    $fields = ['name','skills','education','experience_years','preferred_role','location','profile_summary'];
                    $filled = collect($fields)->filter(fn($f) => !empty($user->$f))->count();
                    $pct = intval(($filled / count($fields)) * 100);
                @endphp
                <div class="pv-comp-wrap">
                    <div class="pv-comp-label">Complete</div>
                    <div class="pv-comp-bar"><div class="pv-comp-fill" style="width:{{ $pct }}%"></div></div>
                    <div class="pv-comp-pct">{{ $pct }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- AI SUMMARY --}}
    @if($user->profile_summary)
    <div class="pv-ai-card">
        <div class="pv-ai-badge">AI-Generated Profile Summary</div>
        <p class="pv-ai-text">{{ $user->profile_summary }}</p>
    </div>
    @endif

    {{-- HINT --}}
    <div class="pv-hint">
        <div class="pv-hint-text">
            A complete profile earns up to <strong>100% match scores</strong> on relevant jobs.
            <strong>Skills</strong>, <strong>experience</strong>, <strong>preferred role</strong>,
            <strong>location</strong> and <strong>portfolio</strong> all contribute to matching accuracy.
        </div>
    </div>

    {{-- PERSONAL INFO --}}
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Personal Information</div>
        </div>
        <div class="pv-card-body">
            <div class="pv-grid">
                <div class="pv-field">
                    <div class="pv-field-label">Full Name</div>
                    <div class="pv-field-value">{{ $user->name }}</div>
                </div>
                <div class="pv-field">
                    <div class="pv-field-label">Email Address</div>
                    <div class="pv-field-value" style="word-break:break-all;">{{ $user->email }}</div>
                </div>
                <div class="pv-field">
                    <div class="pv-field-label">Location</div>
                    <div class="pv-field-value">
                        @if($user->location) {{ $user->location }}
                        @else <span class="pv-field-empty">Not specified</span> @endif
                    </div>
                </div>
                <div class="pv-field">
                    <div class="pv-field-label">Phone</div>
                    <div class="pv-field-value">
                        @if($user->phone) {{ $user->phone }}
                        @else <span class="pv-field-empty">Not provided</span> @endif
                    </div>
                </div>
            </div>
            <div class="pv-field">
                <div class="pv-field-label" style="margin-bottom:10px;">Preferred Roles</div>
                @php $roles = $user->preferredRoleArray(); @endphp
                @if($roles)
                <div class="pv-tags">
                    @foreach($roles as $role)
                    <span class="pv-tag" style="background:linear-gradient(135deg,rgba(16,185,129,0.1),rgba(5,150,105,0.08));border-color:rgba(16,185,129,0.25);color:#059669;">{{ $role }}</span>
                    @endforeach
                </div>
                @else <span class="pv-field-empty">No preferred roles added yet.</span> @endif
            </div>
        </div>
    </div>

    {{-- EDUCATION --}}
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Education History</div>
            <a href="{{ route('seeker.education') }}" style="margin-left:auto;font-size:12px;font-weight:700;color:#6366f1;text-decoration:none;">Manage</a>
        </div>
        <div class="pv-card-body">
            @if($user->educations->isNotEmpty())
                @foreach($user->educations as $edu)
                <div class="pv-edu-item">
                    <div class="pv-edu-body">
                        <div class="pv-edu-school">{{ $edu->school }}</div>
                        <div class="pv-edu-degree">{{ $edu->degree }}</div>
                        <div class="pv-edu-field">{{ $edu->field_of_study }}</div>
                    </div>
                    <div class="pv-edu-years">{{ $edu->start_year }} &ndash; {{ $edu->end_year }}</div>
                </div>
                @endforeach
            @else
                <div class="pv-nudge">
                    <h3>No education history</h3>
                    <p>Add your degrees and academic background.</p>
                    <a href="{{ route('seeker.education') }}" class="btn-nudge">+ Add Education</a>
                </div>
            @endif
        </div>
    </div>

    {{-- WORK EXPERIENCE --}}
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Work Experience</div>
        </div>
        <div class="pv-card-body">
            @if($user->experience_years && trim(strip_tags($user->experience_years)))
                <div class="pv-rich">{!! $user->experience_years !!}</div>
            @else
                <div class="pv-nudge">
                    <h3>No experience details yet</h3>
                    <p>Describe your work history, roles, and key achievements.</p>
                    <a href="{{ route('seeker.profile') }}" class="btn-nudge">+ Add Experience</a>
                </div>
            @endif
        </div>
    </div>

    {{-- SKILLS --}}
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Skills &amp; Technologies</div>
            @php $skillTags = $user->skillsArray(); @endphp
            <div style="margin-left:auto;font-size:12px;color:#94a3b8;font-weight:600;">{{ count($skillTags) }} skill{{ count($skillTags) != 1 ? 's' : '' }}</div>
        </div>
        <div class="pv-card-body">
            @if($user->skills && trim(strip_tags($user->skills)))
                <div class="pv-rich" style="margin-bottom:18px;">{!! $user->skills !!}</div>
                @if($skillTags)
                <div style="display: none !important;">
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:10px;">Recognized Skill Tags (used in AI matching)</div>
                    <div class="pv-tags">
                        @foreach($skillTags as $sk)
                        <span class="pv-tag">{{ $sk }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
                <div class="pv-nudge">
                    <h3>No skills listed</h3>
                    <p>Add your technical skills, tools, and technologies.</p>
                    <a href="{{ route('seeker.profile') }}" class="btn-nudge">+ Add Skills</a>
                </div>
            @endif
        </div>
    </div>

    {{-- PORTFOLIO --}}
    @php $hasPortfolio = !empty(trim(strip_tags($user->portfolio ?? ''))); @endphp
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Projects &amp; Portfolio</div>
            <span class="pv-portfolio-badge {{ $hasPortfolio ? 'badge-yes' : 'badge-no' }}" style="margin-left:auto;">
                {{ $hasPortfolio ? '+10 Match Points' : 'Not Added' }}
            </span>
        </div>
        <div class="pv-card-body">
            @if($hasPortfolio)
                <div class="pv-rich">{!! $user->portfolio !!}</div>
            @else
                <div class="pv-nudge">
                    <h3>No portfolio added</h3>
                    <p>Add your GitHub, Behance, Dribbble links or project descriptions to earn <strong>+10 bonus match points</strong>.</p>
                    <a href="{{ route('seeker.profile') }}" class="btn-nudge">+ Add Portfolio</a>
                </div>
            @endif
        </div>
    </div>

    {{-- GENERATED CV --}}
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Curriculum Vitae</div>
        </div>
        <div class="pv-card-body" style="padding:20px 28px;">
            <a href="{{ route('seeker.profile.cv') }}" target="_blank" style="display:inline-flex;align-items:center;padding:10px 28px;background:#6366f1;color:#fff;font-size:14px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 3px 10px rgba(99,102,241,0.35);transition:background 0.2s;" onmouseover="this.style.background='#4f46e5';" onmouseout="this.style.background='#6366f1';">
                CV
            </a>
        </div>
    </div>


    {{-- PROFILE STRENGTH --}}
    <div class="pv-card" style="display: none;">
        <div class="pv-card-header">
            <div class="pv-card-title">Profile Strength Breakdown</div>
            <div style="margin-left:auto;font-size:13px;font-weight:800;color:{{ $pct >= 70 ? '#059669' : ($pct >= 40 ? '#d97706' : '#dc2626') }};">{{ $pct }}% Complete</div>
        </div>
        <div class="pv-card-body">
            @php
                $checks = [
                    ['label'=>'Name',           'ok'=> !empty($user->name)],
                    ['label'=>'Skills',         'ok'=> !empty(trim(strip_tags($user->skills ?? '')))],
                    ['label'=>'Experience',     'ok'=> !empty(trim(strip_tags($user->experience_years ?? '')))],
                    ['label'=>'Preferred Role', 'ok'=> !empty(trim(strip_tags($user->preferred_role ?? '')))],
                    ['label'=>'Location',       'ok'=> !empty($user->location)],
                    ['label'=>'Portfolio',      'ok'=> $hasPortfolio],
                    ['label'=>'AI Summary',     'ok'=> !empty($user->profile_summary)],
                    ['label'=>'Education',      'ok'=> $user->educations->isNotEmpty()],
                    ['label'=>'Phone',          'ok'=> !empty($user->phone)],
                    ['label'=>'CV on File',     'ok'=> !empty($user->cv_path)],
                ];
            @endphp
            <div class="pv-strength-grid">
                @foreach($checks as $c)
                <div class="pv-strength-item {{ $c['ok'] ? 'strength-ok' : 'strength-miss' }}">
                    <div>
                        <div class="pv-strength-label {{ $c['ok'] ? 'ok-text' : 'miss-text' }}">{{ $c['label'] }}</div>
                        <div class="pv-strength-sub {{ $c['ok'] ? 'ok-text' : 'miss-text' }}">{{ $c['ok'] ? 'Filled' : 'Missing' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- BOTTOM CTA --}}
    <div style="text-align:center;padding:8px 0 20px;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
        <a href="{{ route('seeker.profile') }}" class="btn-edit" style="font-size:14px;padding:13px 32px;">Edit My Profile</a>
        <a href="{{ route('seeker.jobs') }}"
           style="display:inline-flex;align-items:center;gap:7px;padding:12px 28px;background:transparent;color:#6366f1;border:2px solid #6366f1;font-size:14px;font-weight:700;border-radius:14px;text-decoration:none;transition:all 0.2s;box-sizing:border-box;"
           onmouseover="this.style.background='#6366f1'; this.style.color='#fff';"
           onmouseout="this.style.background='transparent'; this.style.color='#6366f1';">
            Find Jobs
        </a>
    </div>

</div>
@endsection
