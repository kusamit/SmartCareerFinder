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

    // ─── Composite Scoring ────────────────────────────────────────────────────

    /**
     * Check if seeker's location overlaps with job location.
     */
    public function checkLocationMatch(Job $job): bool
    {
        $seekerLoc = mb_strtolower(trim($this->location ?? ''));
        $jobLoc    = mb_strtolower(trim($job->location ?? ''));
        if (!$seekerLoc || !$jobLoc) return false;
        if (str_contains($jobLoc, 'remote')) return true;            // remote matches anyone
        return str_contains($jobLoc, $seekerLoc) || str_contains($seekerLoc, $jobLoc);
    }

    /**
     * Check if seeker has portfolio / projects evidence.
     */
    public function hasPortfolio(): bool
    {
        if (!empty($this->cv_path)) return true;                     // uploaded CV counts
        $summary = mb_strtolower($this->profile_summary ?? '');
        $skills  = mb_strtolower($this->skills ?? '');
        foreach (['github', 'gitlab', 'portfolio', 'project', 'repo', 'bitbucket', 'behance', 'dribbble'] as $kw) {
            if (str_contains($summary, $kw) || str_contains($skills, $kw)) return true;
        }
        return false;
    }

    /**
     * Check if seeker's preferred role aligns with job domain.
     */
    public function checkDomainMatch(Job $job): bool
    {
        $seekerRole = mb_strtolower(trim($this->preferred_role ?? ''));
        $jobTitle   = mb_strtolower(trim($job->title ?? ''));
        $jobDesc    = mb_strtolower(substr($job->description ?? '', 0, 500));
        if (!$seekerRole) return false;

        // Shared domain keywords — if both role and job contain one, it's a domain match
        $domains = ['developer', 'engineer', 'designer', 'analyst', 'manager', 'scientist',
                    'devops', 'admin', 'tester', 'qa', 'frontend', 'backend', 'fullstack',
                    'mobile', 'web', 'data', 'security', 'cloud', 'architect', 'lead'];
        foreach ($domains as $kw) {
            if (str_contains($seekerRole, $kw) && (str_contains($jobTitle, $kw) || str_contains($jobDesc, $kw))) {
                return true;
            }
        }
        // Fallback: direct substring overlap between role and title
        return str_contains($jobTitle, $seekerRole) || str_contains($seekerRole, $jobTitle);
    }

    /**
     * Compute composite score:
     *   FAISS (70%) + Location (10 pts) + Portfolio/Projects (10 pts) + Job Domain (10 pts) = 100
     */
    public function compositeScore(Job $job, int $faissScore): array
    {
        $faissWeighted  = (int) round($faissScore * 0.70);

        $locationMatch  = $this->checkLocationMatch($job);
        $locationPts    = $locationMatch ? 10 : 0;

        $portfolioMatch = $this->hasPortfolio();
        $portfolioPts   = $portfolioMatch ? 10 : 0;

        $domainMatch    = $this->checkDomainMatch($job);
        $domainPts      = $domainMatch ? 10 : 0;

        $finalScore     = min(100, $faissWeighted + $locationPts + $portfolioPts + $domainPts);

        return [
            'faiss_score'     => $faissScore,
            'faiss_weighted'  => $faissWeighted,
            'faiss_max'       => 70,
            'location_match'  => $locationMatch,
            'location_pts'    => $locationPts,
            'location_max'    => 10,
            'portfolio_match' => $portfolioMatch,
            'portfolio_pts'   => $portfolioPts,
            'portfolio_max'   => 10,
            'domain_match'    => $domainMatch,
            'domain_pts'      => $domainPts,
            'domain_max'      => 10,
            'final_score'     => $finalScore,
        ];
    }

    // ─── Match Details (post-FAISS breakdown for modal) ───────────────────────

    public function matchDetails(Job $job, int $storedScore = 0): array
    {
        // 1. Skills comparison
        $seekerSkills   = array_values(array_filter(array_map('trim', explode(',', $this->skills ?? ''))));
        $jobSkills      = $job->skillsArray();
        $matchedSkills  = [];
        $unmatchedSkills = [];
        foreach ($jobSkills as $js) {
            $found = false;
            foreach ($seekerSkills as $ss) {
                if (mb_strtolower($js) === mb_strtolower($ss)
                    || str_contains(mb_strtolower($js), mb_strtolower($ss))
                    || str_contains(mb_strtolower($ss), mb_strtolower($js))) {
                    $found = true; break;
                }
            }
            $found ? ($matchedSkills[] = $js) : ($unmatchedSkills[] = $js);
        }

        // 2. Experience comparison
        $seekerExp      = (int) ($this->experience_years ?? 0);
        $jobExpRequired = 0;
        if ($job->experience_required) {
            preg_match('/\d+/', $job->experience_required, $m);
            if (!empty($m)) $jobExpRequired = (int) $m[0];
        }
        $expMatch   = $seekerExp >= $jobExpRequired;
        $expMessage = $expMatch
            ? "Candidate has {$seekerExp} yr(s) of experience — meets the requirement."
            : "Candidate has {$seekerExp} yr(s); job requires {$job->experience_required}.";

        // 3. Composite post-FAISS components
        $locationMatch  = $this->checkLocationMatch($job);
        $portfolioMatch = $this->hasPortfolio();
        $domainMatch    = $this->checkDomainMatch($job);

        // 4. Back-calculate composite breakdown from stored composite score
        $locationPts   = $locationMatch  ? 10 : 0;
        $portfolioPts  = $portfolioMatch ? 10 : 0;
        $domainPts     = $domainMatch    ? 10 : 0;
        $bonusPts      = $locationPts + $portfolioPts + $domainPts;
        $faissWeighted = max(0, $storedScore - $bonusPts);
        $faissApprox   = min(100, (int) round($faissWeighted / 0.70));

        $composite = [
            'faiss_score'     => $faissApprox,
            'faiss_weighted'  => $faissWeighted,
            'faiss_max'       => 70,
            'location_match'  => $locationMatch,
            'location_pts'    => $locationPts,
            'location_max'    => 10,
            'portfolio_match' => $portfolioMatch,
            'portfolio_pts'   => $portfolioPts,
            'portfolio_max'   => 10,
            'domain_match'    => $domainMatch,
            'domain_pts'      => $domainPts,
            'domain_max'      => 10,
            'final_score'     => $storedScore ?: ($faissWeighted + $bonusPts),
        ];

        return [
            'matched_skills'  => $matchedSkills,
            'unmatched_skills'=> $unmatchedSkills,
            'location_match'  => $locationMatch,
            'role_match'      => $domainMatch,
            'seeker_skills'   => $seekerSkills,
            'job_skills'      => $jobSkills,
            'seeker_location' => $this->location ?? 'Not Specified',
            'job_location'    => $job->location,
            'seeker_role'     => $this->preferred_role ?? 'Not Specified',
            'job_title'       => $job->title,
            'exp_match'       => $expMatch,
            'exp_message'     => $expMessage,
            'seeker_exp'      => $seekerExp,
            'job_exp'         => $jobExpRequired,
            'portfolio_match' => $portfolioMatch,
            'has_cv'          => !empty($this->cv_path),
            'composite'       => $composite,
        ];
    }
}

