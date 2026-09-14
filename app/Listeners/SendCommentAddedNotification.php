<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Notifications\CommentAddedNotification;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Queue\InteractsWithQueue;

class SendCommentAddedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $task = $comment->task;

        if ($task->assigned_to && $task->assigned_to !== $comment->user_id) {
            $task->assignee->notify(new CommentAddedNotification($comment));
        }
    }
}