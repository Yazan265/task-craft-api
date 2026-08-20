<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Notifications\CommentAddedNotification;
use Illuminate\Support\Facades\Notification;

class CommentController extends Controller
{

public function store(Request $request, Task $task): JsonResponse
{
    // التحقق من حالة المهمة أولاً
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

    if ($task->assigned_to && $task->assigned_to !== $request->user()->id) {
        $task->assignee->notify(new CommentAddedNotification($comment));
    }

    return response()->json([
        'message' => 'Comment added successfully.',
        'data'    => $comment,
    ], 201);
}


    public function index(Task $task): JsonResponse
    {
        $comments = $task->comments()->with('user')->get();

        return response()->json([
            'data' => $comments,
        ]);
    }

  
   public function update(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $validated = $request->validate([
            'content'    => 'sometimes|required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // إذا أرسل المستخدم مرفقاً جديداً، نحذف القديم إن وجد ونخزن الجديد
        if ($request->hasFile('attachment')) {
            if ($comment->attachment) {
                Storage::disk('public')->delete($comment->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $comment->update($validated);

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data'    => $comment,
        ]);
    }

  
 public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        // حذف الملف المرفق من القرص التخزيني عند حذف التعليق
        if ($comment->attachment) {
            Storage::disk('public')->delete($comment->attachment);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
    
    
}