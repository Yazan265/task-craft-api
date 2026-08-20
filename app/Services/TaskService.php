<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Notifications\TaskAssignedNotification;

class TaskService
{
  
    public function getTasksByProject(Project $project)
    {
        return $project->tasks;
    }

   
    public function createTask(Project $project, array $data): Task
    {
        $task = $project->tasks()->create($data);

        if (!empty($data['assigned_to'])) {
            $assignedUser = User::find($data['assigned_to']);
            if ($assignedUser) {
                $assignedUser->notify(new TaskAssignedNotification($task));
            }
        }

        return $task;
    }

   
    public function getTaskById(int $id): ?Task
    {
        return Task::find($id);
    }

  
    public function updateTask(Task $task, array $data): Task
    {
        $oldAssignedTo = $task->assigned_to;
        
        $task->update($data);

        if (!empty($data['assigned_to']) && $data['assigned_to'] != $oldAssignedTo) {
            $assignedUser = User::find($data['assigned_to']);
            if ($assignedUser) {
                $assignedUser->notify(new TaskAssignedNotification($task));
            }
        }

        return $task;
    }

   
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    
    public function assignTask(Task $task, User $assignedUser)
    {
        $task->update([
            'assigned_to' => $assignedUser->id,
        ]);

        $assignedUser->notify(new TaskAssignedNotification($task));

        return $task;
    }
}