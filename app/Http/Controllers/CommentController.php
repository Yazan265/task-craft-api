<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Events\CommentCreated;
use App\Http\Resources\CommentResource;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comments', description: 'Endpoints for managing task comments and attachments')]
class CommentController extends Controller
{
    #[OA\Post(
        path: '/api/tasks/{task}/comments',
        summary: 'Add a comment to a task',
        tags: ['Comments'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['content'],
                    properties: [
                        new OA\Property(property: 'content', type: 'string', example: 'This is a comment.'),
                        new OA\Property(property: 'attachment', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Comment added successfully'),
            new OA\Response(response: 422, description: 'Task completed or validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function store(Request $request, Task $task): JsonResponse
    {
        if ($task->status === 'completed') {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'task' => ['Cannot add comments to a completed task.']
                ]
            ], 422);
        }

        $validated = $request->validate([
            'content'    => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $comment = $task->comments()->create([
            'user_id'    => $request->user()->id,
            'content'    => $validated['content'],
            'attachment' => $attachmentPath,
        ]);

        $comment->load('user');

        event(new CommentCreated($comment));

        return response()->json([
            'message' => 'Comment added successfully.',
            'data'    => new CommentResource($comment),
        ], 201);
    }

    #[OA\Get(
        path: '/api/tasks/{task}/comments',
        summary: 'Get all comments for a task',
        tags: ['Comments'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of comments retrieved'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function index(Task $task): JsonResponse
    {
        $comments = $task->comments()->with('user')->latest()->get();

        return response()->json([
            'data' => CommentResource::collection($comments),
        ]);
    }

    #[OA\Post(
        path: '/api/comments/{comment}',
        summary: 'Update a comment (Use POST with _method=PUT for file uploads)',
        tags: ['Comments'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'comment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: '_method', type: 'string', example: 'PUT'),
                        new OA\Property(property: 'content', type: 'string', example: 'Updated comment text.'),
                        new OA\Property(property: 'attachment', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Comment updated successfully'),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function update(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $validated = $request->validate([
            'content'    => 'sometimes|required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            if ($comment->attachment) {
                Storage::disk('public')->delete($comment->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $comment->update($validated);
        $comment->load('user');

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data'    => new CommentResource($comment),
        ]);
    }

    #[OA\Delete(
        path: '/api/comments/{comment}',
        summary: 'Delete a comment',
        tags: ['Comments'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'comment', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Comment deleted successfully'),
            new OA\Response(response: 403, description: 'Unauthorized')
        ]
    )]
    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        if ($comment->attachment) {
            Storage::disk('public')->delete($comment->attachment);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}