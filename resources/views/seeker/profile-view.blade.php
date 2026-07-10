@extends('layouts.app')
@section('title', 'My Profile — View')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile.view') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<style>
    body { background: #f1f4f9 !important; }
    .pv-wrapper { max-width: 860px; margin: 0 auto; padding: 0 20px 80px; }

    .pv-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #312e81 100%);
        border-radius: 28px; padding: 44px 48px; margin-bottom: 22px;
        position: relative; overflow: hidden;
        box-shadow: 0 24px 64px rgba(15,23,42,0.32);
    }
    .pv-hero::before { content:''; position:absolute; top:-90px; right:-70px; width:300px; height:300px; background:radial-gradient(circle,rgba(99,102,241,0.22) 0%,transparent 70%); border-radius:50%; }
    .pv-hero::after  { content:''; position:absolute; bottom:-80px; left:-50px; width:240px; height:240px; background:radial-gradient(circle,rgba(124,58,237,0.16) 0%,transparent 70%); border-radius:50%; }
    .pv-hero-inner { position:relative; z-index:2; display:flex; align-items:flex-start; gap:24px; flex-wrap:wrap; }
    .pv-avatar { width:80px; height:80px; border-radius:22px; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:900; color:#fff; flex-shrink:0; box-shadow:0 10px 32px rgba(99,102,241,0.45); letter-spacing:-2px; }
    .pv-name  { font-size:26px; font-weight:900; color:#fff; letter-spacing:-0.5px; margin-bottom:4px; }
    .pv-email { color:rgba(255,255,255,0.5); font-size:13px; }
    .pv-role-badge { display:inline-flex; align-items:center; gap:5px; margin:6px 4px 0 0; background:rgba(99,102,241,0.22); border:1px solid rgba(99,102,241,0.35); color:#a5b4fc; font-size:11.5px; font-weight:700; padding:3px 13px; border-radius:999px; }
    .pv-location { color:rgba(255,255,255,0.4); font-size:12px; margin-top:8px; display:flex; align-items:center; gap:5px; }
    .pv-hero-actions { display:flex; flex-direction:column; gap:10px; align-items:flex-end; flex-shrink:0; margin-left:auto; }
    .btn-edit { display:inline-flex; align-items:center; gap:7px; padding:10px 22px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff !important; font-size:13px; font-weight:700; border-radius:12px; text-decoration:none; white-space:nowrap; box-shadow:0 6px 20px rgba(99,102,241,0.35); transition:all 0.22s; }
    .btn-edit:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(99,102,241,0.45); }
    .pv-comp-wrap { background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.12); border-radius:14px; padding:13px 18px; display:flex; align-items:center; gap:12px; min-width:190px; }
    .pv-comp-label { color:rgba(255,255,255,0.55); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; white-space:nowrap; }
    .pv-comp-bar { flex:1; height:6px; background:rgba(255,255,255,0.15); border-radius:999px; overflow:hidden; }
    .pv-comp-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#6366f1,#8b5cf6); }
    .pv-comp-pct { color:#a5b4fc; font-size:13px; font-weight:800; white-space:nowrap; }

    .pv-ai-card { background:linear-gradient(135deg,rgba(99,102,241,0.07),rgba(124,58,237,0.05)); border:1.5px solid rgba(99,102,241,0.18); border-radius:20px; padding:22px 28px; margin-bottom:18px; position:relative; overflow:hidden; }
    .pv-ai-card::after { content:'🤖'; position:absolute; right:22px; top:50%; transform:translateY(-50%); font-size:48px; opacity:0.06; }
    .pv-ai-badge { display:inline-flex; align-items:center; gap:5px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; padding:3px 12px; border-radius:999px; margin-bottom:10px; }
    .pv-ai-text { color:#475569; font-size:13.5px; line-height:1.75; font-style:italic; }

    .pv-hint { background:linear-gradient(135deg,rgba(16,185,129,0.06),rgba(5,150,105,0.04)); border:1.5px solid rgba(16,185,129,0.2); border-radius:18px; padding:14px 20px; margin-bottom:18px; display:flex; align-items:center; gap:14px; }
    .pv-hint-text { font-size:13px; color:#475569; line-height:1.6; }
    .pv-hint-text strong { color:#059669; }

    .pv-card { background:#fff; border-radius:22px; box-shadow:0 4px 24px rgba(0,0,0,0.07); margin-bottom:16px; overflow:hidden; }
    .pv-card-header { padding:16px 28px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px; }
    .pv-card-icon { width:36px; height:36px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
    .pv-card-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:#64748b; }
    .pv-card-body  { padding:24px 28px; }

    .pv-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:20px; }
    @media (max-width:600px) { .pv-grid { grid-template-columns:1fr; } }
    .pv-field { display:flex; flex-direction:column; gap:6px; }
    .pv-field-label { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.1em; }
    .pv-field-value { font-size:15px; font-weight:600; color:#0f172a; line-height:1.5; }
    .pv-field-empty { color:#cbd5e1; font-style:italic; font-size:13px; font-weight:400; }

    .pv-rich { background:#f8fafc; border:1.5px solid #f1f5f9; border-radius:14px; padding:18px 22px; color:#334155; font-size:14px; line-height:1.8; }
    .pv-rich p { margin:0 0 8px; }
    .pv-rich p:last-child { margin-bottom:0; }
    .pv-rich ul, .pv-rich ol { margin:4px 0 8px 20px; padding:0; }
    .pv-rich li { margin-bottom:4px; }
    .pv-rich strong { color:#0f172a; }
    .pv-rich a { color:#6366f1; text-decoration:underline; word-break:break-all; }

    .pv-tags { display:flex; flex-wrap:wrap; gap:7px; }
    .pv-tag { display:inline-flex; align-items:center; padding:5px 14px; background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(124,58,237,0.08)); border:1px solid rgba(99,102,241,0.22); color:#6366f1; border-radius:999px; font-size:12.5px; font-weight:600; transition:all 0.18s; }
    .pv-tag:hover { background:linear-gradient(135deg,rgba(99,102,241,0.18),rgba(124,58,237,0.14)); }

    .pv-edu-item { display:flex; align-items:flex-start; gap:14px; padding:14px 18px; background:#f8fafc; border:1.5px solid #f1f5f9; border-radius:14px; transition:border-color 0.2s; margin-bottom:10px; }
    .pv-edu-item:last-child { margin-bottom:0; }
    .pv-edu-item:hover { border-color:#c7d2fe; }
    .pv-edu-dot { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; box-shadow:0 4px 12px rgba(99,102,241,0.25); }
    .pv-edu-body { flex:1; min-width:0; }
    .pv-edu-school { font-size:14px; font-weight:700; color:#0f172a; margin-bottom:2px; }
    .pv-edu-degree { font-size:12.5px; color:#6366f1; font-weight:600; }
    .pv-edu-field  { font-size:12px; color:#64748b; margin-top:2px; }
    .pv-edu-years  { font-size:12px; color:#94a3b8; font-weight:600; white-space:nowrap; flex-shrink:0; padding-top:2px; }

    .pv-portfolio-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 12px; border-radius:999px; font-size:11.5px; font-weight:700; }
    .badge-yes { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .badge-no  { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

    .pv-strength-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:9px; }
    .pv-strength-item { display:flex; align-items:center; gap:10px; padding:11px 13px; border-radius:12px; border:1.5px solid; }
    .strength-ok   { background:#f0fdf4; border-color:#bbf7d0; }
    .strength-miss { background:#fef2f2; border-color:#fecaca; }
    .pv-strength-label { font-size:12px; font-weight:700; }
    .pv-strength-sub   { font-size:10px; margin-top:1px; }
    .ok-text   { color:#15803d; }
    .miss-text { color:#b91c1c; }

    .pv-nudge { text-align:center; padding:32px 24px; border:2px dashed #e2e8f0; border-radius:16px; }
    .pv-nudge h3 { font-size:15px; font-weight:700; color:#0f172a; margin:8px 0 6px; }
    .pv-nudge p  { font-size:13px; color:#94a3b8; margin-bottom:14px; }
    .btn-nudge { display:inline-flex; align-items:center; gap:6px; padding:8px 20px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff !important; font-size:13px; font-weight:700; border-radius:10px; text-decoration:none; box-shadow:0 4px 14px rgba(99,102,241,0.3); transition:all 0.2s; }
    .btn-nudge:hover { transform:translateY(-1px); }
</style>
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
                    <span class="pv-role-badge">&#10022; {{ $role }}</span>
                    @endforeach
                </div>
                @endif
                @if($user->location)
                <div class="pv-location">&#128205; {{ $user->location }}</div>
                @endif
            </div>
            <div class="pv-hero-actions">
                <a href="{{ route('seeker.profile') }}" class="btn-edit">&#9998; Edit Profile</a>
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
        <div class="pv-ai-badge">&#129302; AI-Generated Profile Summary</div>
        <p class="pv-ai-text">{{ $user->profile_summary }}</p>
    </div>
    @endif

    {{-- HINT --}}
    <div class="pv-hint">
        <span style="font-size:26px; flex-shrink:0;">&#9889;</span>
        <div class="pv-hint-text">
            A complete profile earns up to <strong>100% match scores</strong> on relevant jobs.
            Your <strong>skills</strong>, <strong>experience</strong>, <strong>preferred role</strong>,
            <strong>location</strong> and <strong>portfolio</strong> all contribute to matching accuracy.
        </div>
    </div>

    {{-- PERSONAL INFO --}}
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-icon" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(124,58,237,0.1));">&#128100;</div>
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
            <div class="pv-card-icon" style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(217,119,6,0.1));">&#127891;</div>
            <div class="pv-card-title">Education History</div>
            <a href="{{ route('seeker.education') }}" style="margin-left:auto;font-size:12px;font-weight:700;color:#6366f1;text-decoration:none;">Manage &#8594;</a>
        </div>
        <div class="pv-card-body">
            @if($user->educations->isNotEmpty())
                @foreach($user->educations as $edu)
                <div class="pv-edu-item">
                    <div class="pv-edu-dot">&#127891;</div>
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
                    <div style="font-size:36px;">&#127891;</div>
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
            <div class="pv-card-icon" style="background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(37,99,235,0.1));">&#128188;</div>
            <div class="pv-card-title">Work Experience</div>
        </div>
        <div class="pv-card-body">
            @if($user->experience_years && trim(strip_tags($user->experience_years)))
                <div class="pv-rich">{!! $user->experience_years !!}</div>
            @else
                <div class="pv-nudge">
                    <div style="font-size:36px;">&#128188;</div>
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
            <div class="pv-card-icon" style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(109,40,217,0.1));">&#9889;</div>
            <div class="pv-card-title">Skills &amp; Technologies</div>
            @php $skillTags = $user->skillsArray(); @endphp
            <div style="margin-left:auto;font-size:12px;color:#94a3b8;font-weight:600;">{{ count($skillTags) }} skill{{ count($skillTags) != 1 ? 's' : '' }}</div>
        </div>
        <div class="pv-card-body">
            @if($user->skills && trim(strip_tags($user->skills)))
                <div class="pv-rich" style="margin-bottom:18px;">{!! $user->skills !!}</div>
                @if($skillTags)
                <div>
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
                    <div style="font-size:36px;">&#9889;</div>
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
                    <div style="font-size:36px;">&#128193;</div>
                    <h3>No portfolio added</h3>
                    <p>Add your GitHub, Behance, Dribbble links or project descriptions to earn <strong>+10 bonus match points</strong>.</p>
                    <a href="{{ route('seeker.profile') }}" class="btn-nudge">+ Add Portfolio</a>
                </div>
            @endif
        </div>
    </div>

    {{-- CV --}}
    @if($user->cv_path)
    <div class="pv-card">
        <div class="pv-card-header">
            <div class="pv-card-title">Uploaded CV / R&eacute;sum&eacute;</div>
        </div>
        <div class="pv-card-body" style="padding:20px 28px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:14px;margin-bottom:3px;">CV on file</div>
                    <a href="{{ asset('storage/' . $user->cv_path) }}" target="_blank" style="color:#6366f1;font-size:13px;font-weight:700;text-decoration:none;">View / Download &#8599;</a>
                    <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Used for CV-based job matching</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PROFILE STRENGTH --}}
    <div class="pv-card">
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
        <a href="{{ route('seeker.profile') }}" class="btn-edit" style="font-size:14px;padding:13px 32px;">&#9998; Edit My Profile</a>
        <a href="{{ route('seeker.jobs') }}"
           style="display:inline-flex;align-items:center;gap:7px;padding:13px 28px;background:#f1f5f9;color:#475569;font-size:14px;font-weight:700;border-radius:14px;text-decoration:none;transition:all 0.2s;"
           onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            &#128269; Find Jobs &#8594;
        </a>
    </div>

</div>
@endsection
