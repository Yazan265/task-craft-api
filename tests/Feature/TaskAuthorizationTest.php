<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase; 

    protected $owner;
    protected $assignedUser;
    protected $randomUser;
    protected $project;
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->assignedUser = User::factory()->create();
        $this->randomUser = User::factory()->create();

        $this->project = Project::factory()->create([
            'user_id' => $this->owner->id
        ]);

        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
            'title' => 'Original Title',
            'assigned_to' => $this->assignedUser->id,
            'status' => 'pending'
        ]);
    }

    #[Test]
    public function project_owner_can_update_full_task_details()
    {
        $response = $this->actingAs($this->owner)
            ->putJson("/api/tasks/{$this->task->id}", [
                'title' => 'Updated Title By Owner',
                'status' => 'in_progress'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'title' => 'Updated Title By Owner',
            'status' => 'in_progress'
        ]);
    }

    #[Test]
    public function assigned_user_can_only_update_status()
    {
        $response = $this->actingAs($this->assignedUser)
            ->putJson("/api/tasks/{$this->task->id}", [
                'title' => 'Hacked Title Attempt',
                'status' => 'completed'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'title' => 'Original Title', 
            'status' => 'completed'    
        ]);
    }

    #[Test]
    public function random_user_cannot_update_task()
    {
        $response = $this->actingAs($this->randomUser)
            ->putJson("/api/tasks/{$this->task->id}", [
                'status' => 'completed'
            ]);

        // يتوقع منع الوصول 403 Forbidden
        $response->assertStatus(403);
    }

    #[Test]
    public function only_project_owner_can_delete_task()
    {
        $this->actingAs($this->assignedUser)
            ->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('tasks', [
            'id' => $this->task->id
        ]);
    }
}