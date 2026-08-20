<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Notifications\CommentAddedNotification;
use Illuminate\Support\Facades\Notification;


class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $task;
    protected $comment;


    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->task = Task::factory()->create();
    }

    #[Test]
    public function user_can_add_comment_to_task()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/tasks/{$this->task->id}/comments", [
                'content' => 'This is a test comment'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Comment added successfully.',
                'data' => [
                    'task_id' => $this->task->id,
                    'user_id' => $this->user->id,
                    'content' => 'This is a test comment',
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'This is a test comment',
        ]);
    }
    #[Test]
    public function user_can_fetch_comments_for_a_task()
    {
        Comment::factory()->count(2)->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/tasks/{$this->task->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'task_id', 'user_id', 'content', 'created_at']
                ]
            ]);
    }

#[Test]
public function owner_can_update_their_comment()
{
    $comment = Comment::factory()->create([
        'user_id' => $this->user->id,
        'task_id' => $this->task->id,
        'content' => 'Old Content',
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->putJson("/api/comments/{$comment->id}", [
            'content' => 'Updated Content',
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('comments', [
        'id'      => $comment->id,
        'content' => 'Updated Content',
    ]);
}

#[Test]
public function other_users_cannot_update_comment()
{
    $otherUser = User::factory()->create();
    $comment = Comment::factory()->create([
        'user_id' => $this->user->id,
        'task_id' => $this->task->id,
    ]);

    $response = $this->actingAs($otherUser, 'api')
        ->putJson("/api/comments/{$comment->id}", [
            'content' => 'Hacked Content',
        ]);

    $response->assertStatus(403); // Forbidden
}

#[Test]
public function owner_can_delete_their_comment()
{
    $comment = Comment::factory()->create([
        'user_id' => $this->user->id,
        'task_id' => $this->task->id,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/comments/{$comment->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
}


#[Test]
    public function admin_can_delete_any_comment()
    {
        $regularUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $regularUser->id,
            'task_id' => $this->task->id,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'api')
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }
        #[Test]
    public function user_can_add_comment_with_an_attachment()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('document.jpg');

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/tasks/{$this->task->id}/comments", [
                'content'    => 'هذا تعليق يحتوي على صورة مرفقة',
                'attachment' => $file,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'content' => 'هذا تعليق يحتوي على صورة مرفقة',
        ]);

        $comment = Comment::first();
        Storage::disk('public')->assertExists($comment->attachment);
    }
   #[Test]
public function it_sends_notification_to_assigned_user_when_comment_is_added()
    {
        Notification::fake();

        $assignedUser = User::factory()->create();
        $this->task->update(['assigned_to' => $assignedUser->id]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/tasks/{$this->task->id}/comments", [
                'content' => 'تعليق جديد لتجربة الإشعار',
            ]);

        $response->assertStatus(201);

        Notification::assertSentTo(
            [$assignedUser],
            CommentAddedNotification::class
        );
    }
    #[Test]
public function it_cannot_add_a_comment_if_the_task_is_completed()
{
    $this->task->update(['status' => 'completed']);

    $response = $this->actingAs($this->user, 'api')
        ->postJson("/api/tasks/{$this->task->id}/comments", [
            'content' => 'حاوِلت إضافة تعليق على مهمة منتهية',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['task']);

    $this->assertDatabaseMissing('comments', [
        'task_id' => $this->task->id,
        'content' => 'حاوِلت إضافة تعليق على مهمة منتهية',
    ]);
}
}