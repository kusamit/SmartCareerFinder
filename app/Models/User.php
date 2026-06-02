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

    // Simple keyword-based matching score against a job (0-100)
    public function matchScore(Job $job): int
    {
        $profileText = strtolower($this->profile_summary . ' ' . $this->skills . ' ' . $this->preferred_role);
        $jobText     = strtolower($job->title . ' ' . $job->key_skills . ' ' . $job->description . ' ' . $job->requirements);

        // Tokenize
        $profileWords = array_filter(preg_split('/[\s,;]+/', $profileText));
        $jobWords     = array_filter(preg_split('/[\s,;]+/', $jobText));

        if (empty($profileWords) || empty($jobWords)) return 0;

        // Skill overlap
        $overlap     = count(array_intersect($profileWords, $jobWords));
        $skillScore  = min(60, intval(($overlap / count($jobWords)) * 100));

        // Experience match
        $expScore = 0;
        if ($this->experience_years !== null && $job->experience_required) {
            preg_match('/\d+/', $job->experience_required, $m);
            $reqExp = isset($m[0]) ? (int)$m[0] : 0;
            $expScore = $this->experience_years >= $reqExp ? 20 : max(0, 20 - ($reqExp - $this->experience_years) * 5);
        }

        // Location match
        $locScore = 0;
        if ($this->location && $job->location) {
            similar_text(strtolower($this->location), strtolower($job->location), $pct);
            $locScore = intval($pct / 5); // max 20
        }

        return min(100, $skillScore + $expScore + $locScore);
    }
}
