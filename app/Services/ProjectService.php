<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    public function createProject(array $data, int $userId): Project
    {
        $data['user_id'] = $userId;
        $project = Project::create($data);

        return $project->load('user');
    }

  
    public function getUserProjects(int $userId): Collection
    {
        return Project::where('user_id', $userId)
            ->orWhereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['user', 'members', 'tasks'])
            ->latest()
            ->get();
    }

    public function getProjectById(int $id): ?Project
    {
        return Project::with(['user', 'members', 'tasks'])->find($id); 
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->load(['user', 'members', 'tasks']);
    }

    public function deleteProject(Project $project): bool
    {
        return $project->delete();
    }

    public function addMember(Project $project, int $userId, string $role = 'member'): void
    {
        $project->members()->syncWithoutDetaching([
            $userId => ['role' => $role]
        ]);
    }

    public function removeMember(Project $project, int $userId): void
    {
        $project->members()->detach($userId);
    }
}