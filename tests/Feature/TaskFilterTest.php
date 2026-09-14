<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use PHPUnit\Framework\Attributes\Test;


class TaskFilterTest extends TestCase
{
    use RefreshDatabase;
 #[Test]
    public function user_can_filter_tasks_by_status()
    {
        // 1. الترتيب (Arrange)
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $completedTask = Task::factory()->create([
            'project_id' => $project->id,
            'status'     => 'completed',
            'title'      => 'Completed Task'
        ]);

        $inProgressTask = Task::factory()->create([
            'project_id' => $project->id,
            'status'     => 'in_progress',
            'title'      => 'In Progress Task'
        ]);

        // 2. التنفيذ (Act)
        $response = $this->actingAs($user, 'api')
            ->getJson("/api/projects/{$project->id}/tasks?status=completed");

        // 3. التحقق (Assert)
        $response->assertStatus(200)
    ->assertJsonCount(1, 'data')
    ->assertJsonFragment(['id' => $completedTask->id]);

$taskIds = collect($response->json('data'))->pluck('id');

$this->assertNotContains($inProgressTask->id, $taskIds);
    }
}