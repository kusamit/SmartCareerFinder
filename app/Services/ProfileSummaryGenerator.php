<?php

namespace App\Services;

use App\Models\User;

class ProfileSummaryGenerator
{
    /**
     * Generate a natural language profile summary for a given User model.
     *
     * @param User $user
     * @return string
     */
    public static function generate(User $user): string
    {
        $skills  = implode(', ', $user->skillsArray()) ?: 'various skills';
        $expText = trim(strip_tags($user->experience_years ?? ''));
        $expDesc = is_numeric($expText) ? "{$expText} year(s) of experience" : "experience: {$expText}";
        $role    = trim(strip_tags($user->preferred_role ?? '')) ?: 'a suitable role';
        $loc     = trim(strip_tags($user->location ?? '')) ?: 'any location';
        
        $eduParts = [];
        foreach ($user->educations as $e) {
            $eduParts[] = "{$e->degree} in {$e->field_of_study} from {$e->school} ({$e->start_year} - {$e->end_year})";
        }
        $edu = implode(', ', $eduParts) ?: trim(strip_tags($user->education ?? ''));

        return "{$user->name} is a professional with {$expDesc} in {$skills}. "
            . ($edu ? "Education: {$edu}. " : '')
            . "Looking for {$role} based in {$loc}.";
    }
}
