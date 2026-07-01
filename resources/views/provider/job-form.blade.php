@extends('layouts.app')
@section('title', isset($job) ? 'Edit Job' : 'Post Job')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    /* ===== PAGE BACKGROUND GLOW ===== */
    body { background: #f1f4f9 !important; }

    .form-page-wrapper {
        max-width: 820px;
        margin: 0 auto;
        padding: 0 20px 60px;
    }

    /* ===== HERO HEADER ===== */
    .form-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 60%, #6366f1 100%);
        border-radius: 24px;
        padding: 40px 44px 36px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;

    }
    .form-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 260px; height: 260px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .form-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .form-hero-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: rgba(255,255,255,0.7);
        font-size: 13px;
        text-decoration: none;
        margin-bottom: 20px;
        transition: color 0.2s;
        position: relative;
        z-index: 1;
    }
    .form-hero-back:hover { color: #fff; }
    .form-hero-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
        position: relative; z-index: 1;
        backdrop-filter: blur(8px);
    }
    .form-hero h1 {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
        position: relative; z-index: 1;
    }
    .form-hero p {
        color: rgba(255,255,255,0.7);
        font-size: 14px;
        position: relative; z-index: 1;
    }

    /* ===== CARD ===== */
    .form-card {
        background: #ffffff;
        border-radius: 24px;

        overflow: hidden;
    }

    /* ===== SECTION DIVIDER ===== */
    .form-section {
        padding: 28px 36px;
        border-bottom: 1px solid #f1f5f9;
    }
    .form-section:last-child { border-bottom: none; }

    .form-section-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f1f5f9;
    }

    /* ===== FIELD GROUP ===== */
    .field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 20px;
    }
    .field-group:last-child { margin-bottom: 0; }

    .field-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .field-label .req { color: #6366f1; }
    .field-label .hint {
        font-weight: 400;
        color: #94a3b8;
        font-size: 12px;
    }

    /* ===== INPUTS ===== */
    .field-input {
        width: 100%;
        padding: 13px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        color: #0f172a;
        font-size: 14px;
        font-family: 'Sora', sans-serif;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .field-input::placeholder { color: #94a3b8; }
    .field-input:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }
    .field-input:hover:not(:focus) { border-color: #c7d2fe; }

    select.field-input { cursor: pointer; }

    /* ===== GRID ===== */
    .field-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 600px) { .field-grid-2 { grid-template-columns: 1fr; } }

    /* ===== SKILLS TAGS ===== */
    .skills-tags-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        min-height: 28px;
    }
    .skill-tag {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(124,58,237,0.12));
        border: 1px solid rgba(99,102,241,0.25);
        color: #6366f1;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
    }

    /* ===== QUILL EDITOR ===== */
    .editor-wrapper {
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .editor-wrapper:focus-within {
        border-color: #6366f1;
    }
    .ql-toolbar.ql-snow {
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        background: #f8fafc;
        padding: 8px 12px;
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 2px;
    }
    .ql-toolbar.ql-snow .ql-formats { margin-right: 8px; }
    .ql-container.ql-snow {
        border: none !important;
        background: #fff;
        color: #0f172a;
        font-family: 'Sora', sans-serif;
        font-size: 14px;
        min-height: 160px;
    }
    .ql-editor { min-height: 160px; padding: 14px 16px; line-height: 1.7; }
    .ql-editor.ql-blank::before {
        color: #94a3b8 !important;
        font-style: normal !important;
        left: 16px;
    }
    .ql-snow .ql-picker { color: #475569; }
    .ql-snow .ql-stroke { stroke: #475569; }
    .ql-snow .ql-fill { fill: #475569; }
    .ql-snow button:hover .ql-stroke,
    .ql-snow .ql-picker-label:hover .ql-stroke { stroke: #6366f1 !important; }
    .ql-snow button:hover .ql-fill { fill: #6366f1 !important; }
    .ql-snow button.ql-active .ql-stroke { stroke: #6366f1 !important; }
    .ql-snow button.ql-active .ql-fill { fill: #6366f1 !important; }

    /* ===== ERROR ===== */
    .form-error {
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        color: #b91c1c;
        padding: 14px 18px;
        border-radius: 14px;
        font-size: 13px;
        margin-bottom: 24px;
    }

    /* ===== FOOTER ACTIONS ===== */
    .form-actions {
        padding: 24px 36px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .btn-submit {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff !important;
        font-weight: 700;
        font-size: 15px;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 8px 24px rgba(79,70,229,0.3);
        letter-spacing: -0.1px;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(79,70,229,0.4);
    }
    .btn-submit:active { transform: translateY(0); }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 24px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #64748b !important;
        font-weight: 600;
        font-size: 14px;
        border-radius: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-cancel:hover {
        border-color: #c7d2fe;
        color: #4f46e5 !important;
        background: #f5f3ff;
    }
</style>
@endpush

@section('content')
<div class="form-page-wrapper fade-up">

    {{-- ===== HERO HEADER ===== --}}
    <div class="form-hero">
        <a href="{{ route('provider.jobs') }}" class="form-hero-back">
            ← Back to My Jobs
        </a>
        <h1>{{ isset($job) ? 'Edit Job Posting' : 'Post a New Job' }}</h1>
        <p>Fill in the details below to {{ isset($job) ? 'update' : 'publish' }} your job posting</p>
    </div>

    {{-- ===== FORM CARD ===== --}}
    <div class="form-card">

        @if($errors->any())
        <div style="padding: 20px 36px 0;">
            <div class="form-error">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
        </div>
        @endif

        <form method="POST"
              action="{{ isset($job) ? route('provider.jobs.update', $job->id) : route('provider.jobs.store') }}">
            @csrf
            @if(isset($job)) @method('PUT') @endif

            {{-- ===== SECTION: Basic Info ===== --}}
            <div class="form-section">
                <div class="form-section-title">Basic Information</div>

                <div class="field-group">
                    <label class="field-label">Job Title <span class="req">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $job->title ?? '') }}"
                           class="field-input" placeholder="e.g. Senior Backend Developer" required>
                </div>

                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label">Location <span class="req">*</span></label>
                        <input type="text" name="location" value="{{ old('location', $job->location ?? '') }}"
                               class="field-input" placeholder="e.g. Kathmandu / Remote" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Job Type <span class="req">*</span></label>
                        <select name="type" class="field-input" required>
                            @foreach(['full-time','part-time','remote','contract','internship'] as $t)
                            <option value="{{ $t }}" {{ old('type', $job->type ?? 'full-time') === $t ? 'selected' : '' }}>
                                {{ ucfirst($t) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-grid-2">
                    <div class="field-group">
                        <label class="field-label">Experience Required</label>
                        <input type="text" name="experience_required" value="{{ old('experience_required', $job->experience_required ?? '') }}"
                               class="field-input" placeholder="e.g. 2+ years">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Salary Range</label>
                        <input type="text" name="salary_range" value="{{ old('salary_range', $job->salary_range ?? '') }}"
                               class="field-input" placeholder="e.g. NPR 50,000–80,000">
                    </div>
                </div>
            </div>

            {{-- ===== SECTION: Skills ===== --}}
            <div class="form-section">
                <div class="form-section-title">Skills</div>

                <div class="field-group">
                    <label class="field-label">
                        Skills
                        <span class="hint"> List skills, technologies, or tools required</span>
                    </label>
                    <input type="hidden" name="key_skills" id="key_skills"
                           value="{{ old('key_skills', $job->key_skills ?? '') }}">
                    <div class="editor-wrapper">
                        <div id="skills-toolbar" class="ql-toolbar ql-snow">
                            <span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-underline"></button></span>
                            <span class="ql-formats"><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span>
                            <span class="ql-formats"><button class="ql-indent" value="-1"></button><button class="ql-indent" value="+1"></button></span>
                            <span class="ql-formats"><select class="ql-align"></select></span>
                            <span class="ql-formats"><button class="ql-blockquote"></button><button class="ql-clean"></button></span>
                        </div>
                        <div id="skills-editor">{!! old('key_skills', $job->key_skills ?? '') !!}</div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION: Description ===== --}}
            <div class="form-section">
                <div class="form-section-title">Job Description</div>

                <div class="field-group">
                    <label class="field-label">
                        Job Description <span class="req">*</span>
                        <span class="hint"> Describe the role, responsibilities, and team</span>
                    </label>
                    <input type="hidden" name="description" id="description"
                           value="{{ old('description', $job->description ?? '') }}" required>
                    <div class="editor-wrapper">
                        <div id="description-toolbar" class="ql-toolbar ql-snow">
                            <span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-underline"></button></span>
                            <span class="ql-formats"><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span>
                            <span class="ql-formats"><button class="ql-indent" value="-1"></button><button class="ql-indent" value="+1"></button></span>
                            <span class="ql-formats"><select class="ql-align"></select></span>
                            <span class="ql-formats"><button class="ql-blockquote"></button><button class="ql-clean"></button></span>
                        </div>
                        <div id="description-editor">{!! old('description', $job->description ?? '') !!}</div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION: Requirements ===== --}}
            <div class="form-section">
                <div class="form-section-title">Requirements & Qualifications</div>

                <div class="field-group">
                    <label class="field-label">
                        Requirements <span class="req">*</span>
                        <span class="hint"> Qualifications, experience, technical skills</span>
                    </label>
                    <input type="hidden" name="requirements" id="requirements"
                           value="{{ old('requirements', $job->requirements ?? '') }}" required>
                    <div class="editor-wrapper">
                        <div id="requirements-toolbar" class="ql-toolbar ql-snow">
                            <span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-underline"></button></span>
                            <span class="ql-formats"><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span>
                            <span class="ql-formats"><button class="ql-indent" value="-1"></button><button class="ql-indent" value="+1"></button></span>
                            <span class="ql-formats"><select class="ql-align"></select></span>
                            <span class="ql-formats"><button class="ql-blockquote"></button><button class="ql-clean"></button></span>
                        </div>
                        <div id="requirements-editor">{!! old('requirements', $job->requirements ?? '') !!}</div>
                    </div>
                </div>
            </div>

            {{-- ===== FOOTER ACTIONS ===== --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtn">
                    {{ isset($job) ? 'Update Job Posting' : 'Publish Job Posting' }}
                </button>
                <a href="{{ route('provider.jobs') }}" class="btn-cancel">Cancel</a>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {


        // Helper: init a Quill editor against a pre-built toolbar element.
        // Passing the toolbar's CSS selector string tells Quill v2 to adopt
        // that existing DOM element instead of auto-generating a new one —
        // this is the definitive fix for the Quill 2.x double-toolbar bug.
        function initQuill(toolbarSelector, editorSelector, placeholder) {
            const editorEl  = document.querySelector(editorSelector);
            const savedHTML = editorEl.innerHTML.trim();
            editorEl.innerHTML = '';  // clean slate before init
            const q = new Quill(editorSelector, {
                modules: { toolbar: toolbarSelector },
                placeholder: placeholder,
                theme: 'snow'
            });
            if (savedHTML) {
                q.clipboard.dangerouslyPasteHTML(savedHTML);
            }
            return q;
        }

        // ===== INIT EDITORS =====
        const skillsQuill = initQuill('#skills-toolbar', '#skills-editor',
            'List the skills, technologies, frameworks, or tools required (e.g. Python, Django, REST API)...');

        const descQuill = initQuill('#description-toolbar', '#description-editor',
            'Describe the role, responsibilities, and what a typical day looks like...');

        const reqQuill = initQuill('#requirements-toolbar', '#requirements-editor',
            'List the qualifications, experience level, and technical skills required...');

        // ===== FORM SUBMIT: sync all hidden inputs =====
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            const descText = descQuill.getText().trim();
            const reqText  = reqQuill.getText().trim();

            if (descText.length === 0) {
                e.preventDefault();
                alert('Please fill in the Job Description before submitting.');
                return false;
            }
            if (reqText.length === 0) {
                e.preventDefault();
                alert('Please fill in the Requirements before submitting.');
                return false;
            }

            document.getElementById('key_skills').value   = skillsQuill.root.innerHTML;
            document.getElementById('description').value  = descQuill.root.innerHTML;
            document.getElementById('requirements').value = reqQuill.root.innerHTML;
        });
    });
</script>
@endpush
