<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResourceStructureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function project_endpoint_returns_valid_resource_structure()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $token = auth()->guard('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'title',
                         'description',
                         'owner' => ['id', 'name', 'email'],
                         'created_at',
                         'updated_at'
                     ]
                 ]);
    }
}