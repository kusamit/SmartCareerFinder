@extends('layouts.app')
@section('title', 'All Applicants')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/provider-all-applicants.css') }}">
@endpush

@section('content')
<div class="fade-up">

    {{-- Back --}}
    <a href="{{ route('provider.dashboard') }}" class="back-link">← Back to Dashboard</a>

    {{-- ===== PAGE HEADER ===== --}}
    <div class="page-header">
        <div>
            <div class="page-title">All Applicants</div>
            <div class="page-sub">Review candidates across all your active and closed job postings</div>
        </div>
    </div>

    {{-- ===== APPLICANT SEARCH BAR ===== --}}
    <div style="background:#fff; border: 1.5px solid #f1f5f9; border-radius: 20px; padding: 18px 24px; margin-bottom: 24px; box-shadow:0 2px 12px rgba(0,0,0,0.03);">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
            <div>
                <label style="font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:6px;">Search by Name</label>
                <input type="text" id="searchName" placeholder="e.g. Ramesh..." style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 12px; font-size:13px; color:#1e293b; outline:none; transition:all 0.2s;" oninput="filterApplicants()">
            </div>
            <div>
                <label style="font-size: 10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:6px;">Filter by Applied Date</label>
                <input type="date" id="searchDate" style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 12px; font-size:13px; color:#1e293b; outline:none; transition:all 0.2s;" onchange="filterApplicants()">
            </div>
        </div>
    </div>

    {{-- ===== APPLICANT LIST ===== --}}
    @forelse($applications as $app)
    @php
        $score      = $app->match_score;
        $scoreClass = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-mid' : 'score-low');
        $details    = $app->seeker->matchDetails($app->job, $score);
        $matchDataArr = [
            'name'             => $app->seeker->name,
            'job_title'        => $app->job->title,
            'score'            => $score,
            'matched_skills'   => array_values($details['matched_skills']),
            'unmatched_skills' => array_values($details['unmatched_skills']),
            'location_match'   => $details['location_match'],
            'role_match'       => $details['role_match'],
            'seeker_location'  => $details['seeker_location'],
            'job_location'     => $details['job_location'],
            'seeker_role'      => $details['seeker_role'],
            'exp_match'        => $details['exp_match'],
            'exp_message'      => $details['exp_message'],
            'portfolio_match'  => $details['portfolio_match'],
            'composite'        => $details['composite'],
        ];
    @endphp
    <div class="applicant-card" data-name="{{ strtolower($app->seeker->name) }}" data-date="{{ $app->created_at->format('Y-m-d') }}">

        {{-- Avatar --}}
        <div class="applicant-avatar">{{ strtoupper(substr($app->seeker->name, 0, 2)) }}</div>

        {{-- Info --}}
        <div class="applicant-info">
            <div class="applicant-name">{{ $app->seeker->name }}</div>
            <div class="applicant-email">{{ $app->seeker->email }}</div>
            <div class="applicant-job">Applied for: {{ $app->job->title }}</div>
            @if($app->seeker->location)
            <div class="applicant-location">{{ $app->seeker->location }}</div>
            @endif
            @if($app->seeker->skills)
            <div class="applicant-skills">
                @foreach(array_slice($app->seeker->skillsArray(), 0, 5) as $skill)
                <span class="applicant-skill">{{ $skill }}</span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Experience --}}
        <div class="applicant-stat">
            <div class="stat-label-sm">Experience</div>
            <div class="stat-value-sm">{{ $app->seeker->experienceSummary() }}</div>
        </div>

        {{-- Match Score (Clickable) --}}
        <div class="match-wrapper {{ $scoreClass }}" data-match='@json($matchDataArr)' title="Click to view match details">
            <div class="match-score-val" style="display: flex; align-items: baseline; justify-content: center; gap: 2px;">
                <span>{{ $score }}%</span>
                <span style="font-size: 11px; font-weight: 700; color: #e11d48;">/ {{ 100 - $score }}%</span>
            </div>
            <div class="match-label" style="font-size: 8px;">Match / Unmatched</div>
            <div class="match-bar-wrap" style="margin-left: auto; margin-right: auto;">
                <div class="match-bar-fill" style="width: {{ $score }}%"></div>
            </div>
            <div class="match-hint">View Details</div>
        </div>

        {{-- Applied Date --}}
        <div class="applied-col">
            <div class="stat-label-sm">Applied</div>
            <div class="applied-date">{{ $app->created_at->format('M d') }}</div>
            <div class="applied-year">{{ $app->created_at->format('Y') }}</div>
        </div>

        {{-- Status Update --}}
        @php $csClass = 'cs-' . $app->status; @endphp
        <div class="status-action-col">
            <div class="status-action-label">Application Status</div>
            <div class="applicant-current-status {{ $csClass }}">{{ $app->status }}</div>
            <form method="POST" action="{{ route('provider.applications.status', $app->id) }}">
                @csrf @method('PATCH')
                <select name="status" class="status-select" onchange="this.form.submit()">
                    <option value="applied"     {{ $app->status === 'applied'     ? 'selected' : '' }}>Applied</option>
                    <option value="reviewed"    {{ $app->status === 'reviewed'    ? 'selected' : '' }}>Reviewed</option>
                    <option value="shortlisted" {{ $app->status === 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                    <option value="rejected"    {{ $app->status === 'rejected'    ? 'selected' : '' }}>Rejected</option>
                </select>
            </form>
        </div>

    </div>
    @empty
    <div class="empty-state">
        <h3>No applicants yet</h3>
        <p>Your job listings will show applicant profiles here once candidates apply.</p>
    </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
  function filterApplicants() {
      const qName = document.getElementById('searchName').value.toLowerCase().trim();
      const qDate = document.getElementById('searchDate').value.trim();

      document.querySelectorAll('.applicant-card').forEach(card => {
          const n = card.getAttribute('data-name') || '';
          const d = card.getAttribute('data-date') || '';

          const ok = (!qName || n.includes(qName))
                  && (!qDate || d === qDate);

          card.style.display = ok ? '' : 'none';
      });
  }
</script>
@endpush
