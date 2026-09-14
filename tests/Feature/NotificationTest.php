<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

#[Test]
public function user_can_fetch_unread_notifications()
{
    $task = Task::factory()->create();
    $this->user->notify(new TaskAssignedNotification($task));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/notifications');

   $response->assertJsonStructure([
    'data' => [
        '*' => ['id', 'type', 'data', 'created_at']
    ],
    'current_page',
    'per_page',
    'total',
]);
}

    #[Test]
    public function user_can_mark_notification_as_read()
    {
        $task = Task::factory()->create();
        $this->user->notify(new TaskAssignedNotification($task));

        $notification = $this->user->unreadNotifications()->first();

        $response = $this->actingAs($this->user, 'api')
            ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification marked as read successfully.'
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }
    #[Test]
    public function user_can_mark_all_notifications_as_read()
    {
        $task1 = Task::factory()->create();
        $task2 = Task::factory()->create();

        $this->user->notify(new TaskAssignedNotification($task1));
        $this->user->notify(new TaskAssignedNotification($task2));

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user, 'api')
            ->patchJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All notifications marked as read successfully.'
            ]);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }
    #[Test]
public function user_can_get_unread_notifications_count()
{
    $task = Task::factory()->create();
    $this->user->notify(new TaskAssignedNotification($task));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/notifications/unread-count');

    $response->assertStatus(200)
        ->assertJson([
            'unread_count' => 1
        ]);
}
}