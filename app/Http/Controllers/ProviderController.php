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
        $user      = $this->authUser();
        $jobs      = $user->postedJobs()->withCount('applications')->latest()->take(5)->get();
        $totalJobs = $user->postedJobs()->count();
        $openJobs  = $user->postedJobs()->where('status', 'open')->count();
        $totalApps = Application::whereIn('job_id', $user->postedJobs()->pluck('id'))->count();

        return view('provider.dashboard', compact('user', 'jobs', 'totalJobs', 'openJobs', 'totalApps'));
    }

    public function jobs()
    {
        $user = $this->authUser();
        $jobs = $user->postedJobs()->withCount('applications')->latest()->get();
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
            'location'            => 'required|string|max:100',
            'type'                => 'required|in:full-time,part-time,remote,contract,internship',
            'description'         => 'required|string',
            'requirements'        => 'required|string',
            'experience_required' => 'nullable|string|max:50',
            'salary_range'        => 'nullable|string|max:100',
            'key_skills'          => 'nullable|string',
        ]);

        $data['company'] = $user->company_name ?? $user->name;
        $data['user_id'] = $user->id;

        Job::create($data);

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
            'location'            => 'required|string|max:100',
            'type'                => 'required|in:full-time,part-time,remote,contract,internship',
            'description'         => 'required|string',
            'requirements'        => 'required|string',
            'experience_required' => 'nullable|string|max:50',
            'salary_range'        => 'nullable|string|max:100',
            'key_skills'          => 'nullable|string',
        ]);

        $job->update($data);

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

        $applications = $job->applications()->with('seeker')->orderByDesc('match_score')->get();
        return view('provider.applicants', compact('user', 'job', 'applications'));
    }
}
