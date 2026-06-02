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
        $user         = $this->authUser();
        $applications = $user->applications()->with('job')->latest()->take(5)->get();
        $totalJobs    = Job::where('status', 'open')->count();

        return view('seeker.dashboard', compact('user', 'applications', 'totalJobs'));
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

        return back()->with('success', 'Profile updated! Your job matches have been refreshed.');
    }

    public function jobs(Request $request)
    {
        $user = $this->authUser();

        // Step 3-6: Preprocess + Match using profile text
        $jobs = Job::where('status', 'open')->get();

        // Score each job against the seeker profile
        $scored = $jobs->map(function ($job) use ($user) {
            $job->match_score = $user->matchScore($job);
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
            // In production: integrate pdf-parser or call an API
            $cvText = $user->profile_summary . ' ' . $user->skills;
        }

        $cvText = strtolower($cvText);

        // Preprocess (Step 3)
        $cvText = preg_replace('/[^a-z0-9\s]/', ' ', $cvText);
        $cvText = preg_replace('/\s+/', ' ', $cvText);

        $user->save();

        // Match jobs (Step 6: Query FAISS-like matching)
        $jobs = Job::where('status', 'open')->get();
        $scored = $jobs->map(function ($job) use ($cvText) {
            $jobText  = strtolower($job->title . ' ' . $job->key_skills . ' ' . $job->requirements);
            $jobWords = array_filter(preg_split('/[\s,;]+/', $jobText));
            $cvWords  = array_filter(preg_split('/\s+/', $cvText));

            $overlap   = count(array_intersect($cvWords, $jobWords));
            $score     = $jobWords ? min(100, intval(($overlap / count($jobWords)) * 120)) : 0;
            $job->match_score = $score;
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

        Application::create([
            'job_id'      => $job->id,
            'user_id'     => $user->id,
            'match_score' => $user->matchScore($job),
        ]);

        return back()->with('success', "Applied to {$job->title} successfully!");
    }

    public function applications()
    {
        $user         = $this->authUser();
        $applications = $user->applications()->with('job')->latest()->get();
        return view('seeker.applications', compact('user', 'applications'));
    }
}
