<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
  public function createProject(array $data, int $userId): Project
    {
     $data['user_id'] = $userId;

        return Project::create($data);
    }
  public function getAllProjects(): Collection
    {
        return Project::all();
    }
    public function getProjectById(int $id): ?Project
    {
        return Project::find($id); 
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);
        return $project;
    }

   
    public function deleteProject(Project $project): bool
    {
        return $project->delete();
    }
}
