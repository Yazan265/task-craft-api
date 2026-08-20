<?php
namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

 
    public function index($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to view tasks of this project.'], 403);
        }

        $tasks = $this->taskService->getTasksByProject($project);

        return response()->json([
            'message' => 'Tasks retrieved successfully!',
            'data' => $tasks
        ], 200);
    }

  
    public function store(Request $request, $projectId)
    {
        $project = Project::find($projectId);
        
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to add tasks to this project.'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        $task = $this->taskService->createTask($project, $validatedData);
    


        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task
        ], 201);
    }

   
    public function show($id)
    {
        
        $task = $this->taskService->getTaskById($id);
        if ($task->project->user_id !== auth()->id() && $task->assigned_to !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to view this task'], 403);
        }

        if (!$task) {
            return response()->json(['message' => 'Task not found!'], 404);
        }

        return response()->json([
            'message' => 'Task retrieved successfully!',
            'data' => $task
        ], 200);
    }


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
        'data' => $updatedTask
    ], 200);
}

   
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