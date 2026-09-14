<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- تجعلها تعمل في الخلفية!
use Illuminate\Queue\InteractsWithQueue;

class SendTaskAssignedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskAssigned $event): void
    {
        $task = $event->task;

        if ($task->assigned_to) {
            $task->assignee->notify(new TaskAssignedNotification($task));
        }
    }
}