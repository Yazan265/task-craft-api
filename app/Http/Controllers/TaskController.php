<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Http\Resources\TaskResource;
use App\Events\TaskAssigned;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tasks', description: 'Endpoints for managing tasks within projects')]
class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    #[OA\Get(
        path: '/api/projects/{project}/tasks',
        summary: 'Get tasks for a specific project',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed'])),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tasks retrieved successfully!'),
            new OA\Response(response: 403, description: 'You are not authorized to view tasks of this project.'),
            new OA\Response(response: 404, description: 'Project not found')
        ]
    )]
    public function index(Request $request, $project)
{
    $projectModel = Project::find($project);

    if (!$projectModel) {
        return response()->json(['message' => 'Project not found'], 404);
    }

    $userId = auth()->id();
    $isOwner = ($projectModel->user_id === $userId);
    $isMember = $projectModel->members()->where('user_id', $userId)->exists();

    if (!$isOwner && !$isMember) {
        return response()->json(['message' => 'You are not authorized to view tasks of this project.'], 403);
    }

    $filters = $request->only(['status', 'priority', 'search']);
    $perPage = $request->get('per_page', 10);

    $tasks = $this->taskService->getTasksByProject($projectModel, $filters, $perPage);

    return response()->json([
        'message' => 'Tasks retrieved successfully!',
        'data' => TaskResource::collection($tasks)
    ], 200);
}

    #[OA\Post(
        path: '/api/projects/{project}/tasks',
        summary: 'Create a new task in a project',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Setup Database Migrations'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Create tables for tasks and projects'),
                    new OA\Property(property: 'assigned_to', type: 'integer', nullable: true, example: 2),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], nullable: true, example: 'pending'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], nullable: true, example: 'high')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created successfully'),
            new OA\Response(response: 403, description: 'You are not authorized to add tasks to this project.'),
            new OA\Response(response: 404, description: 'Project not found'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function store(Request $request, $project)
    {
        $projectModel = Project::find($project);

        if (!$projectModel) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($projectModel->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to add tasks to this project.'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'priority' => 'nullable|string|in:low,medium,high',
        ]);

        $task = $this->taskService->createTask($projectModel, $validatedData);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => new TaskResource($task)
        ], 201);
    }

    #[OA\Get(
        path: '/api/tasks/{id}',
        summary: 'Get details of a specific task',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task retrieved successfully!'),
            new OA\Response(response: 403, description: 'You are not authorized to view this task'),
            new OA\Response(response: 404, description: 'Task not found!')
        ]
    )]
    public function show($id)
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found!'], 404);
        }

        if ($task->project->user_id !== auth()->id() && $task->assigned_to !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to view this task'], 403);
        }

        return response()->json([
            'message' => 'Task retrieved successfully!',
            'data' => new TaskResource($task)
        ], 200);
    }

    #[OA\Put(
        path: '/api/tasks/{id}',
        summary: 'Update task details',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Updated Task Title'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Updated details...'),
                    new OA\Property(property: 'assigned_to', type: 'integer', nullable: true, example: 3),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], nullable: true, example: 'in_progress'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], nullable: true, example: 'medium')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Task updated successfully!'),
            new OA\Response(response: 403, description: 'Unauthorized or invalid status update by assigned user'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function update(Request $request, int $id)
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $userId = auth()->id();
        $isProjectOwner = ($task->project->user_id === $userId);
        $isAssignedUser = ($task->assigned_to === $userId);

        if (!$isProjectOwner && !$isAssignedUser) {
            return response()->json(['message' => 'You are not authorized to update this task'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'priority' => 'nullable|string|in:low,medium,high',
        ]);

        if ($isAssignedUser && !$isProjectOwner) {
            $validatedData = $request->only(['status']);

            if (!array_key_exists('status', $validatedData)) {
                return response()->json(['message' => 'As the assigned worker, you can only update the task status.'], 403);
            }
        }

        $updatedTask = $this->taskService->updateTask($task, $validatedData);

        return response()->json([
            'message' => 'Task updated successfully!',
            'data' => new TaskResource($updatedTask)
        ], 200);
    }

    #[OA\Delete(
        path: '/api/tasks/{id}',
        summary: 'Delete a task',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task deleted successfully!'),
            new OA\Response(response: 403, description: 'You are not authorized to delete this task'),
            new OA\Response(response: 404, description: 'Task not found')
        ]
    )]
    public function destroy(int $id)
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if ($task->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to delete this task'], 403);
        }

        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully!'
        ], 200);
    }
}