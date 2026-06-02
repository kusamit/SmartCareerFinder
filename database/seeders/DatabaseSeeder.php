<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo Job Provider
        $provider = User::create([
            'name'                => 'TechCorp Nepal',
            'email'               => 'provider@demo.com',
            'password'            => Hash::make('password'),
            'role'                => 'provider',
            'company_name'        => 'TechCorp Nepal Pvt. Ltd.',
            'company_website'     => 'https://techcorp.com.np',
            'company_description' => 'Leading tech company in Nepal',
        ]);

        // Demo Job Seeker
        $seeker = User::create([
            'name'             => 'Ram Prasad',
            'email'            => 'seeker@demo.com',
            'password'         => Hash::make('password'),
            'role'             => 'seeker',
            'skills'           => 'Python, Django, REST API, PostgreSQL, Docker',
            'education'        => 'BSc Computer Science, TU',
            'experience_years' => 3,
            'preferred_role'   => 'Backend Developer',
            'location'         => 'Kathmandu',
        ]);
        $seeker->profile_summary = $seeker->generateProfileSummary();
        $seeker->save();

        // Sample Jobs
        $jobs = [
            [
                'title'               => 'Senior Python Developer',
                'location'            => 'Kathmandu',
                'type'                => 'full-time',
                'description'         => 'We are looking for a skilled Python developer to join our growing team. You will work on building scalable APIs and backend systems for our SaaS platform.',
                'requirements'        => 'Strong Python skills, experience with Django/FastAPI, PostgreSQL, REST APIs, Docker knowledge is a plus.',
                'experience_required' => '3+ years',
                'salary_range'        => 'NPR 80,000–120,000',
                'key_skills'          => 'Python, Django, REST API, PostgreSQL, Docker',
                'status'              => 'open',
            ],
            [
                'title'               => 'React Frontend Developer',
                'location'            => 'Remote',
                'type'                => 'remote',
                'description'         => 'Join our product team to build beautiful, responsive user interfaces using React and modern web technologies.',
                'requirements'        => 'Proficiency in React, JavaScript, HTML, CSS, Tailwind. Experience with REST API integration.',
                'experience_required' => '2+ years',
                'salary_range'        => 'NPR 60,000–90,000',
                'key_skills'          => 'React, JavaScript, HTML, CSS, Tailwind, REST API',
                'status'              => 'open',
            ],
            [
                'title'               => 'Machine Learning Engineer',
                'location'            => 'Kathmandu',
                'type'                => 'full-time',
                'description'         => 'Work on exciting ML projects including NLP, recommendation systems, and computer vision applications.',
                'requirements'        => 'Python, TensorFlow or PyTorch, experience with ML pipelines, strong math background.',
                'experience_required' => '2+ years',
                'salary_range'        => 'NPR 100,000–150,000',
                'key_skills'          => 'Python, Machine Learning, TensorFlow, NLP, Data Science',
                'status'              => 'open',
            ],
            [
                'title'               => 'DevOps Engineer',
                'location'            => 'Kathmandu / Remote',
                'type'                => 'full-time',
                'description'         => 'Manage and improve our CI/CD pipelines, cloud infrastructure, and deployment processes.',
                'requirements'        => 'Linux, Docker, Kubernetes, AWS/GCP, CI/CD pipelines (Jenkins/GitHub Actions).',
                'experience_required' => '3+ years',
                'salary_range'        => 'NPR 90,000–130,000',
                'key_skills'          => 'Docker, Kubernetes, AWS, Linux, CI/CD, DevOps',
                'status'              => 'open',
            ],
            [
                'title'               => 'Laravel PHP Developer',
                'location'            => 'Lalitpur',
                'type'                => 'full-time',
                'description'         => 'Develop and maintain web applications using Laravel framework for our e-commerce clients.',
                'requirements'        => 'PHP, Laravel, MySQL, REST API, basic JavaScript knowledge.',
                'experience_required' => '1+ years',
                'salary_range'        => 'NPR 40,000–70,000',
                'key_skills'          => 'PHP, Laravel, MySQL, JavaScript, REST API',
                'status'              => 'closed',
            ],
        ];

        foreach ($jobs as $jobData) {
            Job::create(array_merge($jobData, ['user_id' => $provider->id, 'company' => $provider->company_name]));
        }

        echo "✅ Seeded: provider@demo.com / seeker@demo.com (password: password)\n";
    }
}
