<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommentController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    
    Route::post('/projects', [ProjectController::class, 'store']);     // إنشاء مشروع
    Route::get('/projects', [ProjectController::class, 'index']);       // جلب كل المشاريع
    Route::get('/projects/{id}', [ProjectController::class, 'show']);   // جلب مشروع محدد
    Route::put('/projects/{id}', [ProjectController::class, 'update']);      // للتحديث الكامل أو الجزئي
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    // 1. جلب كل مهام مشروع معين
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    
    // 2. إنشاء مهمة جديدة داخل مشروع معين
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    
    // 3. عرض تفاصيل مهمة محددة
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    
    // 4. تعديل مهمة محددة
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    
    // 5. حذف مهمة محددة
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    
});


Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
        Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);

});