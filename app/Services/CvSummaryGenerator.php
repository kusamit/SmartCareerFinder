<?php

namespace App\Services;

use App\Models\User;

class CvSummaryGenerator
{
    /**
     * Generate a concise, high-impact executive summary specifically for CV output.
     * Focuses on professional experience, dedication, learning, problem solving, and technical value.
     * Note: This does NOT modify or overwrite the user's stored profile_summary.
     *
     * @param User $user
     * @return string
     */
    public static function generate(User $user): string
    {
        $hasRole = !empty(trim(strip_tags($user->preferred_role ?? '')));
        $hasSkills = !empty($user->skillsArray());
        $hasExp = !empty(trim(strip_tags($user->experience_years ?? '')));
        $hasEdu = ($user->educations && $user->educations->isNotEmpty()) || !empty(trim(strip_tags($user->education ?? '')));

        // If no profile details are filled, do not generate a summary
        if (!$hasRole && !$hasSkills && !$hasExp && !$hasEdu) {
            return '';
        }

        $role = trim(strip_tags($user->preferred_role ?? '')) ?: 'IT & Technology Professional';

        // Extract top skills
        $skillsArr = $user->skillsArray();
        $topSkills = !empty($skillsArr) 
            ? implode(', ', array_slice($skillsArr, 0, 5)) 
            : 'software engineering and technical systems';

        // Extract experience years
        $expYears = $user->experienceYearsVal();
        $expPhrase = $expYears > 0 
            ? "with {$expYears}+ years of working experience" 
            : "with hands-on practical experience";

        // Bulletproof executive content focusing on dedication, learning & problem solving
        $p1 = "Experienced and dedicated {$role} {$expPhrase} specializing in {$topSkills}.";
        $p2 = "Demonstrates a strong track record in solving complex technical problems, building reliable systems, and delivering high-quality operational solutions.";
        $p3 = "Known for a proactive learning mindset, strong attention to detail, adaptability, and clear professional communication.";
        $p4 = "Motivated to apply technical expertise and continuous learning to drive efficiency and support business growth.";

        return "{$p1} {$p2} {$p3} {$p4}";
    }
}
