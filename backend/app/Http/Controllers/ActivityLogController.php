<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        try {
            $perPage = (int) ($request->query('per_page', 20));
            if ($perPage <= 0) $perPage = 20;
            if ($perPage > 100) $perPage = 100;

            $query = ActivityLog::query()->with('user')->orderBy('created_at', 'desc');

            if ($request->query('search')) {
                $search = (string) $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->query('action')) {
                $query->where('action', (string) $request->query('action'));
            }

            if ($request->query('user_id')) {
                $query->where('user_id', (int) $request->query('user_id'));
            }

            $logs = $query->paginate($perPage);

            return ActivityLogResource::collection($logs)->additional([
                'message' => 'Activity logs fetched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
