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
    #[Test]
    public function cannot_assign_task_to_user_who_is_not_project_member_or_owner()
    {
        $stranger = User::factory()->create();

        // محاولة إنشاء مهمة وإسنادها لشخص غريب
        $response = $this->actingAs($this->owner)
            ->postJson("/api/projects/{$this->project->id}/tasks", [
                'title' => 'Task for stranger',
                'assigned_to' => $stranger->id,
                'priority' => 'medium'
            ]);

        // يتوقع الرفض بخطأ 422 Unprocessable Entity
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    }

    #[Test]
    public function can_assign_task_to_a_valid_project_member()
    {
        $member = User::factory()->create();
        
        // إدراج المستخدم كعضو في المشروع
        $this->project->members()->attach($member->id, ['role' => 'member']);

        // إسناد المهمة للعضو المضاف
        $response = $this->actingAs($this->owner)
            ->postJson("/api/projects/{$this->project->id}/tasks", [
                'title' => 'Task for valid member',
                'assigned_to' => $member->id,
                'priority' => 'medium'
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Task for valid member',
            'assigned_to' => $member->id
        ]);
    }
}