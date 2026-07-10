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
