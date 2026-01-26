<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'division' => $this->division,
            'position' => $this->position,
            'notify_email_on_ticket_created' => $this->notify_email_on_ticket_created,
            'notify_email_on_ticket_reply' => $this->notify_email_on_ticket_reply,
            'notify_email_on_ticket_closed' => $this->notify_email_on_ticket_closed,
            'notify_email_on_ticket_updated' => $this->notify_email_on_ticket_updated,
        ];
    }
}
