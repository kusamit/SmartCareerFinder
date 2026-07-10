<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SeekerController extends Controller
{
    private function authUser(): User
    {
        return User::findOrFail(Session::get('user_id'));
    }

    public function dashboard()
    {
        $user = $this->authUser();
        // Compute total open jobs for stats
        $totalJobs = \App\Models\Job::where('status', 'open')->count();
        // Recent applications for the seeker
        $applications = $user->applications()->with('job')->latest()->take(5)->get();

        // ── Live-recompute composite score for each recent application ──────────────
        if ($applications->count() > 0) {
            $scriptPath  = escapeshellarg(base_path('python/match.py'));
            $cmd         = "python {$scriptPath} --search-jobs --id " . escapeshellarg($user->id);
            $output      = shell_exec($cmd);
            $faissScores = collect(json_decode($output, true) ?? [])->keyBy('job_id');

            foreach ($applications as $app) {
                if (!$app->job) continue;
                $faissScore = $faissScores->get($app->job_id)['score'] ?? 0;
                $comp       = $user->compositeScore($app->job, $faissScore);
                $liveScore  = $comp['final_score'];

                if ($app->match_score != $liveScore) {
                    \App\Models\Application::where('id', $app->id)->update(['match_score' => $liveScore]);
                    $app->match_score = $liveScore;
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────────────────
        // Gather data for visualizations
        // Application status breakdown for doughnut chart
        $statusCounts = $user->applications()
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        // Match scores from recent applications for histogram/bar chart
        $matchScores = $user->applications()
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('match_score')
            ->toArray();

        // Recent activity timeline (application dates)
        $recentActivity = $user->applications()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'job_id', 'created_at', 'status'])
            ->toArray();

        // Profile completeness percentage (simple heuristic)
        $filled = 0;
        $fields = ['name','skills','education','experience_years','preferred_role','location','profile_summary'];
        foreach ($fields as $f) {
            if (!empty($user->$f)) $filled++;
        }
        $profileCompleteness = intval(($filled / count($fields)) * 100);

        // Calculate monthly application counts for the last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[now()->subMonths($i)->format('M')] = 0;
        }

        $allApps = $user->applications()->orderBy('created_at')->get();
        foreach ($allApps as $app) {
            $mon = $app->created_at->format('M');
            if (isset($months[$mon])) {
                $months[$mon]++;
            }
        }
        $monthlyCounts = array_values($months);
        $monthlyLabels = array_keys($months);

        return view('seeker.dashboard', compact('user', 'totalJobs', 'applications', 'statusCounts', 'matchScores', 'recentActivity', 'profileCompleteness', 'monthlyCounts', 'monthlyLabels'));
    }

    public function profile()
    {
        $user = $this->authUser();
        $user->load('educations');
        return view('seeker.profile', compact('user'));
    }

    public function profileView()
    {
        $user = $this->authUser();
        $user->load('educations');
        return view('seeker.profile-view', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = $this->authUser();

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|max:100|unique:users,email,' . $user->id,
            'skills'           => 'nullable|string',
            'education'        => 'nullable|string|max:200',
            'experience_years' => 'nullable|string|max:5000',
            'preferred_role'   => 'nullable|string|max:5000',
            'location'         => 'nullable|string|max:100',
            'phone'            => 'nullable|string|max:50',
            'portfolio'        => 'nullable|string|max:5000',
        ]);

        $user->fill($request->only([
            'name', 'email', 'skills', 'education', 'experience_years', 'preferred_role', 'location', 'phone', 'portfolio'
        ]));

        // Re-generate profile summary (Step 2 from diagram: Profile to Text Conversion)
        $user->profile_summary = $user->generateProfileSummary();
        $user->save();

        // Python embedding and index update
        $userText = $user->profile_summary . ' ' . $user->skills . ' ' . $user->preferred_role . ' ' . $user->location . ' ' . $user->education . ' ' . $user->portfolio;
        $escapedText = escapeshellarg($userText);
        $userId = escapeshellarg($user->id);
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        shell_exec("python {$scriptPath} --embed-user --id {$userId} --text {$escapedText}");
        shell_exec("python {$scriptPath} --index");

        // ── Immediately sync all application scores after profile update ─────────
        // One Python call returns scores for all jobs
        $cmd         = "python {$scriptPath} --search-jobs --id " . escapeshellarg($user->id);
        $output      = shell_exec($cmd);
        $faissScores = collect(json_decode($output, true) ?? [])->keyBy('job_id');

        $allApps = $user->applications()->with('job')->get();
        foreach ($allApps as $app) {
            if (!$app->job) continue;
            $faissScore = $faissScores->get($app->job_id)['score'] ?? 0;
            $comp       = $user->compositeScore($app->job, $faissScore);
            $liveScore  = $comp['final_score'];
            if ($app->match_score != $liveScore) {
                Application::where('id', $app->id)->update(['match_score' => $liveScore]);
            }
        }
        // ─────────────────────────────────────────────────────────────────────────

        return back()->with('success', 'Profile updated! Your job matches have been refreshed.');
    }

    public function jobs(Request $request)
    {
        $user = $this->authUser();

        // Exclude jobs this seeker has already applied to (per-user filtering)
        $appliedJobIds = $user->applications()->pluck('job_id')->toArray();

        $jobs = Job::where('status', 'open')
                   ->whereNotIn('id', $appliedJobIds)
                   ->get();

        // Fetch matches from Python FAISS index
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        $cmd = "python {$scriptPath} --search-jobs --id " . escapeshellarg($user->id);
        $output = shell_exec($cmd);
        $scores = json_decode($output, true) ?? [];

        $scoreMap = [];
        foreach ($scores as $s) {
            $scoreMap[$s['job_id']] = $s['score'];
        }

        // Score each job: FAISS base × 60% + location(15) + portfolio(10) + domain(15)
        $scored = $jobs->map(function ($job) use ($scoreMap, $user) {
            $faissScore        = $scoreMap[$job->id] ?? 0;
            $comp              = $user->compositeScore($job, $faissScore);
            $job->match_score  = $comp['final_score'];
            $job->match_composite = $comp;
            return $job;
        })->sortByDesc('match_score')->values();

        return view('seeker.jobs', compact('user', 'scored'));
    }

    /**
     * Find jobs by uploaded CV
     * Step: Upload CV → extract text → match against jobs
     */
    public function findByCv(Request $request)
    {
        $request->validate(['cv' => 'required|file|mimes:pdf,doc,docx,txt|max:5120']);

        $user = $this->authUser();
        $file = $request->file('cv');

        // Save CV path
        $path     = $file->store('cvs', 'public');
        $user->cv_path = $path;

        // Extract text from CV (basic: read txt files; for PDF use pdftotext in production)
        $cvText = '';
        if ($file->getClientOriginalExtension() === 'txt') {
            $cvText = file_get_contents($file->getRealPath());
        } else {
            // For PDF/DOC, we simulate extraction from filename + user's existing profile
            $cvText = $user->profile_summary . ' ' . $user->skills;
        }

        $cvText = strtolower($cvText);

        // Preprocess (Step 3)
        $cvText = preg_replace('/[^a-z0-9\s]/', ' ', $cvText);
        $cvText = preg_replace('/\s+/', ' ', $cvText);

        $user->save();

        // Search matching jobs by CV text using Python
        $escapedCvText = escapeshellarg($cvText);
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        $cmd = "python {$scriptPath} --search-cv --text {$escapedCvText}";
        $output = shell_exec($cmd);
        $scores = json_decode($output, true) ?? [];

        $scoreMap = [];
        foreach ($scores as $s) {
            $scoreMap[$s['job_id']] = $s['score'];
        }

        $jobs = Job::where('status', 'open')->get();
        $scored = $jobs->map(function ($job) use ($scoreMap, $user) {
            $faissScore        = $scoreMap[$job->id] ?? 0;
            $comp              = $user->compositeScore($job, $faissScore);
            $job->match_score  = $comp['final_score'];
            $job->match_composite = $comp;
            return $job;
        })->sortByDesc('match_score')->values();

        return view('seeker.jobs', compact('user', 'scored'))->with('cv_mode', true);
    }

    public function apply(Request $request, Job $job)
    {
        $user = $this->authUser();

        if (!$job->isOpen()) {
            return back()->with('error', 'This job is no longer accepting applications.');
        }

        $already = Application::where('job_id', $job->id)->where('user_id', $user->id)->exists();
        if ($already) {
            return back()->with('error', 'You have already applied for this job.');
        }

        // Composite score: FAISS × 60% + location + portfolio + domain bonuses
        $faissScore = $user->matchScore($job);
        $composite  = $user->compositeScore($job, $faissScore);

        Application::create([
            'job_id'      => $job->id,
            'user_id'     => $user->id,
            'match_score' => $composite['final_score'],
        ]);

        return back()->with('success', "Applied to {$job->title} successfully!");
    }

    public function applications(Request $request)
    {
        $user = $this->authUser();

        // Status filter (from dashboard widget links like ?status=reviewed)
        $validStatuses = ['applied', 'reviewed', 'shortlisted', 'rejected'];
        $activeStatus  = in_array($request->query('status'), $validStatuses)
                         ? $request->query('status') : null;

        // Status counts (always show all, unaffected by filter)
        $statusCounts = $user->applications()
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $matchScores = $user->applications()
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('match_score')
            ->toArray();

        $recentActivity = $user->applications()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'job_id', 'created_at', 'status'])
            ->toArray();

        $profileCompleteness = (int)($user->profile_summary ? 100 : 0);

        // Build applications query with optional status filter
        $appsQuery = $user->applications()->with('job')->latest();
        if ($activeStatus) {
            $appsQuery->where('status', $activeStatus);
        }
        $applications = $appsQuery->get();

        // ── Live-recompute composite score ────────────────────────────────────
        // Fetch FAISS scores once (one Python call for all jobs)
        $scriptPath  = escapeshellarg(base_path('python/match.py'));
        $cmd         = "python {$scriptPath} --search-jobs --id " . escapeshellarg($user->id);
        $output      = shell_exec($cmd);
        $faissScores = collect(json_decode($output, true) ?? [])->keyBy('job_id');

        // Build a plain PHP array keyed by application ID — never set on the Eloquent model
        $liveScores = [];   // [ app_id => ['score' => int, 'composite' => array] ]

        foreach ($applications as $app) {
            if (!$app->job) continue;
            $faissScore = $faissScores->get($app->job_id)['score'] ?? 0;
            $comp       = $user->compositeScore($app->job, $faissScore);
            $liveScore  = $comp['final_score'];

            $liveScores[$app->id] = ['score' => $liveScore, 'composite' => $comp];

            // Always sync DB to the freshly computed score
            if ($app->match_score != $liveScore) {
                \App\Models\Application::where('id', $app->id)
                    ->update(['match_score' => $liveScore]);
            }
        }
        // ────────────────────────────────────────────────────────────

        return view('seeker.applications', compact('user', 'applications', 'activeStatus', 'liveScores'));
    }

    public function educationIndex()
    {
        $user = $this->authUser();
        $educations = $user->educations;
        return view('seeker.education', compact('user', 'educations'));
    }

    public function educationStore(Request $request)
    {
        $user = $this->authUser();
        $request->validate([
            'school'         => 'required|string|max:200',
            'degree'         => 'required|string|max:200',
            'field_of_study' => 'required|string|max:200',
            'start_year'     => 'required|string|max:50',
            'end_year'       => 'required|string|max:50',
        ]);

        $user->educations()->create($request->only([
            'school', 'degree', 'field_of_study', 'start_year', 'end_year'
        ]));

        $this->syncUserFaissAndScores($user);

        return redirect()->route('seeker.education')->with('success', 'Education added successfully!');
    }

    public function educationUpdate(Request $request, Education $education)
    {
        $user = $this->authUser();
        abort_if($education->user_id !== $user->id, 403);

        $request->validate([
            'school'         => 'required|string|max:200',
            'degree'         => 'required|string|max:200',
            'field_of_study' => 'required|string|max:200',
            'start_year'     => 'required|string|max:50',
            'end_year'       => 'required|string|max:50',
        ]);

        $education->update($request->only([
            'school', 'degree', 'field_of_study', 'start_year', 'end_year'
        ]));

        $this->syncUserFaissAndScores($user);

        return redirect()->route('seeker.education')->with('success', 'Education updated successfully!');
    }

    public function educationDestroy(Education $education)
    {
        $user = $this->authUser();
        abort_if($education->user_id !== $user->id, 403);

        $education->delete();

        $this->syncUserFaissAndScores($user);

        return redirect()->route('seeker.education')->with('success', 'Education removed successfully!');
    }

    private function syncUserFaissAndScores(User $user)
    {
        // Re-generate profile summary and save
        $user->profile_summary = $user->generateProfileSummary();
        $user->save();

        // Python embedding and index update
        $userText = $user->profile_summary . ' ' . $user->skills . ' ' . $user->preferred_role . ' ' . $user->location . ' ' . $user->education . ' ' . $user->portfolio;
        $escapedText = escapeshellarg($userText);
        $userId = escapeshellarg($user->id);
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        shell_exec("python {$scriptPath} --embed-user --id {$userId} --text {$escapedText}");
        shell_exec("python {$scriptPath} --index");

        // Recalculate and update database scores for all seeker applications
        $cmd         = "python {$scriptPath} --search-jobs --id " . escapeshellarg($user->id);
        $output      = shell_exec($cmd);
        $faissScores = collect(json_decode($output, true) ?? [])->keyBy('job_id');

        $allApps = $user->applications()->with('job')->get();
        foreach ($allApps as $app) {
            if (!$app->job) continue;
            $faissScore = $faissScores->get($app->job_id)['score'] ?? 0;
            $comp       = $user->compositeScore($app->job, $faissScore);
            $liveScore  = $comp['final_score'];
            if ($app->match_score != $liveScore) {
                \App\Models\Application::where('id', $app->id)->update(['match_score' => $liveScore]);
            }
        }
    }
}
