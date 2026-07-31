<?php

namespace App\Services;

class Recommendation
{
    private static $skillToCategory = [
        // Frontend
        'html5' => 'Frontend Development',
        'css3' => 'Frontend Development',
        'html' => 'Frontend Development',
        'css' => 'Frontend Development',
        'javascript' => 'Frontend Development',
        'javascript (es6+)' => 'Frontend Development',
        'es6' => 'Frontend Development',
        'react.js' => 'Frontend Development',
        'react' => 'Frontend Development',
        'vue' => 'Frontend Development',
        'vue.js' => 'Frontend Development',
        'angular' => 'Frontend Development',
        'tailwind' => 'Frontend Development',
        'tailwind css' => 'Frontend Development',
        'bootstrap' => 'Frontend Development',
        'typescript' => 'Frontend Development',
        'basic typescript' => 'Frontend Development',
        'nextjs' => 'Frontend Development',
        'next.js' => 'Frontend Development',
        'basic next.js' => 'Frontend Development',
        'sass' => 'Frontend Development',
        'jquery' => 'Frontend Development',
        'redux' => 'Frontend Development',
        'redux toolkit' => 'Frontend Development',
        'redux toolkit / zustand' => 'Frontend Development',
        'zustand' => 'Frontend Development',
        'material ui' => 'Frontend Development',
        'material ui / shadcn ui' => 'Frontend Development',
        'shadcn ui' => 'Frontend Development',
        'react query' => 'Frontend Development',
        'react query / tanstack query' => 'Frontend Development',
        'tanstack query' => 'Frontend Development',
        'webpack' => 'Frontend Development',
        'vite' => 'Frontend Development',
        'webpack / vite' => 'Frontend Development',

        // Backend
        'php' => 'Backend Development',
        'laravel' => 'Backend Development',
        'python' => 'Backend Development',
        'django' => 'Backend Development',
        'flask' => 'Backend Development',
        'node' => 'Backend Development',
        'node.js' => 'Backend Development',
        'java' => 'Backend Development',
        'spring' => 'Backend Development',
        'spring boot' => 'Backend Development',
        'c#' => 'Backend Development',
        'c++' => 'Backend Development',
        'ruby' => 'Backend Development',
        'rails' => 'Backend Development',
        'ruby on rails' => 'Backend Development',
        'rest' => 'Backend Development',
        'api' => 'Backend Development',
        'apis' => 'Backend Development',
        'rest api' => 'Backend Development',
        'rest apis' => 'Backend Development',
        'graphql' => 'Backend Development',
        'sql' => 'Backend Development',
        'mysql' => 'Backend Development',
        'postgresql' => 'Backend Development',
        'mongodb' => 'Backend Development',
        'nosql' => 'Backend Development',

        // DevOps & Cloud
        'docker' => 'DevOps & Infrastructure',
        'kubernetes' => 'DevOps & Infrastructure',
        'aws' => 'DevOps & Infrastructure',
        'gcp' => 'DevOps & Infrastructure',
        'azure' => 'DevOps & Infrastructure',
        'cloud' => 'DevOps & Infrastructure',
        'ci/cd' => 'DevOps & Infrastructure',
        'ci/cd fundamentals' => 'DevOps & Infrastructure',
        'jenkins' => 'DevOps & Infrastructure',
        'ansible' => 'DevOps & Infrastructure',
        'terraform' => 'DevOps & Infrastructure',
        'vagrant' => 'DevOps & Infrastructure',
        'nginx' => 'DevOps & Infrastructure',
        'apache' => 'DevOps & Infrastructure',
        'git' => 'DevOps & Infrastructure',
        'git & github' => 'DevOps & Infrastructure',
        'github' => 'DevOps & Infrastructure',
        'gitlab' => 'DevOps & Infrastructure',
        'bash' => 'DevOps & Infrastructure',
        'linux' => 'DevOps & Infrastructure',

        // Data Science & ML
        'machine learning' => 'Data Science & Machine Learning',
        'data science' => 'Data Science & Machine Learning',
        'data analysis' => 'Data Science & Machine Learning',
        'pandas' => 'Data Science & Machine Learning',
        'numpy' => 'Data Science & Machine Learning',
        'tensorflow' => 'Data Science & Machine Learning',
        'pytorch' => 'Data Science & Machine Learning',
        'nlp' => 'Data Science & Machine Learning',
        'deep learning' => 'Data Science & Machine Learning',
        'scikit-learn' => 'Data Science & Machine Learning',
        'keras' => 'Data Science & Machine Learning',
        'tableau' => 'Data Science & Machine Learning',
        'power bi' => 'Data Science & Machine Learning',
        'excel' => 'Data Science & Machine Learning',
        'sheets' => 'Data Science & Machine Learning',
        'matplotlib' => 'Data Science & Machine Learning',
        'seaborn' => 'Data Science & Machine Learning',
        'statistics' => 'Data Science & Machine Learning',

        // Design & UX
        'ui/ux' => 'Design & UX',
        'ui' => 'Design & UX',
        'ux' => 'Design & UX',
        'ui design' => 'Design & UX',
        'ux design' => 'Design & UX',
        'ui/ux design' => 'Design & UX',
        'figma' => 'Design & UX',
        'adobe xd' => 'Design & UX',
        'sketch' => 'Design & UX',
        'invision' => 'Design & UX',
        'zeplin' => 'Design & UX',
        'wireframing' => 'Design & UX',
        'prototyping' => 'Design & UX',
        'user research' => 'Design & UX',
        'usability testing' => 'Design & UX',
        'interaction design' => 'Design & UX',
        'design thinking' => 'Design & UX',
        'wordpress' => 'Design & UX',
        'webflow' => 'Design & UX',

        // Graphics Design
        'graphic design' => 'Graphics Design',
        'graphics design' => 'Graphics Design',
        'photoshop' => 'Graphics Design',
        'adobe photoshop' => 'Graphics Design',
        'illustrator' => 'Graphics Design',
        'adobe illustrator' => 'Graphics Design',
        'indesign' => 'Graphics Design',
        'adobe indesign' => 'Graphics Design',
        'coreldraw' => 'Graphics Design',
        'canva' => 'Graphics Design',
        'logo design' => 'Graphics Design',
        'brand identity' => 'Graphics Design',
        'branding' => 'Graphics Design',
        'typography' => 'Graphics Design',
        'color theory' => 'Graphics Design',
        'print design' => 'Graphics Design',
        'banner design' => 'Graphics Design',
        'poster design' => 'Graphics Design',
        'flyer design' => 'Graphics Design',
        'packaging design' => 'Graphics Design',
        'motion graphics' => 'Graphics Design',
        'after effects' => 'Graphics Design',
        'adobe after effects' => 'Graphics Design',

        // Video Editing & Production
        'video editing' => 'Video Editing & Production',
        'video production' => 'Video Editing & Production',
        'videography' => 'Video Editing & Production',
        'premiere pro' => 'Video Editing & Production',
        'adobe premiere' => 'Video Editing & Production',
        'adobe premiere pro' => 'Video Editing & Production',
        'final cut pro' => 'Video Editing & Production',
        'davinci resolve' => 'Video Editing & Production',
        'capcut' => 'Video Editing & Production',
        'filmora' => 'Video Editing & Production',
        'color grading' => 'Video Editing & Production',
        'color correction' => 'Video Editing & Production',
        'video color grading' => 'Video Editing & Production',
        'cinematography' => 'Video Editing & Production',
        'storytelling' => 'Video Editing & Production',
        'youtube content creation' => 'Video Editing & Production',
        'reels editing' => 'Video Editing & Production',
        'short video editing' => 'Video Editing & Production',
        'animation' => 'Video Editing & Production',
        '2d animation' => 'Video Editing & Production',
        '3d animation' => 'Video Editing & Production',
        'blender' => 'Video Editing & Production',

        // Digital Marketing
        'digital marketing' => 'Digital Marketing',
        'marketing' => 'Digital Marketing',
        'online marketing' => 'Digital Marketing',
        'growth hacking' => 'Digital Marketing',
        'performance marketing' => 'Digital Marketing',
        'affiliate marketing' => 'Digital Marketing',
        'influencer marketing' => 'Digital Marketing',
        'content marketing' => 'Digital Marketing',
        'content strategy' => 'Digital Marketing',
        'content creation' => 'Digital Marketing',
        'content writing' => 'Digital Marketing',
        'copywriting' => 'Digital Marketing',
        'seo' => 'Digital Marketing',
        'search engine optimization' => 'Digital Marketing',
        'on-page seo' => 'Digital Marketing',
        'off-page seo' => 'Digital Marketing',
        'technical seo' => 'Digital Marketing',
        'local seo' => 'Digital Marketing',
        'sem' => 'Digital Marketing',
        'search engine marketing' => 'Digital Marketing',
        'google ads' => 'Digital Marketing',
        'google adwords' => 'Digital Marketing',
        'ppc' => 'Digital Marketing',
        'pay-per-click' => 'Digital Marketing',
        'facebook ads' => 'Digital Marketing',
        'meta ads' => 'Digital Marketing',
        'instagram ads' => 'Digital Marketing',
        'tiktok ads' => 'Digital Marketing',
        'social media' => 'Digital Marketing',
        'social media marketing' => 'Digital Marketing',
        'social media management' => 'Digital Marketing',
        'community management' => 'Digital Marketing',
        'email marketing' => 'Digital Marketing',
        'email campaigns' => 'Digital Marketing',
        'newsletter' => 'Digital Marketing',
        'mailchimp' => 'Digital Marketing',
        'hubspot' => 'Digital Marketing',
        'marketing automation' => 'Digital Marketing',
        'crm' => 'Digital Marketing',
        'google analytics' => 'Digital Marketing',
        'analytics' => 'Digital Marketing',
        'conversion rate optimization' => 'Digital Marketing',
        'cro' => 'Digital Marketing',
        'a/b testing' => 'Digital Marketing',
        'funnel marketing' => 'Digital Marketing',
        'lead generation' => 'Digital Marketing',
        'brand management' => 'Digital Marketing',
        'public relations' => 'Digital Marketing',
        'pr' => 'Digital Marketing',

        // PM & Methodologies
        'agile/scrum methodology' => 'Agile & Project Management',
        'agile' => 'Agile & Project Management',
        'scrum' => 'Agile & Project Management',
        'agile/scrum' => 'Agile & Project Management',
        'project management' => 'Agile & Project Management',
        'communication skills' => 'Agile & Project Management',
        'problem solving' => 'Agile & Project Management',
        'attention to detail' => 'Agile & Project Management',
    ];

    /**
     * Categorize unmatched skills and recommend courses.
     *
     * @param array $unmatchedSkills
     * @return array
     */
    public static function categorizeSkills(array $unmatchedSkills): array
    {
        if (empty($unmatchedSkills)) {
            return [];
        }

        try {
            $scriptPath = escapeshellarg(base_path('python/recommend.py'));
            $escapedSkills = array_map('escapeshellarg', array_values($unmatchedSkills));
            $cmd = "python {$scriptPath} " . implode(' ', $escapedSkills);
            $output = shell_exec($cmd);
            
            if ($output) {
                $results = json_decode($output, true);
                if (is_array($results) && !empty($results)) {
                    return $results;
                }
            }
        } catch (\Exception $e) {
            // Fallback automatically
        }

        // --- FALLBACK (Deterministic Exact Matching) ---
        $categories = [];

        foreach ($unmatchedSkills as $skill) {
            $normalizedSkill = strtolower(trim($skill));
            $matchedCategory = 'Other Professional Skills';

            foreach (self::$skillToCategory as $key => $category) {
                if ($normalizedSkill === $key || str_contains($normalizedSkill, $key) || str_contains($key, $normalizedSkill)) {
                    $matchedCategory = $category;
                    break;
                }
            }

            $categories[$matchedCategory][] = $skill;
        }

        $result = [];
        foreach ($categories as $catName => $skills) {
            $result[] = [
                'category' => $catName,
                'skills' => $skills,
                'course' => self::getRecommendedCourse($catName, $skills)
            ];
        }

        return $result;
    }

    /**
     * Get a recommended course title based on category and skills.
     *
     * @param string $category
     * @param array $skills
     * @return string
     */
    private static function getRecommendedCourse(string $category, array $skills): string
    {
        $skillsStr = implode(', ', $skills);
        switch ($category) {
            case 'Frontend Development':
                return "Complete Front-end Development Course (covers: {$skillsStr})";
            case 'Backend Development':
                return "Advanced Backend Engineering Path (covers: {$skillsStr})";
            case 'DevOps & Infrastructure':
                return "DevOps, Git & CI/CD Masterclass (covers: {$skillsStr})";
            case 'Data Science & Machine Learning':
                return "Data Science & AI/ML Bootcamp (covers: {$skillsStr})";
            case 'Design & UX':
                return "UI/UX Design & Prototyping Masterclass (covers: {$skillsStr})";
            case 'Graphics Design':
                return "Professional Graphics Design with Adobe Suite & Canva (covers: {$skillsStr})";
            case 'Video Editing & Production':
                return "Video Editing & Content Production Bootcamp (covers: {$skillsStr})";
            case 'Digital Marketing':
                return "Complete Digital Marketing & Growth Strategy Course (covers: {$skillsStr})";
            case 'Agile & Project Management':
                return "Agile, Scrum & Leadership Certification (covers: {$skillsStr})";
            default:
                return "Specialized Professional Skill Building (covers: {$skillsStr})";
        }
    }
}
