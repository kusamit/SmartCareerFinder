@extends('layouts.app')
@section('title', 'My Jobs')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<style>
    body { background: #f1f4f9 !important; }

    .page-header {
        display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;
    }
    .page-title { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px; }

    .btn-post {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 11px 20px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff; font-weight: 700; font-size: 14px;
        border-radius: 12px; border: none; cursor: pointer; text-decoration: none;
        transition: all 0.25s; box-shadow: 0 6px 20px rgba(79,70,229,0.3);
    }
    .btn-post:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,70,229,0.4); color: #fff; }

    /* ===== JOB CARD ===== */
    .job-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .job-card::before {
        content: '';
        position: absolute; top: 0; left: 0;
        width: 4px; height: 100%;
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
        opacity: 0;
        transition: opacity 0.2s;
    }
    .job-card:hover { border-color: #c7d2fe; box-shadow: 0 8px 28px rgba(99,102,241,0.12); transform: translateY(-2px); }
    .job-card:hover::before { opacity: 1; }
    .job-card.is-closed { opacity: 0.8; }
    .job-card.is-closed::before { background: linear-gradient(180deg, #94a3b8, #64748b); }

    /* ===== CARD TOP ===== */
    .card-top { display: flex; align-items: flex-start; gap: 20px; }
    .card-body { flex: 1; min-width: 0; }
    .card-side { flex-shrink: 0; text-align: right; }

    .job-title {
        font-size: 17px; font-weight: 800; color: #0f172a;
        text-decoration: none; transition: color 0.2s;
        display: block; margin-bottom: 6px;
    }
    .job-title:hover { color: #4f46e5; }

    .card-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-bottom: 8px; }

    .status-open {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 11px; border-radius: 999px; font-size: 11px; font-weight: 700;
        background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;
    }
    .status-open::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #16a34a; display: inline-block; }
    .status-closed {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 11px; border-radius: 999px; font-size: 11px; font-weight: 700;
        background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;
    }
    .status-closed::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #dc2626; display: inline-block; }
    .type-badge {
        display: inline-flex; padding: 3px 10px; border-radius: 999px;
        font-size: 11px; font-weight: 600;
        background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; text-transform: capitalize;
    }

    .card-meta { font-size: 13px; color: #64748b; margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 4px 8px; }
    .card-meta-sep { color: #cbd5e1; }

    .card-desc { font-size: 13.5px; color: #64748b; line-height: 1.65; margin-bottom: 14px; }

    /* ===== SKILL TAGS ===== */
    .skill-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .skill-tag-item {
        padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;
        background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;
    }

    /* ===== APPLICANT COUNT ===== */
    .applicant-count { font-size: 30px; font-weight: 900; color: #4f46e5; line-height: 1; }
    .applicant-label { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 2px; }
    .posted-date { font-size: 11px; color: #cbd5e1; margin-top: 6px; }

    /* ===== DIVIDER ===== */
    .card-divider { height: 1px; background: #f1f5f9; margin: 16px 0; }

    /* ===== ACTION BUTTONS ===== */
    .card-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .btn-action-primary {
        display: inline-flex; align-items: center; padding: 8px 18px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff; font-size: 12px; font-weight: 700;
        border-radius: 10px; border: none; cursor: pointer; text-decoration: none;
        transition: all 0.2s; box-shadow: 0 4px 12px rgba(79,70,229,0.25);
    }
    .btn-action-primary:hover { box-shadow: 0 6px 18px rgba(79,70,229,0.4); color: #fff; transform: translateY(-1px); }

    .btn-action {
        display: inline-flex; align-items: center; padding: 8px 16px;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 12px; font-weight: 600; color: #475569;
        background: #fff; cursor: pointer; text-decoration: none;
        transition: all 0.2s;
    }
    .btn-action:hover { border-color: #6366f1; color: #4f46e5; background: #f5f3ff; }
    .btn-action-danger { }
    .btn-action-danger:hover { border-color: #fca5a5; color: #dc2626; background: #fef2f2; }
    .btn-action-success:hover { border-color: #6ee7b7; color: #059669; background: #ecfdf5; }
    .btn-action-delete { }
    .btn-action-delete:hover { border-color: #fca5a5 !important; color: #dc2626 !important; background: #fef2f2 !important; }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        background: #fff; border: 2px dashed #e2e8f0; border-radius: 20px;
        padding: 72px 24px; text-align: center;
    }
    .empty-state h3 { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .empty-state p  { font-size: 13px; color: #94a3b8; margin-bottom: 24px; }
</style>
@endpush

@section('content')
<div class="fade-up">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="page-header">
        <div class="page-title">My Job Postings</div>
        <a href="{{ route('provider.jobs.create') }}" class="btn-post">+ Post New Job</a>
    </div>

    {{-- ===== AUTO SEARCH BAR ===== --}}
    <div style="background:#fff; border: 1.5px solid #f1f5f9; border-radius: 20px; padding: 18px 24px; margin-bottom: 24px; box-shadow:0 2px 12px rgba(0,0,0,0.03);">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
            <div>
                <label style="font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:6px;">Search Job Title</label>
                <input type="text" id="searchTitle" placeholder="e.g. Developer..." style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 12px; font-size:13px; color:#1e293b; outline:none; transition:all 0.2s;" oninput="filterJobs()">
            </div>
            <div>
                <label style="font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:6px;">Location</label>
                <input type="text" id="searchLocation" placeholder="e.g. Lalitpur..." style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 12px; font-size:13px; color:#1e293b; outline:none; transition:all 0.2s;" oninput="filterJobs()">
            </div>
            <div>
                <label style="font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:6px;">Job Type</label>
                <select id="searchType" style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 12px; font-size:13px; color:#1e293b; outline:none; cursor:pointer;" onchange="filterJobs()">
                    <option value="">All Job Types</option>
                    <option value="full-time">Full-Time</option>
                    <option value="part-time">Part-Time</option>
                    <option value="remote">Remote</option>
                    <option value="contract">Contract</option>
                    <option value="internship">Internship</option>
                </select>
            </div>
        </div>
    </div>

    @forelse($jobs as $job)
    <div class="job-card {{ $job->isOpen() ? '' : 'is-closed' }}" data-title="{{ strtolower($job->title) }}" data-location="{{ strtolower($job->location) }}" data-type="{{ strtolower($job->type) }}">
        <div class="card-top">

            {{-- LEFT: Info --}}
            <div class="card-body">
                <a href="{{ route('jobs.show', $job->id) }}" class="job-title">{{ $job->title }}</a>

                <div class="card-badges">
                    @if($job->isOpen())
                        <span class="status-open">Open</span>
                    @else
                        <span class="status-closed">Closed</span>
                    @endif
                    <span class="type-badge">{{ $job->type }}</span>
                </div>

                <div class="card-meta">
                    <span>{{ $job->location }}</span>
                    @if($job->salary_range)
                        <span class="card-meta-sep">·</span>
                        <span>{{ $job->salary_range }}</span>
                    @endif
                    @if($job->experience_required)
                        <span class="card-meta-sep">·</span>
                        <span>{{ $job->experience_required }}</span>
                    @endif
                </div>

                <p class="card-desc">
                    {{ Str::limit(strip_tags($job->description), 140) }}
                </p>

                @if($job->key_skills)
                <div class="skill-tags">
                    @foreach($job->skillsArray() as $skill)
                    <span class="skill-tag-item">{{ $skill }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- RIGHT: Applicant count --}}
            <div class="card-side">
                <div class="applicant-count">{{ $job->applications_count }}</div>
                <div class="applicant-label">Applicants</div>
                <div class="posted-date">{{ $job->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="card-divider"></div>

        <div class="card-actions">
            <a href="{{ route('provider.jobs.applicants', $job->id) }}" class="btn-action-primary">
                View Applicants
            </a>
            <a href="{{ route('provider.jobs.edit', $job->id) }}" class="btn-action">Edit</a>

            <form method="POST" action="{{ route('provider.jobs.status', $job->id) }}" style="display:inline">
                @csrf @method('PATCH')
                <button class="btn-action {{ $job->isOpen() ? 'btn-action-danger' : 'btn-action-success' }}">
                    {{ $job->isOpen() ? 'Close Job' : 'Reopen Job' }}
                </button>
            </form>

            <form method="POST" action="{{ route('provider.jobs.destroy', $job->id) }}" style="display:inline; margin-left:auto;"
                  onsubmit="return confirm('Delete this job posting? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn-action btn-action-delete">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <h3>No job postings yet</h3>
        <p>Start attracting top talent by posting your first job opening.</p>
        <a href="{{ route('provider.jobs.create') }}" class="btn-post" style="display:inline-flex;">Post Your First Job</a>
    </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
  function filterJobs() {
      const qTitle = document.getElementById('searchTitle').value.toLowerCase().trim();
      const qLoc   = document.getElementById('searchLocation').value.toLowerCase().trim();
      const qType  = document.getElementById('searchType').value.toLowerCase().trim();

      document.querySelectorAll('.job-card').forEach(card => {
          const t  = card.getAttribute('data-title') || '';
          const l  = card.getAttribute('data-location') || '';
          const ty = card.getAttribute('data-type') || '';

          const ok = (!qTitle || t.includes(qTitle))
                  && (!qLoc   || l.includes(qLoc))
                  && (!qType  || ty === qType);

          card.style.display = ok ? '' : 'none';
      });
  }
</script>
@endpush
