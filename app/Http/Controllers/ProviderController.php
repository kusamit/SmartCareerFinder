<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProviderController extends Controller
{
    private function authUser(): User
    {
        return User::findOrFail(Session::get('user_id'));
    }

    public function dashboard()
    {
        $user = $this->authUser();
                // Visualization data
        // Applicants per job (bar chart)
        $applicantsPerJob = $user->postedJobs()
            ->withCount('applications')
            ->get()
            ->map(function ($job) {
                return [
                    'title' => $job->title,
                    'count' => $job->applications_count,
                ];
            })
            ->toArray();

        // Job status breakdown (doughnut chart)
        $statusBreakdown = $user->postedJobs()
            ->select('status')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        // Top demanded skills (horizontal bar chart) - simple heuristic
        $topSkills = \App\Models\User::where('role', 'seeker')
            ->whereNotNull('skills')
            ->pluck('skills')
            ->flatMap(function ($s) {
                return array_map('trim', explode(',', $s));
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(8)
            ->keys()
            ->toArray();
        $jobs      = $user->postedJobs()->withCount('applications')->latest()->take(5)->get();
        $totalJobs = $user->postedJobs()->count();
        $openJobs  = $user->postedJobs()->where('status', 'open')->count();
        $closedJobs = $user->postedJobs()->where('status', 'closed')->count();
        $totalApps = Application::whereIn('job_id', $user->postedJobs()->pluck('id'))->count();

        return view('provider.dashboard', compact('user', 'jobs', 'totalJobs', 'openJobs', 'closedJobs', 'totalApps', 'applicantsPerJob', 'statusBreakdown', 'topSkills'));

    }

    public function jobs(Request $request)
    {
        $user = $this->authUser();
        $query = $user->postedJobs()->withCount('applications')->latest();
        if ($request->has('status')) {
            $status = $request->status;
            if (in_array($status, ['open', 'closed'])) {
                $query->where('status', $status);
            }
        }
        $jobs = $query->get();
        return view('provider.jobs', compact('user', 'jobs'));
    }


    public function create()
    {
        $user = $this->authUser();
        return view('provider.job-form', compact('user'));
    }

    public function store(Request $request)
    {
        $user = $this->authUser();

        $data = $request->validate([
            'title'               => 'required|string|max:150',
            'location'            => 'nullable|string|max:100',
            'type'                => 'required|in:full-time,part-time,remote,contract,internship',
            'description'         => 'required|string',
            'requirements'        => 'required|string',
            'experience_required' => 'nullable|string|max:50',
            'salary_range'        => 'nullable|string|max:100',
            'key_skills'          => 'nullable|string',
        ]);

        $data['company'] = $user->company_name ?? $user->name;
        $data['user_id'] = $user->id;

        $job = Job::create($data);

        // Python embedding and index update
        // Build job text: convert HTML list items to comma-separated so skills parse correctly
        $rawJob    = $job->title . ' ' . $job->key_skills . ' ' . $job->description . ' ' . $job->requirements . ' ' . $job->location . ' ' . $job->experience_required;
        $rawJob    = preg_replace('/<\/li>/i', ', ', $rawJob);
        $jobText   = trim(preg_replace('/\s+/', ' ', strip_tags($rawJob)));
        $escapedText = escapeshellarg($jobText);
        $jobId = escapeshellarg($job->id);
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        shell_exec("python {$scriptPath} --embed-job --id {$jobId} --text {$escapedText}");
        shell_exec("python {$scriptPath} --index");

        return redirect()->route('provider.jobs')->with('success', 'Job posted successfully!');
    }

    public function edit(Job $job)
    {
        $user = $this->authUser();
        abort_if($job->user_id !== $user->id, 403);
        return view('provider.job-form', compact('user', 'job'));
    }

    public function update(Request $request, Job $job)
    {
        $user = $this->authUser();
        abort_if($job->user_id !== $user->id, 403);

        $data = $request->validate([
            'title'               => 'required|string|max:150',
            'location'            => 'nullable|string|max:100',
            'type'                => 'required|in:full-time,part-time,remote,contract,internship',
            'description'         => 'required|string',
            'requirements'        => 'required|string',
            'experience_required' => 'nullable|string|max:50',
            'salary_range'        => 'nullable|string|max:100',
            'key_skills'          => 'nullable|string',
        ]);

        $job->update($data);

        // Python embedding and index update
        // Build job text: convert HTML list items to comma-separated so skills parse correctly
        $rawJob    = $job->title . ' ' . $job->key_skills . ' ' . $job->description . ' ' . $job->requirements . ' ' . $job->location . ' ' . $job->experience_required;
        $rawJob    = preg_replace('/<\/li>/i', ', ', $rawJob);
        $jobText   = trim(preg_replace('/\s+/', ' ', strip_tags($rawJob)));
        $escapedText = escapeshellarg($jobText);
        $jobId = escapeshellarg($job->id);
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        shell_exec("python {$scriptPath} --embed-job --id {$jobId} --text {$escapedText}");
        shell_exec("python {$scriptPath} --index");

        return redirect()->route('provider.jobs')->with('success', 'Job updated successfully!');
    }

    public function toggleStatus(Job $job)
    {
        $user = $this->authUser();
        abort_if($job->user_id !== $user->id, 403);

        $job->status = $job->status === 'open' ? 'closed' : 'open';
        $job->save();

        return back()->with('success', "Job status changed to {$job->status}.");
    }

    public function destroy(Job $job)
    {
        $user = $this->authUser();
        abort_if($job->user_id !== $user->id, 403);
        $job->delete();
        return back()->with('success', 'Job deleted.');
    }

    public function applicants(Job $job)
    {
        $user = $this->authUser();
        abort_if($job->user_id !== $user->id, 403);

        // Fetch raw FAISS scores for all applicants of this job
        $scriptPath = escapeshellarg(base_path('python/match.py'));
        $cmd        = "python {$scriptPath} --search-applicants --id " . escapeshellarg($job->id);
        $output     = shell_exec($cmd);
        $scores     = json_decode($output, true) ?? [];

        $faissMap = [];
        foreach ($scores as $s) {
            $faissMap[$s['user_id']] = $s['score'];
        }

        $applications = $job->applications()->with('seeker')->get();
        $applications = $applications->map(function ($app) use ($faissMap, $job) {
            if (!$app->seeker) { $app->match_score = 0; return $app; }
            $faissScore       = $faissMap[$app->user_id] ?? 0;
            $comp             = $app->seeker->compositeScore($job, $faissScore);
            $liveScore        = $comp['final_score'];
            $app->match_score = $liveScore;

            // Sync DB so seeker sees the same score everywhere
            if ($app->getOriginal('match_score') != $liveScore) {
                Application::where('id', $app->id)->update(['match_score' => $liveScore]);
            }
            return $app;
        })->sortByDesc('match_score')->values();

        return view('provider.applicants', compact('user', 'job', 'applications'));
    }

    public function allApplicants()
    {
        $user   = $this->authUser();
        $jobIds = $user->postedJobs()->pluck('id');

        $applications = Application::whereIn('job_id', $jobIds)
            ->with(['seeker', 'job'])
            ->latest()
            ->get();

        // ── Live-recompute match score for each application ───────────────────────
        $scriptPath = escapeshellarg(base_path('python/match.py'));

        // Build per-job score maps (one Python call per unique job)
        $jobScoreMaps = [];
        foreach ($jobIds as $jid) {
            $cmd    = "python {$scriptPath} --search-applicants --id " . escapeshellarg($jid);
            $output = shell_exec($cmd);
            $scores = json_decode($output, true) ?? [];
            $jobScoreMaps[$jid] = collect($scores)->keyBy('user_id');
        }

        foreach ($applications as $app) {
            if (!$app->seeker || !$app->job) continue;
            $scoreMap  = $jobScoreMaps[$app->job_id] ?? collect();
            $faissScore = $scoreMap->get($app->user_id)['score'] ?? 0;
            $comp       = $app->seeker->compositeScore($app->job, $faissScore);
            $liveScore  = $comp['final_score'];
            $app->match_score = $liveScore;

            if ($app->getOriginal('match_score') != $liveScore) {
                Application::where('id', $app->id)->update(['match_score' => $liveScore]);
            }
        }
        // ─────────────────────────────────────────────────────────────────────────

        return view('provider.all-applicants', compact('user', 'applications'));
    }

    public function updateApplicationStatus(Request $request, Application $application)
    {
        $user = $this->authUser();
        abort_if($application->job->user_id !== $user->id, 403);

        $request->validate([
            'status' => 'required|in:applied,reviewed,shortlisted,rejected'
        ]);

        $application->status = $request->status;
        $application->save();

        return back()->with('success', "Application status updated to " . ucfirst($request->status) . ".");
    }

    public function viewCv(Application $application)
    {
        $user = $this->authUser();
        abort_if($application->job->user_id !== $user->id, 403);

        $seeker = $application->seeker;
        if (!$seeker) {
            abort(404, 'Candidate profile not found.');
        }

        $seeker->load('educations');

        $cvSummary = $seeker->generateCvSummary();

        return view('provider.cv-pdf', compact('user', 'seeker', 'application', 'cvSummary'));
    }
}

