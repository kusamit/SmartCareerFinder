@extends('layouts.app')
@section('title', 'My Jobs')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/provider-jobs.css') }}">
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
            <div>
                <label style="font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:6px;">Job Status</label>
                <select id="searchStatus" style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 12px; font-size:13px; color:#1e293b; outline:none; cursor:pointer;" onchange="filterJobs()">
                    <option value="">All Statuses</option>
                    <option value="open">Open Jobs</option>
                    <option value="closed">Closed Jobs</option>
                </select>
            </div>
        </div>
    </div>

    @forelse($jobs as $job)
    <div class="job-card {{ $job->isOpen() ? '' : 'is-closed' }}" data-title="{{ strtolower($job->title) }}" data-location="{{ strtolower($job->location) }}" data-type="{{ strtolower($job->type) }}" data-status="{{ $job->isOpen() ? 'open' : 'closed' }}">
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


                {{-- ===== MORE JOB DETAILS ROW ===== --}}
                <div class="job-details-row">

                    {{-- Salary --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Salary</div>
                            <div class="detail-chip-value">{{ $job->salary_range ?: 'Not specified' }}</div>
                        </div>
                    </div>

                    {{-- Experience --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Experience</div>
                            <div class="detail-chip-value">{{ $job->experience_required ?: 'Open to all' }}</div>
                        </div>
                    </div>


                    {{-- Posted date --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Posted</div>
                            <div class="detail-chip-value">{{ $job->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="detail-chip">
                        <div>
                            <div class="detail-chip-label">Location</div>
                            <div class="detail-chip-value">{{ $job->location }}</div>
                        </div>
                    </div>

                </div>
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
      const qTitle  = document.getElementById('searchTitle').value.toLowerCase().trim();
      const qLoc    = document.getElementById('searchLocation').value.toLowerCase().trim();
      const qType   = document.getElementById('searchType').value.toLowerCase().trim();
      const qStatus = document.getElementById('searchStatus').value.toLowerCase().trim();

      document.querySelectorAll('.job-card').forEach(card => {
          const t  = card.getAttribute('data-title') || '';
          const l  = card.getAttribute('data-location') || '';
          const ty = card.getAttribute('data-type') || '';
          const st = card.getAttribute('data-status') || '';

          const ok = (!qTitle  || t.includes(qTitle))
                  && (!qLoc    || l.includes(qLoc))
                  && (!qType   || ty === qType)
                  && (!qStatus || st === qStatus);

          card.style.display = ok ? '' : 'none';
      });
  }
</script>
@endpush
