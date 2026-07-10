<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear Faiss mock outputs or scripts in tests to avoid shell command overhead
        $this->mockPythonMatch();
    }

    private function mockPythonMatch()
    {
        // We can mock shell calls if needed, but since our shell_exec in ProviderController runs python, 
        // we'll make sure it doesn't fail. The test runs in the local workspace.
    }

    public function test_provider_can_create_job_posting_without_location()
    {
        $provider = User::create([
            'name' => 'Test Provider',
            'email' => 'provider@example.com',
            'password' => bcrypt('password'),
            'role' => 'provider',
            'company_name' => 'Acme Corp'
        ]);

        $response = $this->withSession(['user_id' => $provider->id, 'user_role' => 'provider'])
            ->post(route('provider.jobs.store'), [
                'title' => 'Senior Laravel Developer',
                'type' => 'full-time',
                'experience_required' => '3+ Years',
                'salary_range' => 'NPR 100,000',
                'key_skills' => '<p>PHP, Laravel, MySQL</p>',
                'description' => '<p>Detailed description of the responsibilities.</p>',
                'requirements' => '<p>Detailed requirements, degree, etc.</p>',
                // location is optional/nullable
                'location' => null
            ]);

        $response->assertRedirect(route('provider.jobs'));
        $response->assertSessionHas('success', 'Job posted successfully!');

        $this->assertDatabaseHas('jobs', [
            'title' => 'Senior Laravel Developer',
            'location' => null,
            'company' => 'Acme Corp'
        ]);
    }

    public function test_provider_can_update_job_posting()
    {
        $provider = User::create([
            'name' => 'Test Provider',
            'email' => 'provider@example.com',
            'password' => bcrypt('password'),
            'role' => 'provider',
            'company_name' => 'Acme Corp'
        ]);

        $job = Job::create([
            'user_id' => $provider->id,
            'title' => 'Old Title',
            'company' => 'Acme Corp',
            'location' => 'Kathmandu',
            'type' => 'full-time',
            'description' => 'Old description',
            'requirements' => 'Old requirements',
            'key_skills' => 'Old skills',
            'status' => 'open'
        ]);

        $response = $this->withSession(['user_id' => $provider->id, 'user_role' => 'provider'])
            ->put(route('provider.jobs.update', $job->id), [
                'title' => 'Updated Title',
                'type' => 'remote',
                'experience_required' => '5+ Years',
                'salary_range' => 'NPR 150,000',
                'key_skills' => '<p>PHP, Laravel, Docker</p>',
                'description' => '<p>Updated description of the responsibilities.</p>',
                'requirements' => '<p>Updated requirements, degree, etc.</p>',
                'location' => 'Pokhara'
            ]);

        $response->assertRedirect(route('provider.jobs'));
        $response->assertSessionHas('success', 'Job updated successfully!');

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'title' => 'Updated Title',
            'location' => 'Pokhara',
            'type' => 'remote'
        ]);
    }
}
