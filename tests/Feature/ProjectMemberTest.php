<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function owner_can_add_a_member_to_their_project()
    {
        $owner = User::factory()->create();
        $newMember = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $project->members()->attach($newMember->id, ['role' => 'member']);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $newMember->id,
            'role'       => 'member',
        ]);
    }

    #[Test]
    public function non_member_cannot_access_project_tasks()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($stranger, 'api')
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function member_can_access_project_tasks()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $project->members()->attach($member->id);

        $response = $this->actingAs($member, 'api')
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
    }
}