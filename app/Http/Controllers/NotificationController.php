<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Notifications', description: 'Endpoints for managing user notifications')]
class NotificationController extends Controller
{
    #[OA\Get(
        path: '/api/notifications',
        summary: 'Get paginated user notifications',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of notifications retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        $data = NotificationResource::collection($notifications->items());

        return response()->json(array_merge(
            $notifications->toArray(),
            ['data' => $data]
        ));
    }

    #[OA\Patch(
        path: '/api/notifications/{id}/read',
        summary: 'Mark a specific notification as read',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification marked as read successfully'),
            new OA\Response(response: 404, description: 'Notification not found or already read'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->unreadNotifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read successfully.'
        ]);
    }

    #[OA\Post(
        path: '/api/notifications/mark-all-read',
        summary: 'Mark all unread notifications as read',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'All notifications marked as read successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read successfully.'
        ]);
    }

    #[OA\Get(
        path: '/api/notifications/unread-count',
        summary: 'Get total unread notifications count',
        tags: ['Notifications'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Unread count retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count()
        ]);
    }
}