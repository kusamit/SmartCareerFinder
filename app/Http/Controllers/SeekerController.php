<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
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

        return view('seeker.dashboard', compact('user', 'totalJobs', 'applications', 'statusCounts', 'matchScores', 'recentActivity', 'profileCompleteness'));
    }

    public function profile()
    {
        $user = $this->authUser();
        return view('seeker.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = $this->authUser();

        $request->validate([
            'name'             => 'required|string|max:100',
            'skills'           => 'nullable|string',
            'education'        => 'nullable|string|max:200',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'preferred_role'   => 'nullable|string|max:100',
            'location'         => 'nullable|string|max:100',
        ]);

        $user->fill($request->only([
            'name', 'skills', 'education', 'experience_years', 'preferred_role', 'location'
        ]));

        // Re-generate profile summary (Step 2 from diagram: Profile to Text Conversion)
        $user->profile_summary = $user->generateProfileSummary();
        $user->save();

        // Python embedding and index update
        $userText = $user->profile_summary . ' ' . $user->skills . ' ' . $user->preferred_role . ' ' . $user->location . ' ' . $user->education;
        $escapedText = escapeshellarg($userText);
        $userId = escapeshellarg($user->id);
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        shell_exec("python {$scriptPath} --embed-user --id {$userId} --text {$escapedText}");
        shell_exec("python {$scriptPath} --index");

        return back()->with('success', 'Profile updated! Your job matches have been refreshed.');
    }

    public function jobs(Request $request)
    {
        $user = $this->authUser();
        $jobs = Job::where('status', 'open')->get();

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
            $job->match_composite = $comp;          // available in view
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

        return back()->with('success', "Applied to {$job->title} successfully! Your composite match score is {$composite['final_score']}%.");
    }

    public function applications()
    {
        $user = $this->authUser();
        // Visualization data
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

        $applications = $user->applications()->with('job')->latest()->get();
        return view('seeker.applications', compact('user', 'applications'));
    }
}
