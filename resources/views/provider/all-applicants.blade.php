@extends('layouts.app')
@section('title', 'All Applicants')

@section('nav_links')
<a href="{{ route('provider.dashboard') }}" class="nav-link">Dashboard</a>
<a href="{{ route('provider.jobs') }}" class="nav-link">My Jobs</a>
<a href="{{ route('provider.jobs.create') }}" class="nav-link">Post Job</a>
@endsection

@push('styles')
<style>
    body { background: #f1f4f9 !important; }

    /* ===== BACK LINK ===== */
    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 600; color: #64748b;
        text-decoration: none; margin-bottom: 20px;
        transition: color 0.2s;
    }
    .back-link:hover { color: #4f46e5; }

    /* ===== PAGE HEADER ===== */
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; }
    .page-title   { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px; }
    .page-sub     { font-size: 13px; color: #64748b; margin-top: 3px; }

    /* ===== APPLICANT CARD ===== */
    .applicant-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
    }
    .applicant-card:hover { border-color: #c7d2fe; box-shadow: 0 6px 20px rgba(99,102,241,0.1); transform: translateY(-1px); }

    /* ===== AVATAR ===== */
    .applicant-avatar {
        width: 52px; height: 52px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 800; flex-shrink: 0;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }

    /* ===== APPLICANT INFO ===== */
    .applicant-info { flex: 1; min-width: 0; }
    .applicant-name { font-size: 15px; font-weight: 800; color: #0f172a; }
    .applicant-email { font-size: 12px; color: #64748b; margin-top: 1px; }
    .applicant-job   { font-size: 12px; color: #6366f1; font-weight: 600; margin-top: 3px; }
    .applicant-location { font-size: 12px; color: #94a3b8; margin-top: 2px; }

    .applicant-skills { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 10px; }
    .applicant-skill {
        padding: 3px 10px; border-radius: 7px; font-size: 11px; font-weight: 600;
        background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;
    }

    /* ===== STATS COLUMNS ===== */
    .applicant-stat {
        text-align: center; flex-shrink: 0;
        padding: 0 12px;
        border-left: 1px solid #f1f5f9;
    }
    .stat-label-sm { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 4px; }
    .stat-value-sm { font-size: 15px; font-weight: 800; color: #0f172a; }

    /* ===== MATCH SCORE ===== */
    .match-wrapper { text-align: center; flex-shrink: 0; padding: 0 12px; border-left: 1px solid #f1f5f9; }
    .match-score-val { font-size: 24px; font-weight: 900; line-height: 1; }
    .match-label    { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }
    .match-bar-wrap {
        height: 5px; background: #fee2e2; border-radius: 999px; overflow: hidden; margin-top: 6px; width: 64px;
    }
    .match-bar-fill { height: 100%; border-radius: 999px; transition: width 0.5s ease; }

    .score-high { color: #059669; }
    .score-high .match-bar-fill { background: linear-gradient(90deg, #10b981, #059669); }
    .score-mid  { color: #d97706; }
    .score-mid .match-bar-fill  { background: linear-gradient(90deg, #fbbf24, #d97706); }
    .score-low  { color: #64748b; }
    .score-low .match-bar-fill  { background: linear-gradient(90deg, #94a3b8, #64748b); }

    /* ===== APPLIED DATE ===== */
    .applied-col { text-align: center; flex-shrink: 0; padding: 0 8px; border-left: 1px solid #f1f5f9; }
    .applied-date { font-size: 13px; font-weight: 700; color: #0f172a; }
    .applied-year { font-size: 11px; color: #94a3b8; }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        background: #fff; border: 2px dashed #e2e8f0; border-radius: 20px;
        padding: 72px 24px; text-align: center;
    }
    .empty-state h3 { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .empty-state p  { font-size: 13px; color: #94a3b8; }

    /* ===== CLICKABLE MATCH ===== */
    .match-wrapper {
        cursor: pointer;
        border-radius: 12px;
        transition: background 0.2s, transform 0.15s;
        padding: 8px 12px;
    }
    .match-wrapper:hover { background: #f8fafc; transform: scale(1.05); }
    .match-wrapper .match-hint {
        font-size: 9px; color: #c7d2fe; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em; margin-top: 4px;
    }

    /* ===== APPLICANT STATUS CONTROL ===== */
    .status-action-col { flex-shrink: 0; padding: 0 0 0 12px; border-left: 1px solid #f1f5f9; min-width: 160px; }
    .status-action-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 6px; }
    .status-select {
        width: 100%; padding: 6px 10px; border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 12px; font-weight: 700; color: #0f172a; background: #f8fafc;
        cursor: pointer; outline: none; transition: border-color 0.2s;
    }
    .status-select:hover { border-color: #a5b4fc; }
    .status-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .applicant-current-status {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700;
        text-transform: capitalize; margin-bottom: 5px;
    }
    .cs-applied     { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .cs-reviewed    { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .cs-shortlisted { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .cs-rejected    { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
</style>
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
