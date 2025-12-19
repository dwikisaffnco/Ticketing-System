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
use Illuminate\Http\Request;

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

    public function bulkDestroy(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = $request->input('ids');
        $currentUserId = auth()->id();

        if (in_array($currentUserId, $ids)) {
             return response()->json([
                'message' => 'Tidak bisa menghapus akun sendiri dalam pilihan',
                'data' => null,
            ], 422);
        }

        try {
            DB::transaction(function () use ($ids) {
                // Get ticket IDs for all users to be deleted
                $ticketIds = Ticket::query()
                    ->whereIn('user_id', $ids)
                    ->pluck('id');

                if ($ticketIds->isNotEmpty()) {
                    TicketReply::query()->whereIn('ticket_id', $ticketIds)->delete();
                    Ticket::query()->whereIn('id', $ticketIds)->delete();
                }

                TicketReply::query()->whereIn('user_id', $ids)->delete();

                DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->whereIn('notifiable_id', $ids)
                    ->delete();

                DB::table('sessions')->whereIn('user_id', $ids)->delete();

                User::whereIn('id', $ids)->delete();
            });

            ActivityLog::record(
                'admin_user_bulk_delete',
                'Admin bulk deleted users',
                ['count' => count($ids), 'ids' => $ids],
                request()
            );

            return response()->json([
                'message' => count($ids) . ' user berhasil dihapus',
                'data' => null,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function import(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $fileContents = file($file->getPathname());

        if (empty($fileContents)) {
            return response()->json([
                'message' => 'File kosong',
                'data' => null,
            ], 400);
        }

        $header = str_getcsv(array_shift($fileContents));
        $requiredHeaders = ['name', 'email', 'password', 'role'];

        // Normalize headers
        $header = array_map('trim', $header);
        
        // Check if required headers exist
        $missingHeaders = array_diff($requiredHeaders, $header);
        if (!empty($missingHeaders)) {
            return response()->json([
                'message' => 'Format CSV salah. Kolom berikut wajib ada: ' . implode(', ', $missingHeaders),
                'data' => null,
            ], 400);
        }

        $importedCount = 0;
        $errors = [];

        foreach ($fileContents as $index => $line) {
            $data = str_getcsv($line);
            
            // Skip empty lines
            if (empty($data) || count($data) < count($requiredHeaders)) {
                continue;
            }

            $rowData = array_combine($header, $data);

            try {
                // Check if user already exists
                if (User::where('email', $rowData['email'])->exists()) {
                    $errors[] = "Baris " . ($index + 2) . ": Email {$rowData['email']} sudah digunakan.";
                    continue;
                }

                // Check if role is valid
                $validRoles = ['user', 'admin'];
                $role = strtolower(trim($rowData['role']));

                if (!in_array($role, $validRoles)) {
                    $errors[] = "Baris " . ($index + 2) . ": Role '{$rowData['role']}' tidak valid. Role harus 'user' atau 'admin'.";
                    continue;
                }

                $user = new User();
                $user->name = $rowData['name'];
                $user->email = $rowData['email'];
                $user->password = Hash::make($rowData['password']);
                $user->role = $role;
                $user->division = $rowData['division'] ?? null;
                $user->position = $rowData['position'] ?? null;
                $user->save();

                $importedCount++;

            } catch (\Exception $e) {
                $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        ActivityLog::record(
            'admin_user_import',
            'Admin imported users from CSV',
            ['count' => $importedCount, 'errors' => $errors],
            request()
        );

        return response()->json([
            'message' => "Berhasil mengimpor $importedCount user.",
            'errors' => $errors,
        ], 200);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_template.csv"',
        ];

        $columns = ['name', 'email', 'password', 'role', 'division', 'position'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            // Sample data
            fputcsv($file, ['John Doe', 'john@example.com', 'password123', 'user', 'IT', 'Developer']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
