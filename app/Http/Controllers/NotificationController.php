<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
public function index(Request $request): JsonResponse
{
    // جلب 10 إشعارات فقط لكل صفحة مع معلومات الـ Pagination
    $notifications = $request->user()
        ->unreadNotifications()
        ->paginate(10);

    return response()->json([
        'data' => $notifications
    ]);
}

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->unreadNotifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read successfully.'
        ]);
    }
 
public function markAllAsRead(Request $request): JsonResponse
{
    $request->user()->unreadNotifications->markAsRead();

    return response()->json([
        'message' => 'All notifications marked as read successfully.'
    ]);
}
}