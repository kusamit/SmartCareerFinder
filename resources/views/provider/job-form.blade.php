@extends('layouts.app')
@section('title', isset($job) ? 'Edit Job' : 'Post Job')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/provider-job-form.css') }}">
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="form-page-wrapper fade-up">

    {{-- ===== HERO HEADER ===== --}}
    <div class="form-hero">
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

        <form id="jobForm" method="POST"
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
                        <label class="field-label">Location</label>
                        <input type="text" name="location" value="{{ old('location', $job->location ?? '') }}"
                               class="field-input" placeholder="e.g. Kathmandu / Remote">
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
                    <input type="hidden" name="key_skills" id="key_skills" value="">
                    <div class="editor-wrapper">
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
                    <input type="hidden" name="description" id="description" value="">
                    <div class="editor-wrapper">
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
                    <input type="hidden" name="requirements" id="requirements" value="">
                    <div class="editor-wrapper">
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
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js" crossorigin="anonymous"></script>
<script>
    // Global error listener to display browser-side errors on the page
    window.addEventListener('error', function(e) {
        alert('JavaScript Error: ' + e.message + '\nFile: ' + e.filename + '\nLine: ' + e.lineno);
    });

    document.addEventListener('DOMContentLoaded', function () {

        // Helper: init a Quill editor with auto-generated toolbar in JS.
        function initQuill(editorSelector, placeholder) {
            const editorEl = document.querySelector(editorSelector);
            if (!editorEl) return null;
            const savedHTML = editorEl.innerHTML.trim();
            editorEl.innerHTML = '';  // clean slate before init
            
            const q = new Quill(editorEl, {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                        ['blockquote', 'clean']
                    ]
                },
                placeholder: placeholder,
                theme: 'snow'
            });
            
            if (savedHTML) {
                q.root.innerHTML = savedHTML;
            }
            return q;
        }

        // ===== INIT EDITORS =====
        const skillsQuill = initQuill('#skills-editor',
            'List the skills, technologies, frameworks, or tools required (e.g. Python, Django, REST API)...');

        const descQuill = initQuill('#description-editor',
            'Describe the role, responsibilities, and what a typical day looks like...');

        const reqQuill = initQuill('#requirements-editor',
            'List the qualifications, experience level, and technical skills required...');

        // ===== INITIAL SYNC AND REAL-TIME SYNC =====
        const syncHiddenInputs = () => {
            const getEditorHTML = (selector) => {
                const container = document.querySelector(selector);
                if (!container) return '';
                const editor = container.querySelector('.ql-editor');
                return editor ? editor.innerHTML : container.innerHTML;
            };

            document.getElementById('key_skills').value   = getEditorHTML('#skills-editor');
            document.getElementById('description').value  = getEditorHTML('#description-editor');
            document.getElementById('requirements').value = getEditorHTML('#requirements-editor');
        };

        // Sync initially
        syncHiddenInputs();

        // Sync on changes
        if (skillsQuill) skillsQuill.on('text-change', syncHiddenInputs);
        if (descQuill)   descQuill.on('text-change', syncHiddenInputs);
        if (reqQuill)    reqQuill.on('text-change', syncHiddenInputs);

        // ===== FORM SUBMIT =====
        const form = document.getElementById('jobForm');
        form.addEventListener('submit', function (e) {
            // Final sync
            syncHiddenInputs();

            // Validate by stripping HTML tags from the hidden input values
            const stripHTML = function(html) {
                return html.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim();
            };

            const descVal = document.getElementById('description').value;
            const reqVal  = document.getElementById('requirements').value;

            if (stripHTML(descVal).length === 0) {
                e.preventDefault();
                alert('Please fill in the Job Description before submitting.');
                return false;
            }
            if (stripHTML(reqVal).length === 0) {
                e.preventDefault();
                alert('Please fill in the Requirements before submitting.');
                return false;
            }
        });
    });
</script>
@endpush
