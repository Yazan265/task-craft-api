<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskAssignedNotification;
use PHPUnit\Framework\Attributes\Test;

class TaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $assignedUser;
    protected $randomUser;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->assignedUser = User::factory()->create();
        $this->randomUser = User::factory()->create();

        $this->project = Project::factory()->create([
            'user_id' => $this->owner->id,
            'name'    => 'Test Project',
        ]);
    }

    #[Test]
    public function it_sends_notification_when_task_is_created_and_assigned()
    {
        Notification::fake();
        $this->project->members()->attach($this->assignedUser->id);

       $response = $this->actingAs($this->owner)
    ->postJson("/api/projects/{$this->project->id}/tasks", [
        'title'       => 'New Feature Task',
        'description' => 'Task description',
        'assigned_to' => $this->assignedUser->id,
        'status'      => 'pending',
    ]);

        $response->assertStatus(201);

        Notification::assertSentTo(
            [$this->assignedUser],
            TaskAssignedNotification::class
        );
    }
    #[Test]
public function it_sends_notification_when_task_is_reassigned_to_another_user()
{
    Notification::fake();
    $this->project->members()->attach($this->randomUser->id);

    $task = Task::factory()->create([
        'project_id'  => $this->project->id,
        'assigned_to' => $this->assignedUser->id,
    ]);

    $response = $this->actingAs($this->owner)
        ->putJson("/api/tasks/{$task->id}", [
            'title'       => $task->title,
            'assigned_to' => $this->randomUser->id, 
        ]);

    $response->assertStatus(200);

    Notification::assertSentTo(
        [$this->randomUser],
        TaskAssignedNotification::class
    );

    Notification::assertNotSentTo(
        [$this->assignedUser],
        TaskAssignedNotification::class
    );
}
}