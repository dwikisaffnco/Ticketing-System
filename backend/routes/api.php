<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserLoginSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Login Session Management
    Route::get('/sessions', [UserLoginSessionController::class, 'index']);
    Route::post('/sessions/verify-ip', [UserLoginSessionController::class, 'verifyIP']);
    Route::delete('/sessions/{sessionId}', [UserLoginSessionController::class, 'revokeSession']);
    Route::post('/sessions/revoke-all-others', [UserLoginSessionController::class, 'revokeAllOthers']);
    Route::post('/sessions/update-activity', [UserLoginSessionController::class, 'updateActivity']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/clear', [NotificationController::class, 'clearAll']);

    Route::post('/admin/users/import', [AdminUserController::class, 'import']);
    Route::get('/admin/users/import/template', [AdminUserController::class, 'downloadTemplate']);
    Route::delete('/admin/users/bulk', [AdminUserController::class, 'bulkDestroy']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);

    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index']);

    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);

    Route::get('/ticket', [TicketController::class, 'index']);
    Route::get('/ticket/{code}', [TicketController::class, 'show']);
    Route::post('/ticket', [TicketController::class, 'store']);
    Route::put('/ticket/{code}', [TicketController::class, 'update']);
    Route::delete('/ticket/{code}', [TicketController::class, 'destroy']);
    Route::patch('/ticket/{code}/archive', [TicketController::class, 'archive']);
    Route::patch('/ticket/{code}/unarchive', [TicketController::class, 'unarchive']);
    Route::post('/ticket-reply/{code}', [TicketController::class, 'storeReply']);
    Route::get('/ticket/{code}/attachment/view', [TicketController::class, 'serveTicketAttachment']);
    Route::get('/ticket/{code}/attachment/download', [TicketController::class, 'downloadTicketAttachment']);
    Route::get('/ticket-reply/{code}/{replyId}/attachment/view', [TicketController::class, 'serveTicketReplyAttachment']);
    Route::get('/ticket-reply/{code}/{replyId}/attachment/download', [TicketController::class, 'downloadTicketReplyAttachment']);
});
