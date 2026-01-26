<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'division',
        'position',
        'last_login_ip',
        'last_login_at',
        'notify_email_on_ticket_created',
        'notify_email_on_ticket_reply',
        'notify_email_on_ticket_closed',
        'notify_email_on_ticket_updated',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'notify_email_on_ticket_created' => 'boolean',
            'notify_email_on_ticket_reply' => 'boolean',
            'notify_email_on_ticket_closed' => 'boolean',
            'notify_email_on_ticket_updated' => 'boolean',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketReplies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function loginSessions()
    {
        return $this->hasMany(UserLoginSession::class);
    }
}
