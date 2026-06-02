<?php

namespace App\Http\Controllers;

use App\Models\Job;

class JobController extends Controller
{
    public function publicList()
    {
        $jobs = Job::where('status', 'open')->with('provider')->latest()->get();
        return view('jobs.list', compact('jobs'));
    }

    public function show(Job $job)
    {
        return view('jobs.show', compact('job'));
    }
}
