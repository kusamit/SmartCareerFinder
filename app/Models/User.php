<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = [
        'name', 'email', 'password', 'role',
        'skills', 'education', 'experience_years', 'preferred_role',
        'location', 'phone', 'portfolio', 'cv_path', 'profile_summary',
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

    // Seeker: education history
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderByDesc('start_year');
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
        return \App\Services\ProfileSummaryGenerator::generate($this);
    }

    // Generate executive summary specifically for CV output
    public function generateCvSummary(): string
    {
        return \App\Services\CvSummaryGenerator::generate($this);
    }

    public function experienceYearsVal(): int
    {
        $raw = $this->experience_years ?? '';
        $raw = strip_tags($raw);
        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Find patterns like "3 years", "5+ years", "1 year"
        preg_match_all('/(\d+)\+?\s*(?:year|yr)/i', $raw, $matches);
        if (!empty($matches[1])) {
            return (int) max(array_map('intval', $matches[1]));
        }
        
        // Fallback: search for numbers
        preg_match_all('/\b(\d+)\b/', $raw, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $m) {
                $val = (int) $m;
                if ($val > 0 && $val <= 20) {
                    return $val;
                }
            }
        }
        return 0;
    }

    public function experienceSummary(): string
    {
        $years = $this->experienceYearsVal();
        if ($years > 0) return $years . ' yr(s)';

        // Fallback: check if raw text is a plain number
        $raw = trim(strip_tags($this->experience_years ?? ''));
        if (is_numeric($raw) && (int)$raw > 0) return $raw . ' yr(s)';

        // If experience text exists but no years parsed, show "Fresher"
        if (!empty($raw)) return 'Fresher';

        return '—';
    }

    public function skillsArray(): array
    {
        $raw = $this->skills ?? '';
        $raw = preg_replace('/<\/?(li|br|p|h[1-6]|div|ul|ol)[^>]*>/i', ',', $raw);
        $raw = strip_tags($raw);
        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Trim regular AND non-breaking spaces (\xc2\xa0 = UTF-8 encoding of char 160)
        $trimNbsp = fn(string $s): string => trim($s, " \t\n\r\0\x0B\xc2\xa0");
        // Replace non-breaking space sequences with a single real space inside strings
        $raw = preg_replace('/[\xc2\xa0\s]+/', ' ', $raw);
        return array_values(array_filter(array_map($trimNbsp, preg_split('/[,\n]+/', $raw))));
    }

    public function preferredRoleArray(): array
    {
        $raw = $this->preferred_role ?? '';
        $raw = preg_replace('/<\/?(li|br|p|h[1-6]|div|ul|ol)[^>]*>/i', ',', $raw);
        $raw = strip_tags($raw);
        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Trim regular AND non-breaking spaces (\xc2\xa0 = UTF-8 encoding of char 160)
        $trimNbsp = fn(string $s): string => trim($s, " \t\n\r\0\x0B\xc2\xa0");
        // Replace non-breaking space sequences with a single real space inside strings
        $raw = preg_replace('/[\xc2\xa0\s]+/', ' ', $raw);
        return array_values(array_filter(array_map($trimNbsp, preg_split('/[,\n]+/', $raw))));
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
        $profileText = strip_tags($this->profile_summary . ' ' . $this->skills . ' ' . $this->preferred_role . ' ' . $this->location . ' ' . $this->education);
        $escapedUserText = escapeshellarg($profileText);
        shell_exec("python {$scriptPath} --embed-user --id {$userId} --text {$escapedUserText}");

        $rawJob = $job->title . ' ' . $job->key_skills . ' ' . $job->description . ' ' . $job->requirements . ' ' . $job->location . ' ' . $job->experience_required;
        $rawJob = preg_replace('/<\/li>/i', ', ', $rawJob);
        $jobText = trim(preg_replace('/\s+/', ' ', strip_tags($rawJob)));
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
     * Check if seeker's city matches the job's city.
     *
     * "remote", "hybrid", "onsite" are JOB TYPES — not locations.
     * They are stripped as noise. Only real city names (Kathmandu, Lalitpur,
     * Pokhara, Birgunj, etc.) can produce a location match.
     *
     * Returns true only when both sides have at least one city token in common.
     */
    public function checkLocationMatch(Job $job): bool
    {
        $seekerRaw = mb_strtolower(trim($this->location ?? ''));
        $jobRaw    = mb_strtolower(trim($job->location ?? ''));

        if (!$seekerRaw || !$jobRaw) return false;

        // Words that must NEVER be treated as city names
        $noise = [
            // job-type / work-arrangement words
            'remote', 'hybrid', 'onsite', 'on-site', 'work from home',
            'wfh', 'anywhere', 'nationwide', 'flexible',
            // geographic noise (note: 'nepal' is removed so it can match country-wide locations as requested)
            'province', 'district', 'city', 'zone', 'municipality',
            'metropolitan', 'sub-metropolitan', 'rural', 'urban', 'ward',
            // common filler
            'the', 'of', 'and', 'in', 'at', 'near',
        ];

        // Extract real city tokens only
        $extractCityTokens = function (string $raw) use ($noise): array {
            $parts  = preg_split('/[,\/|\-]+/', $raw);
            $tokens = [];
            foreach ($parts as $part) {
                $tok = trim($part);
                if ($tok && !in_array($tok, $noise) && strlen($tok) > 1) {
                    $tokens[] = $tok;
                }
            }
            return $tokens;
        };

        $seekerTokens = $extractCityTokens($seekerRaw);
        $jobTokens    = $extractCityTokens($jobRaw);

        // No real city on either side → no location match
        if (empty($seekerTokens) || empty($jobTokens)) return false;

        // Match if any city token appears on both sides
        foreach ($seekerTokens as $st) {
            foreach ($jobTokens as $jt) {
                if ($st === $jt || str_contains($jt, $st) || str_contains($st, $jt)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if seeker has portfolio / projects evidence.
     * Strictly checks if the dedicated project/portfolio field is not empty.
     */
    public function hasPortfolio(): bool
    {
        $raw = trim(strip_tags($this->portfolio ?? ''));
        return !empty($raw);
    }

    /**
     * Check if ANY of the seeker's preferred roles aligns with the job domain.
     * Each role in the comma/newline-separated list is checked independently.
     * Returns [matched => bool, matched_role => string|null, matched_keyword => string|null]
     */
    public function checkDomainMatch(Job $job): array
    {
        $roles    = $this->preferredRoleArray();          // e.g. ['Backend Developer','Frontend Developer','UI/UX Designer']
        $jobTitle = mb_strtolower(trim($job->title ?? ''));
        $jobDesc  = mb_strtolower(substr(strip_tags($job->description ?? ''), 0, 500));

        // Domain keyword groups — shared vocabulary between role and job
        $domains = [
            'developer', 'engineer', 'designer', 'analyst', 'manager', 'scientist',
            'devops', 'admin', 'tester', 'qa', 'frontend', 'backend', 'fullstack',
            'mobile', 'web', 'data', 'security', 'cloud', 'architect', 'lead',
            'ui', 'ux', 'product', 'marketing', 'sales', 'support', 'operations',
        ];

        foreach ($roles as $role) {
            $roleLower = mb_strtolower(trim($role));
            if (!$roleLower) continue;

            // 1. Keyword overlap: both role AND job share a domain keyword
            foreach ($domains as $kw) {
                if (str_contains($roleLower, $kw)
                    && (str_contains($jobTitle, $kw) || str_contains($jobDesc, $kw))) {
                    return ['matched' => true, 'matched_role' => $role, 'matched_keyword' => $kw];
                }
            }

            // 2. Direct substring overlap between this role and the job title
            if (str_contains($jobTitle, $roleLower) || str_contains($roleLower, $jobTitle)) {
                return ['matched' => true, 'matched_role' => $role, 'matched_keyword' => null];
            }
        }

        return ['matched' => false, 'matched_role' => null, 'matched_keyword' => null];
    }

    /**
     * Compute composite score:
     *   FAISS (70%) + Location (10 pts) + Portfolio/Projects (10 pts) + Job Domain (10 pts) = 100
     *
     * Rules:
     *   - If the seeker has no meaningful profile data → 0% (no bonuses applied)
     *   - Bonuses (location / portfolio / domain) only apply when base score > 0
     */
    public function compositeScore(Job $job, int $faissScore): array
    {
        // ── Profile completeness guard ────────────────────────────────────────
        // A match score requires actual profile content.
        // If the user has filled in none of the core fields, return 0 immediately.
        $hasSkills     = !empty(trim(strip_tags($this->skills           ?? '')));
        $hasRole       = !empty(trim(strip_tags($this->preferred_role   ?? '')));
        $hasExperience = !empty(trim(strip_tags($this->experience_years ?? '')));
        $hasEducation  = !empty(trim(strip_tags($this->education        ?? '')));

        $emptyProfile = !$hasSkills && !$hasRole && !$hasExperience && !$hasEducation;

        if ($emptyProfile) {
            return [
                'faiss_score'         => 0,
                'faiss_weighted'      => 0,
                'faiss_max'           => 70,
                'location_match'      => false,
                'location_pts'        => 0,
                'location_max'        => 10,
                'portfolio_match'     => false,
                'portfolio_pts'       => 0,
                'portfolio_max'       => 10,
                'domain_match'        => false,
                'domain_matched_role' => null,
                'domain_pts'          => 0,
                'domain_max'          => 10,
                'final_score'         => 0,
            ];
        }
        // ─────────────────────────────────────────────────────────────────────

        // ── Skill-based fallback when FAISS returns 0 ─────────────────────────
        if ($faissScore <= 0) {
            $seekerSkills = $this->skillsArray();
            $jobSkills    = $job->skillsArray();

            $matchTerm = function ($term, $text) {
                $escaped = preg_quote($term, '/');
                $pattern = '/(?:^|[\s,.;:()\\/\\-\\[\\]{}*])' . $escaped . '(?:$|[\s,.;:()\\/\\-\\[\\]{}*])/i';
                return (bool) preg_match($pattern, $text);
            };

            $matchedCount = 0;
            foreach ($jobSkills as $js) {
                foreach ($seekerSkills as $ss) {
                    $stdJs = mb_strtolower(trim($js));
                    $stdSs = mb_strtolower(trim($ss));
                    if ($stdJs === 'apis') $stdJs = 'api';
                    if ($stdSs === 'apis') $stdSs = 'api';
                    if (in_array($stdJs, ['github', 'gitlab'])) $stdJs = 'git';
                    if (in_array($stdSs, ['github', 'gitlab'])) $stdSs = 'git';

                    if ($stdJs === $stdSs
                        || $matchTerm($stdJs, $ss)
                        || $matchTerm($stdSs, $js)) {
                        $matchedCount++;
                        break;
                    }
                }
            }
            if ($matchedCount > 0 && count($jobSkills) > 0) {
                $faissScore = (int) round(100 * ($matchedCount / count($jobSkills)));
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        $faissWeighted = (int) round($faissScore * 0.70);

        // ── Bonuses only apply when there is actual content-based score ───────
        // Prevents location/domain bonuses from inflating a zero-content match.
        if ($faissScore > 0) {
            $locationMatch  = $this->checkLocationMatch($job);
            $locationPts    = $locationMatch ? 10 : 0;

            $portfolioMatch = $this->hasPortfolio();
            $portfolioPts   = $portfolioMatch ? 10 : 0;

            $domainResult   = $this->checkDomainMatch($job);
            $domainMatch    = $domainResult['matched'];
            $domainPts      = $domainMatch ? 10 : 0;
        } else {
            $locationMatch  = false;  $locationPts  = 0;
            $portfolioMatch = false;  $portfolioPts = 0;
            $domainResult   = ['matched' => false, 'matched_role' => null, 'matched_keyword' => null];
            $domainMatch    = false;  $domainPts    = 0;
        }
        // ─────────────────────────────────────────────────────────────────────

        $finalScore = min(100, $faissWeighted + $locationPts + $portfolioPts + $domainPts);

        return [
            'faiss_score'         => $faissScore,
            'faiss_weighted'      => $faissWeighted,
            'faiss_max'           => 70,
            'location_match'      => $locationMatch,
            'location_pts'        => $locationPts,
            'location_max'        => 10,
            'portfolio_match'     => $portfolioMatch,
            'portfolio_pts'       => $portfolioPts,
            'portfolio_max'       => 10,
            'domain_match'        => $domainMatch,
            'domain_matched_role' => $domainResult['matched_role'],
            'domain_pts'          => $domainPts,
            'domain_max'          => 10,
            'final_score'         => $finalScore,
        ];
    }

    // ─── Match Details (post-FAISS breakdown for modal) ───────────────────────

    public function parseSkillExperiences(string $text): array
    {
        $textClean = preg_replace('/<[^>]*>/', "\n", $text);
        $segments = [];
        foreach (explode("\n", $textClean) as $line) {
            foreach (preg_split('/[;,]/', $line) as $part) {
                $part = trim($part);
                if ($part) {
                    $segments[] = $part;
                }
            }
        }
        
        $knownSkills = [
            "python", "php", "javascript", "react", "laravel", "sql", "css", "html", "docker", "django", 
            "postgresql", "node", "java", "c#", "c++", "ruby", "rails", "git", "bash", "linux", "aws", 
            "gcp", "azure", "tailwind", "rest", "api", "apis", "vue", "angular", "typescript", "nextjs", 
            "next.js", "mongodb", "mysql", "nosql", "sass", "bootstrap", "jquery", "graphql", "html5", "css3",
            "react.js", "vue.js", "node.js", "express", "express.js", "flask", "fastapi", "spring", "spring boot", "redis", "firebase",
            "machine learning", "data science", "data analysis", "pandas", "numpy", "tensorflow", "pytorch", 
            "nlp", "deep learning", "scikit-learn", "keras", "tableau", "power bi", "excel", "sheets", "matplotlib", "seaborn", "statistics",
            "devops", "kubernetes", "ci/cd", "jenkins", "ansible", "terraform", "vagrant", "nginx", "apache",
            "figma", "adobe xd", "sketch", "invision", "zeplin", "ui design", "ux design", "ui/ux", "ui/ux design", "ui", "ux",
            "wireframing", "prototyping", "user research", "ux research", "ui research", "usability testing", "interaction design",
            "design thinking", "information architecture", "user journey mapping", "personas", "design systems", "responsive design",
            "mobile app design", "website design", "dashboard design", "user experience design", "web design", "app design", "product design",
            "web optimization", "digital marketing", "marketing", "seo", "sem", "social media", "content writing", "photoshop", 
            "illustrator", "graphic design", "adobe illustrator", "adobe photoshop", "canva", "coreldraw", "indesign", "wordpress", "github", "gitlab",
            "communication skills", "problem solving", "attention to detail", "teamwork", "leadership", "agile", "scrum", "agile/scrum",
            "project management", "jira", "confluence"
        ];
        
        usort($knownSkills, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        $matchTerm = function ($term, $text) {
            $escaped = preg_quote($term, '/');
            $pattern = '/(?:^|[\s,.;:()\/\\-\\[\\]{}*])' . $escaped . '(?:$|[\s,.;:()\/\\-\\[\\]{}*])/i';
            return (bool) preg_match($pattern, $text);
        };
        
        $skillExps = [];
        foreach ($segments as $seg) {
            $segLower = strtolower($seg);
            
            $years = 0;
            if (preg_match('/(\d+)\+?\s*(?:years?|yrs?|y\b)/i', $segLower, $m)) {
                $years = (int) $m[1];
                $segClean = str_replace($m[0], ' ', $segLower);
            } else {
                $segClean = $segLower;
            }
            
            $segClean = preg_replace('/professional skills:?/i', '', $segClean);
            $segClean = preg_replace('/[•●▪\-*:]/', ' ', $segClean);
            $segClean = trim($segClean);
            
            $foundAny = false;
            foreach ($knownSkills as $ks) {
                if ($matchTerm($ks, $segClean)) {
                    $stdName = $ks;
                    if ($ks === "apis") $stdName = "api";
                    if (in_array($ks, ["github", "gitlab"])) $stdName = "git";
                    
                    $skillExps[$stdName] = max($skillExps[$stdName] ?? 0, $years);
                    $foundAny = true;
                }
            }
            
            if (!$foundAny && $years > 0) {
                $parts = preg_split('/\band\b|\bor\b|&/i', $segClean);
                foreach ($parts as $p) {
                    $pClean = trim($p);
                    if ($pClean && strlen($pClean) > 1) {
                        $skillExps[$pClean] = max($skillExps[$pClean] ?? 0, $years);
                    }
                }
            }
        }
        return $skillExps;
    }

    public function parseSkillRequirements(string $text): array
    {
        $textClean = preg_replace('/<[^>]*>/', "\n", $text);
        $segments = [];
        foreach (explode("\n", $textClean) as $line) {
            foreach (preg_split('/[;,]/', $line) as $part) {
                $part = trim($part);
                if ($part) {
                    $segments[] = $part;
                }
            }
        }
        
        $knownSkills = [
            "python", "php", "javascript", "react", "laravel", "sql", "css", "html", "docker", "django", 
            "postgresql", "node", "java", "c#", "c++", "ruby", "rails", "git", "bash", "linux", "aws", 
            "gcp", "azure", "tailwind", "rest", "api", "apis", "vue", "angular", "typescript", "nextjs", 
            "next.js", "mongodb", "mysql", "nosql", "sass", "bootstrap", "jquery", "graphql", "html5", "css3",
            "react.js", "vue.js", "node.js", "express", "express.js", "flask", "fastapi", "spring", "spring boot", "redis", "firebase",
            "machine learning", "data science", "data analysis", "pandas", "numpy", "tensorflow", "pytorch", 
            "nlp", "deep learning", "scikit-learn", "keras", "tableau", "power bi", "excel", "sheets", "matplotlib", "seaborn", "statistics",
            "devops", "kubernetes", "ci/cd", "jenkins", "ansible", "terraform", "vagrant", "nginx", "apache",
            "figma", "adobe xd", "sketch", "invision", "zeplin", "ui design", "ux design", "ui/ux", "ui/ux design", "ui", "ux",
            "wireframing", "prototyping", "user research", "ux research", "ui research", "usability testing", "interaction design",
            "design thinking", "information architecture", "user journey mapping", "personas", "design systems", "responsive design",
            "mobile app design", "website design", "dashboard design", "user experience design", "web design", "app design", "product design",
            "web optimization", "digital marketing", "marketing", "seo", "sem", "social media", "content writing", "photoshop", 
            "illustrator", "graphic design", "adobe illustrator", "adobe photoshop", "canva", "coreldraw", "indesign", "wordpress", "github", "gitlab",
            "communication skills", "problem solving", "attention to detail", "teamwork", "leadership", "agile", "scrum", "agile/scrum",
            "project management", "jira", "confluence"
        ];
        
        usort($knownSkills, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        $matchTerm = function ($term, $text) {
            $escaped = preg_quote($term, '/');
            $pattern = '/(?:^|[\s,.;:()\/\\-\\[\\]{}*])' . $escaped . '(?:$|[\s,.;:()\/\\-\\[\\]{}*])/i';
            return (bool) preg_match($pattern, $text);
        };
        
        // Extract global years
        $globalYears = 0;
        if (preg_match_all('/(\d+)\+?\s*(?:years?|yrs?|y\b)/i', $text, $matches)) {
            $globalYears = max(array_map('intval', $matches[1]));
        }
        
        $parsed = [];
        foreach ($segments as $seg) {
            $segLower = strtolower($seg);

            $years = 0;
            if (preg_match('/(\d+)\+?\s*(?:years?|yrs?|y\b)/i', $segLower, $m)) {
                $years    = (int) $m[1];
                $segClean = str_replace($m[0], ' ', $segLower);
            } else {
                $segClean = $segLower;
            }

            $segClean = preg_replace('/[•●▪\-*:]/', ' ', $segClean);
            $segClean = trim($segClean);

            foreach ($knownSkills as $ks) {
                if ($matchTerm($ks, $segClean)) {
                    $stdName = $ks;
                    if ($ks === "apis") $stdName = "api";
                    if (in_array($ks, ["github", "gitlab"])) $stdName = "git";
                    $parsed[$stdName] = max($parsed[$stdName] ?? 0, $years);
                }
            }
        }

        return $parsed;
    }

    public function matchDetails(Job $job, int $storedScore = 0): array
    {
        // ── Synonym groups ─────────────────────────────────────────────────────
        // Skills in the same group are treated as equivalent during comparison.
        // Add new rows here to expand coverage — keys are lowercased skill names.
        $synonymGroups = [
            // Research
            ['user research', 'ux research', 'research', 'usability research'],
            // Experience / UX
            ['user experience', 'ux', 'ux design', 'user experience design', 'experience design'],
            // Interface / UI
            ['user interface', 'ui', 'ui design', 'interface design'],
            // Web / Website
            ['web design', 'website design', 'web designing'],
            // Mobile / App
            ['mobile app design', 'app design', 'mobile design', 'mobile application design', 'mobile ui'],
            // Dashboard
            ['dashboard design', 'dashboard ui', 'data dashboard'],
            // Figma / Adobe XD (close tools — treat as equivalent for matching)
            ['figma', 'figma design'],
            ['adobe xd', 'xd', 'adobe experience design'],
            // Frontend scripting
            ['javascript', 'js', 'javascript (basic)', 'javascript (advanced)'],
            ['react', 'react.js', 'reactjs', 'react js'],
            ['node', 'node.js', 'nodejs', 'node js'],
            ['vue', 'vue.js', 'vuejs'],
            ['angular', 'angular.js', 'angularjs'],
            // HTML / CSS variants
            ['html', 'html5'],
            ['css', 'css3', 'cascading style sheets'],
            // Git variants
            ['git', 'github', 'gitlab', 'version control'],
            // API variants
            ['api', 'apis', 'rest api', 'rest', 'restful api', 'restful'],
            // Agile variants
            ['agile', 'scrum', 'agile/scrum', 'agile methodology', 'scrum methodology'],
            // SQL variants
            ['sql', 'mysql', 'postgresql', 'database', 'relational database'],
            // Design systems
            ['design systems', 'design system', 'component library', 'component libraries'],
            // Prototyping / Wireframing
            ['wireframing', 'wireframe', 'wireframes'],
            ['prototyping', 'prototype', 'prototypes'],
            // Photoshop / Illustrator
            ['photoshop', 'adobe photoshop', 'ps'],
            ['illustrator', 'adobe illustrator', 'ai'],
            // Responsive
            ['responsive design', 'responsive web design', 'responsive'],
            // PHP
            ['php', 'php8', 'php 8', 'php7', 'php 7'],
            // Python
            ['python', 'python3', 'python 3'],
        ];

        // Build a flat map: lowercase skill → group index
        $synonymMap = [];
        foreach ($synonymGroups as $gid => $terms) {
            foreach ($terms as $term) {
                $synonymMap[mb_strtolower(trim($term))] = $gid;
            }
        }

        // Returns synonym group ID or null if skill not in any group
        $synGroup = fn(string $s): ?int => $synonymMap[mb_strtolower(trim($s))] ?? null;

        // 1. Skills comparison using synonym groups + boundary-safe regex
        $seekerSkills    = $this->skillsArray();
        $jobSkills       = $job->skillsArray();
        $matchedSkills   = [];
        $unmatchedSkills = [];

        $matchTerm = function (string $term, string $text): bool {
            $escaped = preg_quote($term, '/');
            $pattern = '/(?:^|[\s,.;:()\\/\-\[\]{}*])' . $escaped . '(?:$|[\s,.;:()\\/\-\[\]{}*])/i';
            return (bool) preg_match($pattern, $text);
        };

        foreach ($jobSkills as $js) {
            $found = false;
            $stdJs = mb_strtolower(trim($js));
            $jsGid = $synGroup($stdJs);

            foreach ($seekerSkills as $ss) {
                $stdSs = mb_strtolower(trim($ss));
                $ssGid = $synGroup($stdSs);

                // Synonym group match — same group means semantically equivalent
                $synonymMatch = ($jsGid !== null && $ssGid !== null && $jsGid === $ssGid);

                if ($stdJs === $stdSs 
                    || $synonymMatch
                    || $matchTerm($stdJs, $ss) 
                    || $matchTerm($stdSs, $js)) {
                    $found = true; 
                    break;
                }
            }
            $found ? ($matchedSkills[] = $js) : ($unmatchedSkills[] = $js);
        }

        // 2. Experience comparison
        $seekerExp = $this->experienceYearsVal();
        $seekerSkillExps = $this->parseSkillExperiences($this->experience_years . ' ' . $this->skills);
        $jobSkillReqs = $this->parseSkillRequirements($job->requirements . ' ' . $job->key_skills . ' ' . $job->experience_required);
        
        // Compare each required skill's experience individually
        $specificMatches = [];
        $overallMatch = true;
        
        foreach ($jobSkillReqs as $skill => $reqYears) {
            if ($reqYears > 0) {
                $seekerYears = 0;
                $usingFallback = false;
                foreach ($seekerSkillExps as $ss => $sy) {
                    $ssGid = $synGroup($ss);
                    $skillGid = $synGroup($skill);
                    $synonymMatch = ($ssGid !== null && $skillGid !== null && $ssGid === $skillGid);

                    if ($ss === $skill 
                        || $synonymMatch
                        || $matchTerm($ss, $skill) 
                        || $matchTerm($skill, $ss)) {
                        $seekerYears = $sy;
                        break;
                    }
                }

                
                // Fallback to overall experience years if skill-specific years not stated
                if ($seekerYears == 0 && $seekerExp > 0) {
                    $seekerYears = $seekerExp;
                    $usingFallback = true;
                }
                
                $matched = $seekerYears >= $reqYears;
                if (!$matched) {
                    $overallMatch = false;
                }
                
                $specificMatches[] = [
                    'skill'    => $skill,
                    'required' => $reqYears,
                    'seeker'   => $seekerYears,
                    'matched'  => $matched,
                    'fallback' => $usingFallback,
                ];
            }
        }
        
        // Always compute jobExpRequired so it's available in the return array
        $jobExpRequired = 0;
        if ($job->experience_required) {
            preg_match('/\d+\+?/', $job->experience_required, $m);
            if (!empty($m)) $jobExpRequired = (int) $m[0];
        }

        if (count($specificMatches) > 0) {
            $msgParts = [];
            // Show at most 5 skill comparisons to keep message concise
            $shown = array_slice($specificMatches, 0, 5);
            foreach ($shown as $sm) {
                $status = $sm['matched'] ? '✓' : '✗';
                $yrLabel = ($sm['fallback'] ?? false) ? "~{$sm['seeker']}(overall)" : $sm['seeker'];
                $msgParts[] = ucfirst($sm['skill']) . ": {$yrLabel}/{$sm['required']} yr(s) {$status}";
            }
            if (count($specificMatches) > 5) {
                $msgParts[] = '… +' . (count($specificMatches) - 5) . ' more';
            }
            $expMatch = $overallMatch;
            $expMessage = "Skill Experience: " . implode(' | ', $msgParts);
        } else {
            // Fallback to general experience years comparison
            $expMatch   = $seekerExp >= $jobExpRequired;
            $expMessage = $expMatch
                ? "Candidate has {$seekerExp} yr(s) of experience — meets the requirement of " . ($job->experience_required ?: 'Fresher') . "."
                : "Candidate has {$seekerExp} yr(s); job requires " . ($job->experience_required ?: 'Fresher') . ".";
        }

        // 3. Composite post-FAISS components
        $locationMatch  = $this->checkLocationMatch($job);
        $portfolioMatch = $this->hasPortfolio();
        $domainResult   = $this->checkDomainMatch($job);
        $domainMatch    = $domainResult['matched'];
        $matchedRole    = $domainResult['matched_role'];

        // 4. Back-calculate composite breakdown from stored composite score
        $locationPts   = $locationMatch  ? 10 : 0;
        $portfolioPts  = $portfolioMatch ? 10 : 0;
        $domainPts     = $domainMatch    ? 10 : 0;
        $bonusPts      = $locationPts + $portfolioPts + $domainPts;
        $faissWeighted = max(0, $storedScore - $bonusPts);
        $faissApprox   = min(100, (int) round($faissWeighted / 0.70));

        if ($faissApprox <= 0 && count($matchedSkills) > 0 && count($jobSkills) > 0) {
            $faissApprox = (int) round(100 * (count($matchedSkills) / count($jobSkills)));
            $faissWeighted = (int) round($faissApprox * 0.70);
            if ($storedScore > 0) {
                $storedScore = $faissWeighted + $bonusPts;
            }
        }

        $composite = [
            'faiss_score'         => $faissApprox,
            'faiss_weighted'      => $faissWeighted,
            'faiss_max'           => 70,
            'location_match'      => $locationMatch,
            'location_pts'        => $locationPts,
            'location_max'        => 10,
            'portfolio_match'     => $portfolioMatch,
            'portfolio_pts'       => $portfolioPts,
            'portfolio_max'       => 10,
            'domain_match'        => $domainMatch,
            'domain_matched_role' => $matchedRole,
            'domain_pts'          => $domainPts,
            'domain_max'          => 10,
            'final_score'         => $storedScore ?: ($faissWeighted + $bonusPts),
        ];

        $allRoles   = $this->preferredRoleArray();
        $seekerRoleLabel = $allRoles ? implode(', ', $allRoles) : 'Not Specified';

        return [
            'matched_skills'      => $matchedSkills,
            'unmatched_skills'    => $unmatchedSkills,
            'location_match'      => $locationMatch,
            'role_match'          => $domainMatch,
            'role_matched_role'   => $matchedRole,
            'seeker_roles'        => $allRoles,
            'seeker_skills'       => $seekerSkills,
            'job_skills'          => $jobSkills,
            'seeker_location'     => $this->location ?? 'Not Specified',
            'job_location'        => $job->location,
            'seeker_role'         => $seekerRoleLabel,
            'job_title'           => $job->title,
            'exp_match'           => $expMatch,
            'exp_message'         => $expMessage,
            'seeker_exp'          => $seekerExp,
            'job_exp'             => $jobExpRequired,
            'portfolio_match'     => $portfolioMatch,
            'has_cv'              => !empty($this->cv_path),
            'composite'           => $composite,
        ];
    }
}

