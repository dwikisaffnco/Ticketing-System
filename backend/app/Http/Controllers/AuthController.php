<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RegisterStoreRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserLoginSession;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401);
            }

            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // Update last login info
            $user->update([
                'last_login_ip' => $ipAddress,
                'last_login_at' => now(),
            ]);

            // Record login session
            $loginSession = UserLoginSession::create([
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'device_name' => UserLoginSession::getDeviceName($userAgent),
                'user_agent' => $userAgent,
                'login_at' => now(),
                'last_activity_at' => now(),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            try {
                ActivityLog::record(
                    'auth_login',
                    'User login from ' . $ipAddress,
                    [
                        'email' => $user->email, 
                        'role' => $user->role,
                        'ip_address' => $ipAddress,
                        'device_name' => $loginSession->device_name,
                    ],
                    $request,
                    $user->id
                );
            } catch (Exception $e) {
                Log::warning('Failed to write activity log (auth_login)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Login Berhasil',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user),
                    'session' => [
                        'ip_address' => $ipAddress,
                        'device_name' => $loginSession->device_name,
                    ]
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('Login API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();

            return response()->json([
                'message' => 'Profile User Berhasil Diambil',
                'data' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();

            ActivityLog::record(
                'auth_logout',
                'User logout',
                ['email' => $user->email, 'role' => $user->role],
                request(),
                $user->id
            );

            return response()->json([
                'message' => 'Logout berhasil',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $data = $request->validated();

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->save();

            return response()->json([
                'message' => 'Profile berhasil diperbarui',
                'data' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Password saat ini salah',
                    'data' => null
                ], 400);
            }

            $user->password = Hash::make($data['password']);
            $user->save();

            return response()->json([
                'message' => 'Password berhasil diperbarui',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(RegisterStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            ActivityLog::record(
                'auth_register',
                'User registration',
                ['email' => $user->email],
                $request,
                $user->id
            );

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Reset link terkirim',
                ], 200);
            }

            Log::warning('Failed to send reset password link', [
                'email' => $request->email,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Gagal mengirim reset link',
                'errors' => ['email' => [__($status)]],
            ], 400);
        } catch (Exception $e) {
            Log::error('Forgot password API error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json([
            'message' => 'Gagal mereset password',
            'errors' => ['email' => [__($status)]]
        ], 400);
    }
}
