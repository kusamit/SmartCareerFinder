@extends('layouts.app')
@section('title', 'My Profile')

@section('nav_links')
<a href="{{ route('seeker.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('seeker.jobs') }}" class="nav-link">Find Jobs</a>
<a href="{{ route('seeker.applications') }}" class="nav-link">Applications</a>
<a href="{{ route('seeker.profile') }}" class="nav-link">Profile</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/seeker-profile.css') }}">
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
                <p style="color:rgba(255,255,255,0.5); font-size:12px; margin-top:2px;">{{ strip_tags($user->preferred_role) }}</p>
                @endif
            </div>
            {{-- View Profile Toggle --}}
            <div style="margin-left:auto; flex-shrink:0;">
                <a href="{{ route('seeker.profile.view') }}"
                   style="display:inline-flex; align-items:center; gap:7px; padding:9px 20px;
                          background:rgba(255,255,255,0.1); border:1.5px solid rgba(255,255,255,0.2);
                          color:#fff !important; font-size:12.5px; font-weight:700;
                          border-radius:12px; text-decoration:none; transition:all 0.22s;
                          backdrop-filter:blur(4px);"
                   onmouseover="this.style.background='rgba(255,255,255,0.18)'"
                   onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    &#128065; View Profile
                </a>
            </div>
        </div>
    </div>

    {{-- ===== AI PROFILE SUMMARY ===== --}}
{{--
    @if($user->profile_summary)
    <div class="ai-summary-card" style="margin-bottom: 20px;">
        <div class="ai-badge">AI Profile Summary</div>
        <p class="ai-summary-text">{{ $user->profile_summary }}</p>
    </div>
    @endif
--}}

    {{-- ===== FORM CARD ===== --}}
    <div class="form-card">

        @if($errors->any())
        <div style="padding: 20px 36px 0;">
            <div class="form-error">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
        </div>
        @endif

        <form id="profileForm" method="POST" action="{{ route('seeker.profile.update') }}">
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
                        <label class="field-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="field-input" placeholder="e.g. +977 9800000000">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="field-input" required placeholder="e.g. user@example.com">
                    </div>
                </div>
                <div class="field-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <label class="field-label" style="margin-bottom:0;">Education History</label>
                        <a href="{{ route('seeker.education') }}" class="btn" style="padding: 6px 14px; font-size:12px; font-weight:600; text-decoration:none; background:#6366f1; color:#fff; border-radius:8px;">Manage Education</a>
                    </div>
                    
                    @if($user->educations->isNotEmpty())
                        <div style="display:grid; gap:8px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                            @foreach($user->educations as $edu)
                                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:8px; margin-bottom:8px; last-child: {border:none, padding:0, margin:0}">
                                    <div>
                                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">{{ $edu->school }}</div>
                                        <div style="color:#64748b; font-size:12.5px;">{{ $edu->degree }}, {{ $edu->field_of_study }}</div>
                                    </div>
                                    <div style="font-size:12px; color:#94a3b8; font-weight:600;">
                                        {{ $edu->start_year }} – {{ $edu->end_year }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center; padding:24px; border: 1.5px dashed #cbd5e1; border-radius:12px; background:#fff;">
                            <div style="font-size:13.5px; color:#64748b; margin-bottom:10px;">No education history added yet.</div>
                            <a href="{{ route('seeker.education') }}" style="color:#6366f1; font-size:13px; font-weight:600; text-decoration:none;">+ Add Education</a>
                        </div>
                    @endif
                </div>
                <div class="field-group">
                    <label class="field-label">
                        Experience
                        <span class="hint">List your skill experiences</span>
                    </label>
                    <input type="hidden" name="experience_years" id="experience_years" value="{{ old('experience_years', $user->experience_years) }}">
                    <div class="editor-wrapper">
                        <div id="experience_years-editor">{!! old('experience_years', $user->experience_years) !!}</div>
                    </div>
                    <div id="experience_years-preview" class="skills-tags-preview" style="display: none !important;">
                        @php
                            $rawExp = strip_tags($user->experience_years ?? '');
                            $rawExp = html_entity_decode($rawExp, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $expList = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $rawExp))));
                            if (count($expList) <= 1) {
                                $expList = array_filter(array_map('trim', explode(',', str_replace(['•', '●', '▪', '-', '*'], ',', $rawExp))));
                            }
                        @endphp
                        @foreach($expList as $item)
                            @if($item)
                                <span class="skill-tag">{{ $item }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">
                        Preferred Role
                        <span class="hint">List your preferred job roles</span>
                    </label>
                    <input type="hidden" name="preferred_role" id="preferred_role" value="{{ old('preferred_role', $user->preferred_role) }}">
                    <div class="editor-wrapper">
                        <div id="preferred_role-editor">{!! old('preferred_role', $user->preferred_role) !!}</div>
                    </div>
                    <div id="preferred_role-preview" class="skills-tags-preview" style="display: none !important;">
                        @foreach($user->preferredRoleArray() as $role)
                            <span class="skill-tag">{{ $role }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Skills --}}
            <div class="form-section">
                <div class="form-section-title">Skills</div>
                <div class="field-group">
                    <label class="field-label">
                        Your Skills
                        <span class="hint">List your skills, technologies, or tools</span>
                    </label>
                    <input type="hidden" name="skills" id="skills" value="{{ old('skills', $user->skills) }}">
                    <div class="editor-wrapper">
                        <div id="skills-editor">{!! old('skills', $user->skills) !!}</div>
                    </div>
                    <div id="skills-preview" class="skills-tags-preview" style="display: none !important;">
                        @foreach($user->skillsArray() as $sk)
                            <span class="skill-tag">{{ $sk }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Project / Portfolio --}}
                <div class="field-group" style="margin-top:24px;">
                    <label class="field-label">
                        Projects / Portfolio
                        <span class="hint">Describe your projects or add links</span>
                    </label>
                    <input type="hidden" name="portfolio" id="portfolio" value="{{ old('portfolio', $user->portfolio) }}">
                    <div class="editor-wrapper">
                        <div id="portfolio-editor">{!! old('portfolio', $user->portfolio) !!}</div>
                    </div>
                    {{-- Live portfolio indicator (hidden) --}}
                    <div style="display: none !important;">
                        <span id="portfolio-status-dot" style="width:8px;height:8px;border-radius:50%;background:{{ trim(strip_tags($user->portfolio ?? '')) ? '#10b981' : '#e11d48' }};flex-shrink:0;"></span>
                        <span id="portfolio-status-text" style="color:#64748b;">
                            @if(trim(strip_tags($user->portfolio ?? '')))
                                Portfolio content present — +10 pts in match score
                            @else
                                No portfolio content yet — add projects or links to unlock +10 pts
                            @endif
                        </span>
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

    {{-- Info box (hidden) --}}
    <div class="info-box" style="display: none !important; margin-top: 20px;">
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
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Helper: init a Quill editor with auto-generated toolbar in JS.
        function initQuill(editorSelector, placeholder, isPortfolio = false) {
            const editorEl = document.querySelector(editorSelector);
            if (!editorEl) return null;
            const savedHTML = editorEl.innerHTML.trim();
            editorEl.innerHTML = '';  // clean slate before init
            
            const toolbarOpts = isPortfolio 
                ? [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'clean']
                  ]
                : [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                  ];

            const q = new Quill(editorEl, {
                modules: {
                    toolbar: toolbarOpts
                },
                placeholder: placeholder,
                theme: 'snow'
            });
            
            if (savedHTML) {
                q.root.innerHTML = savedHTML;
            }
            return q;
        }

        const roleQuill = initQuill('#preferred_role-editor',
            'List preferred roles (e.g. Backend Developer, Frontend Developer, UI/UX Designer)...');

        const skillsQuill = initQuill('#skills-editor',
            'List your skills, technologies, frameworks, or tools (e.g. Python, Django, REST API, Docker)...');

        const expQuill = initQuill('#experience_years-editor',
            'List your skill experiences...');

        // Helper: parse Quill plain text into tag array
        function parseTagsFromQuill(quillInstance) {
            if (!quillInstance) return [];
            const text = quillInstance.getText();
            const cleanText = text.replace(/[\n\r]+/g, ',')
                                  .replace(/[•●▪\-*]+/g, ',')
                                  .trim();
            return cleanText.split(',')
                            .map(s => s.trim())
                            .filter(s => s.length > 0);
        }

        function renderTags(previewId, tagsArray) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.innerHTML = tagsArray.map(t =>
                    `<span class="skill-tag">${t}</span>`
                ).join('');
            }
        }

        // Dynamic preview — Skills
        if (skillsQuill) {
            skillsQuill.on('text-change', function () {
                renderTags('skills-preview', parseTagsFromQuill(skillsQuill));
            });
        }

        // Dynamic preview — Preferred Role
        if (roleQuill) {
            roleQuill.on('text-change', function () {
                renderTags('preferred_role-preview', parseTagsFromQuill(roleQuill));
            });
        }

        // Dynamic preview — Experience
        if (expQuill) {
            expQuill.on('text-change', function () {
                renderTags('experience_years-preview', parseTagsFromQuill(expQuill));
            });
        }

        const portfolioQuill = initQuill('#portfolio-editor',
            'Describe your projects or add links...', true);

        // Dynamic portfolio status indicator
        if (portfolioQuill) {
            portfolioQuill.on('text-change', function () {
                const hasContent = portfolioQuill.getText().trim().length > 0;
                const dot  = document.getElementById('portfolio-status-dot');
                const text = document.getElementById('portfolio-status-text');
                if (dot)  dot.style.background  = hasContent ? '#10b981' : '#e11d48';
                if (text) text.textContent = hasContent
                    ? 'Portfolio content present — +10 pts in match score'
                    : 'No portfolio content yet — add projects or links to unlock +10 pts';
            });
        }


        const form = document.getElementById('profileForm');
        form.addEventListener('submit', function (e) {
            if (roleQuill) document.getElementById('preferred_role').value  = roleQuill.root.innerHTML;
            if (skillsQuill) document.getElementById('skills').value          = skillsQuill.root.innerHTML;
            if (expQuill) document.getElementById('experience_years').value = expQuill.root.innerHTML;
            if (portfolioQuill) document.getElementById('portfolio').value        = portfolioQuill.root.innerHTML;
        });
    });
</script>
@endpush
