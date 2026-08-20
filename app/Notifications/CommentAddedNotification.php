<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentAddedNotification extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment)
    {
    }

   
    public function via(object $notifiable): array
    {
        return ['database'];
    }

  
    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'task_id'    => $this->comment->task_id,
            'user_name'  => $this->comment->user->name,
            'message'    => "أضاف {$this->comment->user->name} تعليقاً جديداً على مهمتك.",
        ];
    }
}