<?php

namespace Tests\Feature;

use App\Events\CommentCreated;
use App\Events\TaskAssigned;
use App\Listeners\SendCommentAddedNotification;
use App\Listeners\SendTaskAssignedNotification;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class QueueAndListenerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_queues_listener_when_task_assigned_event_is_dispatched()
    {
        Queue::fake();

        $task = Task::factory()->create();

        // إطلاق الحدث يدويًا
        event(new TaskAssigned($task));

        // التحقق من أن الـ Listener تم دفعه إلى الـ Queue
        Queue::assertPushed(\Illuminate\Events\CallQueuedListener::class, function ($job) {
            return $job->class === SendTaskAssignedNotification::class;
        });
    }

    #[Test]
    public function listener_sends_notification_when_handling_comment_created_event()
    {
        Notification::fake();

        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $assignee->id,
        ]);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        // تنفيذ الـ Listener مباشرة (سلوك الكود الداخلي)
        $listener = new SendCommentAddedNotification();
        $listener->handle(new CommentCreated($comment));

        // التحقق من أن الإشعار تم إرساله للمستخدم المسند إليه المهمة
        Notification::assertSentTo(
            $assignee,
            CommentAddedNotification::class
        );
    }
}