@extends('layouts.app')
@section('title', 'My Profile')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<style>
    body { background: #f1f4f9 !important; }

    .profile-page-wrapper {
        max-width: 820px;
        margin: 0 auto;
        padding: 0 20px 60px;
    }

    /* ===== HERO ===== */
    .profile-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
        border-radius: 24px;
        padding: 40px 44px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(15,23,42,0.3);
    }
    .profile-hero::before {
        content: '';
        position: absolute;
        top: -80px; right: -60px;
        width: 280px; height: 280px;
        background: rgba(99,102,241,0.15);
        border-radius: 50%;
    }
    .profile-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 220px; height: 220px;
        background: rgba(124,58,237,0.12);
        border-radius: 50%;
    }
    .profile-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 24px; }
    .profile-avatar {
        width: 72px; height: 72px;
        border-radius: 20px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; font-weight: 800; color: #fff;
        box-shadow: 0 8px 24px rgba(99,102,241,0.4);
        flex-shrink: 0;
        letter-spacing: -1px;
    }
    .profile-hero h1 { font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 4px; letter-spacing: -0.3px; }
    .profile-hero p { color: rgba(255,255,255,0.6); font-size: 13px; }
    .profile-completeness {
        margin-left: auto;
        text-align: center;
        flex-shrink: 0;
    }
    .completeness-ring {
        width: 64px; height: 64px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: conic-gradient(#6366f1 0deg, rgba(255,255,255,0.15) 0deg);
        position: relative;
        margin: 0 auto 6px;
    }
    .completeness-inner {
        width: 48px; height: 48px;
        background: rgba(15,23,42,0.5);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; color: #fff;
    }
    .completeness-label { color: rgba(255,255,255,0.5); font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; }

    /* ===== AI SUMMARY ===== */
    .ai-summary-card {
        background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(124,58,237,0.06));
        border: 1.5px solid rgba(99,102,241,0.2);
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .ai-summary-card::before {
        content: '🤖';
        position: absolute;
        right: 20px; top: 50%;
        transform: translateY(-50%);
        font-size: 40px;
        opacity: 0.07;
    }
    .ai-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.1em;
        padding: 3px 10px;
        border-radius: 999px;
        margin-bottom: 10px;
    }
    .ai-summary-text { color: #475569; font-size: 13.5px; line-height: 1.7; font-style: italic; }

    /* ===== FORM CARD ===== */
    .form-card {
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        overflow: hidden;
    }
    .form-section {
        padding: 28px 36px;
        border-bottom: 1px solid #f1f5f9;
    }
    .form-section:last-child { border-bottom: none; }
    .form-section-title {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.12em;
        color: #94a3b8; margin-bottom: 20px;
        display: flex; align-items: center; gap: 8px;
    }
    .form-section-title::after {
        content: ''; flex: 1; height: 1px; background: #f1f5f9;
    }

    /* ===== FIELDS ===== */
    .field-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
    .field-group:last-child { margin-bottom: 0; }
    .field-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 600px) { .field-grid-2 { grid-template-columns: 1fr; } }

    .field-label {
        font-size: 13px; font-weight: 600; color: #334155;
        display: flex; align-items: center; gap: 6px;
    }
    .field-label .hint { font-weight: 400; color: #94a3b8; font-size: 12px; }

    .field-input {
        width: 100%; padding: 13px 16px;
        border: 1.5px solid #e2e8f0; border-radius: 14px;
        background: #f8fafc; color: #0f172a;
        font-size: 14px; font-family: 'Sora', sans-serif;
        outline: none; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .field-input::placeholder { color: #94a3b8; }
    .field-input:focus {
        border-color: #6366f1; background: #fff;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
    }
    .field-input:hover:not(:focus) { border-color: #c7d2fe; }

    /* ===== SKILLS TAGS ===== */
    .skills-tags-preview { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; min-height: 28px; }
    .skill-tag {
        display: inline-flex; align-items: center;
        padding: 4px 12px;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(124,58,237,0.12));
        border: 1px solid rgba(99,102,241,0.25);
        color: #6366f1; border-radius: 999px;
        font-size: 12px; font-weight: 600;
    }

    /* ===== FOOTER ===== */
    .form-actions {
        padding: 24px 36px; background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex; gap: 12px; align-items: center;
    }
    .btn-submit {
        flex: 1; display: inline-flex; align-items: center;
        justify-content: center; gap: 8px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff !important; font-weight: 700; font-size: 15px;
        border-radius: 14px; border: none; cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 8px 24px rgba(79,70,229,0.3);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(79,70,229,0.4); }
    .btn-submit:active { transform: translateY(0); }

    /* ===== INFO BOX ===== */
    .info-box {
        margin-top: 20px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px 22px;
        display: flex; align-items: flex-start; gap: 12px;
    }
    .info-icon {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(124,58,237,0.1));
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .info-box p { color: #64748b; font-size: 13px; line-height: 1.6; }
    .info-box strong { color: #6366f1; }

    /* ===== ERROR ===== */
    .form-error {
        background: #fef2f2; border: 1.5px solid #fecaca;
        color: #b91c1c; padding: 14px 18px;
        border-radius: 14px; font-size: 13px; margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="profile-page-wrapper fade-up">

    {{-- ===== HERO ===== --}}
    <div class="profile-hero" style="margin-bottom: 24px;">
        <div class="profile-hero-inner">
            <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            <div>
                <h1>{{ $user->name }}</h1>
                <p>{{ $user->email }}</p>
                @if($user->preferred_role)
                <p style="color:rgba(255,255,255,0.5); font-size:12px; margin-top:2px;">{{ $user->preferred_role }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== AI PROFILE SUMMARY ===== --}}
    @if($user->profile_summary)
    <div class="ai-summary-card" style="margin-bottom: 20px;">
        <div class="ai-badge">AI Profile Summary</div>
        <p class="ai-summary-text">{{ $user->profile_summary }}</p>
    </div>
    @endif

    {{-- ===== FORM CARD ===== --}}
    <div class="form-card">

        @if($errors->any())
        <div style="padding: 20px 36px 0;">
            <div class="form-error">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('seeker.profile.update') }}">
            @csrf

            {{-- Basic Info --}}
            <div class="form-section">
                <div class="form-section-title">Personal Information</div>
                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="field-input" required placeholder="Your full name">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Location</label>
                        <input type="text" name="location" value="{{ old('location', $user->location) }}" class="field-input" placeholder="e.g. Kathmandu, Nepal">
                    </div>
                </div>
                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label">Education</label>
                        <input type="text" name="education" value="{{ old('education', $user->education) }}" class="field-input" placeholder="e.g. BSc Computer Science">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Years of Experience</label>
                        <input type="number" name="experience_years" value="{{ old('experience_years', $user->experience_years) }}" class="field-input" placeholder="0" min="0" max="50">
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Preferred Role</label>
                    <input type="text" name="preferred_role" value="{{ old('preferred_role', $user->preferred_role) }}" class="field-input" placeholder="e.g. Backend Developer, Data Analyst">
                </div>
            </div>

            {{-- Skills --}}
            <div class="form-section">
                <div class="form-section-title">Skills</div>
                <div class="field-group">
                    <label class="field-label">
                        Your Skills
                        <span class="hint">(comma separated — used for AI job matching)</span>
                    </label>
                    <input type="text" id="skillsInput" name="skills"
                           value="{{ old('skills', $user->skills) }}"
                           class="field-input"
                           placeholder="e.g. Python, Machine Learning, Django, SQL"
                           autocomplete="off">
                    <div id="skills-preview" class="skills-tags-preview">
                        @foreach(array_filter(array_map('trim', explode(',', old('skills', $user->skills ?? '')))) as $sk)
                            <span class="skill-tag">{{ $sk }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    Save & Regenerate Profile
                </button>
            </div>
        </form>
    </div>

    {{-- Info box --}}
    <div class="info-box" style="margin-top: 20px;">
        <div class="info-icon">i</div>
        <p>
            <strong>How it works:</strong>
            Your profile is converted to natural language and passed through our AI matching engine.
            Keeping your skills and experience updated directly improves your job match scores.
        </p>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const skillsInput = document.getElementById('skillsInput');
    const preview = document.getElementById('skills-preview');

    if (skillsInput) {
        skillsInput.addEventListener('input', function () {
            const skills = this.value.split(',').map(s => s.trim()).filter(Boolean);
            preview.innerHTML = skills.map(skill =>
                `<span class="skill-tag">${skill}</span>`
            ).join('');
        });
    }
</script>
@endpush
