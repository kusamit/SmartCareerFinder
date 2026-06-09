<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = [
        'name', 'email', 'password', 'role',
        'skills', 'education', 'experience_years', 'preferred_role',
        'location', 'cv_path', 'profile_summary',
        'company_name', 'company_website', 'company_description',
    ];

    protected $hidden = ['password'];

    // Seeker: jobs they applied to
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // Provider: jobs they posted
    public function postedJobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function isSeeker(): bool
    {
        return $this->role === 'seeker';
    }

    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    // Generate profile summary as natural language text
    public function generateProfileSummary(): string
    {
        $skills = $this->skills ?? 'various skills';
        $exp    = $this->experience_years ?? 0;
        $role   = $this->preferred_role ?? 'a suitable role';
        $loc    = $this->location ?? 'any location';
        $edu    = $this->education ?? '';

        return "{$this->name} is a professional with {$exp} year(s) of experience in {$skills}. "
            . ($edu ? "Education: {$edu}. " : '')
            . "Looking for {$role} based in {$loc}.";
    }

    // Vector-based matching score against a job (0-100) using FAISS index
    public function matchScore(Job $job): int
    {
        $userId = $this->id;
        $jobId = $job->id;
        $scriptPath = escapeshellarg(base_path('python/match.py'));

        // Call python script to query matching jobs for this user
        $cmd = "python {$scriptPath} --search-jobs --id " . escapeshellarg($userId);
        $output = shell_exec($cmd);
        $results = json_decode($output, true);

        if (is_array($results)) {
            foreach ($results as $res) {
                if ($res['job_id'] == $jobId) {
                    return $res['score'];
                }
            }
        }

        // Fallback: if user or job vector is missing, embed both and index
        $profileText = $this->profile_summary . ' ' . $this->skills . ' ' . $this->preferred_role . ' ' . $this->location . ' ' . $this->education;
        $escapedUserText = escapeshellarg($profileText);
        shell_exec("python {$scriptPath} --embed-user --id {$userId} --text {$escapedUserText}");

        $jobText = $job->title . ' ' . $job->key_skills . ' ' . $job->description . ' ' . $job->requirements . ' ' . $job->location . ' ' . $job->experience_required;
        $escapedJobText = escapeshellarg($jobText);
        shell_exec("python {$scriptPath} --embed-job --id {$jobId} --text {$escapedJobText}");

        // Rebuild index
        shell_exec("python {$scriptPath} --index");

        // Retry search
        $output = shell_exec($cmd);
        $results = json_decode($output, true);
        if (is_array($results)) {
            foreach ($results as $res) {
                if ($res['job_id'] == $jobId) {
                    return $res['score'];
                }
            }
        }

        return 0;
    }
}
