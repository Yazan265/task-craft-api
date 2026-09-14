<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 
use App\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Projects', description: 'Endpoints for managing projects and team members')]
class ProjectController extends Controller
{
    use AuthorizesRequests; 

    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    #[OA\Post(
        path: '/api/projects',
        summary: 'Create a new project',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'TaskCraft Backend'),
                    new OA\Property(property: 'description', type: 'string', example: 'Project description here...')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Project created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = $this->projectService->createProject($validatedData, auth()->id());

        return response()->json([
            'message' => 'Project created successfully!',
            'data' => new ProjectResource($project)
        ], 201); 
    }

    #[OA\Get(
        path: '/api/projects',
        summary: 'Get list of projects',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of projects retrieved'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function index(): JsonResponse
    {
        $projects = $this->projectService->getUserProjects(auth()->id());

        return response()->json([
            'message' => 'Projects retrieved successfully!',
            'data' => ProjectResource::collection($projects)
        ], 200);
    }

    #[OA\Get(
        path: '/api/projects/{project}',
        summary: 'Get project details',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project details retrieved'),
            new OA\Response(response: 403, description: 'Unauthorized access to project'),
            new OA\Response(response: 404, description: 'Project not found')
        ]
    )]
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['user', 'members', 'tasks']);

        return response()->json([
            'message' => 'Project retrieved successfully!',
            'data' => new ProjectResource($project)
        ], 200);
    }

    #[OA\Put(
        path: '/api/projects/{project}',
        summary: 'Update project',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Project Name'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated Description')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Project updated successfully'),
            new OA\Response(response: 403, description: 'Unauthorized access to project'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $updatedProject = $this->projectService->updateProject($project, $validatedData);

        return response()->json([
            'message' => 'Project updated successfully!',
            'data' => new ProjectResource($updatedProject)
        ], 200);
    }

    #[OA\Delete(
        path: '/api/projects/{project}',
        summary: 'Delete project',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project deleted successfully'),
            new OA\Response(response: 403, description: 'Unauthorized access to project')
        ]
    )]
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return response()->json([
            'message' => 'Project deleted successfully!'
        ], 200);
    }

    #[OA\Post(
        path: '/api/projects/{project}/members',
        summary: 'Add member to project',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 2),
                    new OA\Property(property: 'role', type: 'string', example: 'member')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Member added successfully'),
            new OA\Response(response: 403, description: 'Unauthorized access to project'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function addMember(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'nullable|string|in:member,admin,viewer',
        ]);

        $this->projectService->addMember($project, $validated['user_id'], $validated['role'] ?? 'member');

        return response()->json(['message' => 'Member added successfully.'], 200);
    }

    #[OA\Delete(
        path: '/api/projects/{project}/members/{user}',
        summary: 'Remove member from project',
        tags: ['Projects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member removed successfully'),
            new OA\Response(response: 403, description: 'Unauthorized access to project')
        ]
    )]
    public function removeMember(Project $project, User $user): JsonResponse
    {
        $this->authorize('update', $project);

        $this->projectService->removeMember($project, $user->id);

        return response()->json([
            'message' => 'Member removed successfully.'
        ], 200);
    }
}