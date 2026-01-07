<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLoginSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'ip_address',
        'device_name',
        'user_agent',
        'login_at',
        'last_activity_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get device name from user agent
     */
    public static function getDeviceName($userAgent)
    {
        if (stripos($userAgent, 'Windows') !== false) {
            return 'Windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            return 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } elseif (stripos($userAgent, 'iPhone') !== false) {
            return 'iPhone';
        } elseif (stripos($userAgent, 'iPad') !== false) {
            return 'iPad';
        } elseif (stripos($userAgent, 'Android') !== false) {
            return 'Android';
        }
        return 'Unknown Device';
    }

    /**
     * Check if session is from recognized IP
     */
    public static function isKnownIP($userId, $ipAddress)
    {
        return self::where('user_id', $userId)
            ->where('ip_address', $ipAddress)
            ->where('deleted_at', null)
            ->exists();
    }

    /**
     * Get all active sessions for user
     */
    public static function getActiveSessions($userId)
    {
        return self::where('user_id', $userId)
            ->where('deleted_at', null)
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }
}
