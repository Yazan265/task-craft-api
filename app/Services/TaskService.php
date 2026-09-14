<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Events\TaskAssigned;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function getTasksByProject(Project $project, array $filters = [], int $perPage = 10)
    {
        return $project->tasks()
            ->with(['assignee', 'project'])
            ->filterByStatus($filters['status'] ?? null)
            ->filterByPriority($filters['priority'] ?? null)
            ->search($filters['search'] ?? null)
            ->latest()
            ->paginate($perPage);
    }

    public function createTask(Project $project, array $data): Task
    {
        if (!empty($data['assigned_to'])) {
            $this->validateUserIsProjectMember($project, (int)$data['assigned_to']);
        }

        $task = $project->tasks()->create($data);

        if (!empty($data['assigned_to'])) {
            event(new TaskAssigned($task));
        }

        return $task->load(['assignee', 'project']);
    }

    public function getTaskById(int $id): ?Task
    {
        return Task::with(['assignee', 'project', 'comments.user'])->find($id);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $oldAssignedTo = $task->assigned_to;

        if (!empty($data['assigned_to']) && (int)$data['assigned_to'] != (int)$oldAssignedTo) {
            $this->validateUserIsProjectMember($task->project, (int)$data['assigned_to']);
        }

        $task->update($data);

        if (!empty($data['assigned_to']) && (int)$data['assigned_to'] != (int)$oldAssignedTo) {
            event(new TaskAssigned($task));
        }

        return $task->load(['assignee', 'project', 'comments']);
    }

    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    public function assignTask(Task $task, User $assignedUser)
    {
        $this->validateUserIsProjectMember($task->project, $assignedUser->id);

        $task->update([
            'assigned_to' => $assignedUser->id,
        ]);

        event(new TaskAssigned($task));

        return $task->load(['assignee', 'project']); 
    }

    
    protected function validateUserIsProjectMember(Project $project, int $userId): void
    {
        $isOwner = ((int)$project->user_id === (int)$userId);
        $isMember = $project->members()->where('user_id', $userId)->exists();

        if (!$isOwner && !$isMember) {
            throw ValidationException::withMessages([
                'assigned_to' => ['The assigned user must be either the project owner or a member of the project.']
            ]);
        }
    }
}