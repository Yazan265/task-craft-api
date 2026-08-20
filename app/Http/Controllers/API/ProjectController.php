<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 

class ProjectController extends Controller
{
    use AuthorizesRequests; 

    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $currentUserId = auth()->id();

        $project = $this->projectService->createProject($validatedData, $currentUserId);

        return response()->json([
            'message' => 'Project created successfully!',
            'data' => $project
        ], 201); 
    }

   
    public function index()
    {
        $projects = $this->projectService->getAllProjects();

        return response()->json([
            'message' => 'Projects retrieved successfully!',
            'data' => $projects
        ], 200);
    }

    
    public function show($id)
    {
        $project = $this->projectService->getProjectById($id);

        if (!$project) {
            return response()->json([
                'message' => 'Project not found!'
            ], 404);
        }

        $this->authorize('view', $project);

        return response()->json([
            'message' => 'Project retrieved successfully!',
            'data' => $project
        ], 200);
    }

    /**
     * تحديث مشروع (لصاحب المشروع فقط)
     */
    public function update(Request $request, int $id)
    {
        $project = $this->projectService->getProjectById($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to update this project'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $updatedProject = $this->projectService->updateProject($project, $validatedData);

        return response()->json([
            'message' => 'Project updated successfully!',
            'data' => $updatedProject
        ], 200);
    }

 
    public function destroy(int $id)
    {
        $project = $this->projectService->getProjectById($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this project'], 403);
        }

        $this->projectService->deleteProject($project);

        return response()->json([
            'message' => 'Project deleted successfully!'
        ], 200);
    }

    public function addMember(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'nullable|string|in:member,admin,viewer',
        ]);

        $project->members()->syncWithoutDetaching([
            $validated['user_id'] => ['role' => $validated['role'] ?? 'member']
        ]);

        return response()->json([
            'message' => 'Member added successfully.'
        ], 200);
    }

   
    public function removeMember(Project $project, User $user)
    {
        $this->authorize('update', $project);

        $project->members()->detach($user->id);

        return response()->json([
            'message' => 'Member removed successfully.'
        ], 200);
    }
}