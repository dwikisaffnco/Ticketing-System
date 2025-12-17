<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $description = null, array $meta = [], ?Request $request = null, ?int $userId = null): self
    {
        $req = $request ?? request();

        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'meta' => empty($meta) ? null : $meta,
            'ip_address' => $req?->ip(),
            'user_agent' => $req?->userAgent(),
        ]);
    }
}
