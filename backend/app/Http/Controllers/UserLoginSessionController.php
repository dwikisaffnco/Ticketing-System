<?php

namespace App\Http\Controllers;

use App\Models\UserLoginSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginSessionController extends Controller
{
    /**
     * Get all active sessions for authenticated user
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            $sessions = UserLoginSession::where('user_id', $user->id)
                ->where('deleted_at', null)
                ->orderBy('last_activity_at', 'desc')
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'ip_address' => $session->ip_address,
                        'device_name' => $session->device_name,
                        'user_agent' => $session->user_agent,
                        'login_at' => $session->login_at,
                        'last_activity_at' => $session->last_activity_at,
                    ];
                });

            return response()->json([
                'message' => 'Session list retrieved',
                'data' => $sessions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving sessions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify if login IP is recognized
     */
    public function verifyIP(Request $request)
    {
        try {
            $request->validate([
                'ip_address' => 'required|ip',
            ]);

            $user = Auth::user();
            $ipAddress = $request->input('ip_address');

            $isKnown = UserLoginSession::isKnownIP($user->id, $ipAddress);

            return response()->json([
                'message' => 'IP verification completed',
                'data' => [
                    'ip_address' => $ipAddress,
                    'is_recognized' => $isKnown,
                    'last_login_ip' => $user->last_login_ip,
                    'last_login_at' => $user->last_login_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error verifying IP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession($sessionId)
    {
        try {
            $user = Auth::user();
            
            $session = UserLoginSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$session) {
                return response()->json([
                    'message' => 'Session not found',
                ], 404);
            }

            $session->delete();

            return response()->json([
                'message' => 'Session revoked successfully',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error revoking session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke all sessions except current
     */
    public function revokeAllOthers(Request $request)
    {
        try {
            $user = Auth::user();
            $currentIp = $request->ip();

            // Soft delete all sessions except current IP
            UserLoginSession::where('user_id', $user->id)
                ->where('ip_address', '!=', $currentIp)
                ->delete();

            return response()->json([
                'message' => 'All other sessions revoked successfully',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error revoking sessions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update last activity for session
     */
    public function updateActivity(Request $request)
    {
        try {
            $request->validate([
                'session_id' => 'required|integer',
            ]);

            $user = Auth::user();
            $sessionId = $request->input('session_id');

            $session = UserLoginSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$session) {
                return response()->json([
                    'message' => 'Session not found',
                ], 404);
            }

            $session->update([
                'last_activity_at' => now(),
            ]);

            return response()->json([
                'message' => 'Activity updated',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
