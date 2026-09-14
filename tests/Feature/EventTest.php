<?php

namespace Tests\Feature;

use App\Events\CommentCreated;
use App\Events\TaskAssigned;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EventTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_task_assigned_event_when_task_is_assigned_to_user()
    {
        Event::fake();

        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        // ربط المستخدم بالملف كعضو في المشروع ليتجاوز الـ Validation
        $project->members()->attach($assignee->id);

        $response = $this->actingAs($owner, 'api')
            ->postJson("/api/projects/{$project->id}/tasks", [
                'title' => 'New Task',
                'description' => 'Task description',
                'assigned_to' => $assignee->id,
                'priority' => 'high',
                'due_date' => now()->addDays(2)->format('Y-m-d'),
            ]);

        $response->assertStatus(201);

        // التحقق من إطلاق حدث TaskAssigned
        Event::assertDispatched(TaskAssigned::class, function ($event) use ($assignee) {
            return $event->task->assigned_to === $assignee->id;
        });
    }

    #[Test]
    public function it_dispatches_comment_created_event_when_comment_is_added()
    {
        Event::fake();

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/tasks/{$task->id}/comments", [
                'content' => 'This is a test comment',
            ]);

        $response->assertStatus(201);

        // التحقق من إطلاق حدث CommentCreated
        Event::assertDispatched(CommentCreated::class, function ($event) use ($task) {
            return $event->comment->task_id === $task->id;
        });
    }
}