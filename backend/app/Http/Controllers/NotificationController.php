<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->notifications()->orderBy('created_at', 'desc');

        $notifications = $query->limit(50)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notifikasi',
                'message' => $n->data['message'] ?? '',
                'href' => $n->data['href'] ?? null,
                'meta' => $n->data['meta'] ?? null,
                'readAt' => $n->read_at,
                'createdAt' => $n->created_at,
            ];
        });

        return response()->json([
            'message' => 'Notifikasi berhasil diambil',
            'data' => [
                'notifications' => $notifications,
                'unreadCount' => $user->unreadNotifications()->count(),
            ]
        ], 200);
    }

    public function markRead(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notifikasi tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca',
            'data' => null,
        ], 200);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca',
            'data' => null,
        ], 200);
    }

    public function clearAll(Request $request)
    {
        $user = $request->user();
        $user->notifications()->delete();

        return response()->json([
            'message' => 'Semua notifikasi berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
