<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class JobController extends Controller
{
    public function publicList()
    {
        $jobs = Job::where('status', 'open')->with('provider')->latest()->get();
        return view('jobs.list', compact('jobs'));
    }

    public function show(Job $job)
    {
        $matchScore = null;
        $user       = null;

        if (Session::has('user_id')) {
            $user = User::find(Session::get('user_id'));

            if ($user && $user->role === 'seeker') {
                // Get raw FAISS score from Python index
                $scriptPath = escapeshellarg(base_path('python/match.py'));
                $cmd        = "python {$scriptPath} --search-jobs --id " . escapeshellarg($user->id);
                $output     = shell_exec($cmd);
                $results    = json_decode($output, true) ?? [];

                // ── Auto-sync fallback: user vector missing from FAISS index ──────────
                if (empty($results)) {
                    $userText    = strip_tags(
                        $user->profile_summary . ' ' . $user->skills . ' ' . $user->preferred_role
                        . ' ' . $user->location . ' ' . $user->education . ' ' . $user->portfolio
                    );
                    $escapedText = escapeshellarg($userText);
                    $escapedUid  = escapeshellarg($user->id);
                    $jobText     = strip_tags($job->title . ' ' . $job->key_skills . ' ' . $job->description . ' ' . $job->requirements . ' ' . $job->location . ' ' . $job->experience_required);
                    $escapedJt   = escapeshellarg($jobText);
                    $escapedJid  = escapeshellarg($job->id);

                    shell_exec("python {$scriptPath} --embed-job  --id {$escapedJid} --text {$escapedJt}");
                    shell_exec("python {$scriptPath} --embed-user --id {$escapedUid} --text {$escapedText}");
                    shell_exec("python {$scriptPath} --index");

                    $output  = shell_exec($cmd);
                    $results = json_decode($output, true) ?? [];
                }
                // ──────────────────────────────────────────────────────────────────────

                $faissScore = 0;
                foreach ($results as $r) {
                    if ($r['job_id'] == $job->id) {
                        $faissScore = $r['score'];
                        break;
                    }
                }

                // Full composite: FAISS×70% + Location(10) + Portfolio(10) + Domain(10)
                $comp       = $user->compositeScore($job, $faissScore);
                $matchScore = $comp['final_score'];
            }
        }

        return view('jobs.show', compact('job', 'user', 'matchScore'));
    }
}
