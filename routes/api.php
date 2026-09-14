<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\API\ReportController;






Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/refresh', [UserController::class, 'refresh']);
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/projects', [ProjectController::class, 'store']);     // إنشاء مشروع
    Route::get('/projects', [ProjectController::class, 'index']);       // جلب كل المشاريع
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    
});


Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
        Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);
    Route::get('/projects/{project}/reports/summary', [ReportController::class, 'summary']);
    Route::get('/projects/{project}/reports/export/excel', [ReportController::class, 'exportExcel']);
    Route::get('/projects/{project}/reports/export/pdf', [ReportController::class, 'exportPdf']);

});