<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserStoreRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function show($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        try {
            $user = User::query()->find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'message' => 'Data user berhasil ditampilkan',
                'data' => new UserResource($user),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        try {
            $users = User::query()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'Data user berhasil ditampilkan',
                'data' => UserResource::collection($users)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(AdminUserStoreRequest $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        $data = $request->validated();

        try {
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = $data['role'];
            $user->division = $data['division'] ?? null;
            $user->position = $data['position'] ?? null;
            $user->save();

            ActivityLog::record(
                'admin_user_create',
                'Admin created a user',
                ['target_user_id' => $user->id, 'target_email' => $user->email, 'target_role' => $user->role],
                request()
            );

            return response()->json([
                'message' => 'Akun berhasil dibuat',
                'data' => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(AdminUserUpdateRequest $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        $data = $request->validated();

        try {
            $user = User::query()->find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role = $data['role'];
            $user->division = $data['division'] ?? null;
            $user->position = $data['position'] ?? null;

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            ActivityLog::record(
                'admin_user_update',
                'Admin updated a user',
                ['target_user_id' => $user->id, 'target_email' => $user->email, 'target_role' => $user->role],
                request()
            );

            return response()->json([
                'message' => 'User berhasil diperbarui',
                'data' => new UserResource($user),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        if ((int) $id === (int) auth()->id()) {
            return response()->json([
                'message' => 'Tidak bisa menghapus akun sendiri',
                'data' => null,
            ], 422);
        }

        try {
            $user = User::query()->find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            DB::transaction(function () use ($user) {
                $ticketIds = Ticket::query()
                    ->where('user_id', $user->id)
                    ->pluck('id');

                if ($ticketIds->isNotEmpty()) {
                    TicketReply::query()->whereIn('ticket_id', $ticketIds)->delete();
                    Ticket::query()->whereIn('id', $ticketIds)->delete();
                }

                TicketReply::query()->where('user_id', $user->id)->delete();

                DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $user->id)
                    ->delete();

                DB::table('sessions')->where('user_id', $user->id)->delete();

                $user->delete();
            });

            ActivityLog::record(
                'admin_user_delete',
                'Admin deleted a user',
                ['target_user_id' => $user->id, 'target_email' => $user->email],
                request()
            );

            return response()->json([
                'message' => 'User berhasil dihapus',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
