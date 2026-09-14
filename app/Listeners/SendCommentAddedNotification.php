<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Notifications\CommentAddedNotification;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- هذه تجعلها تعمل في الخلفية!
use Illuminate\Queue\InteractsWithQueue;

class SendCommentAddedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $task = $comment->task;

        // نفس المنطق السابق للإشعار لكنه الآن يعمل في الخلفية!
        if ($task->assigned_to && $task->assigned_to !== $comment->user_id) {
            $task->assignee->notify(new CommentAddedNotification($comment));
        }
    }
}