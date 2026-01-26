<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketEventNotification extends Notification
{
    use Queueable;

    private array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via(object $notifiable): array
    {
        // Check notification preference based on event type
        $event = $this->payload['meta']['event'] ?? null;
        $sendEmail = $this->shouldSendEmail($notifiable, $event);

        if ($sendEmail) {
            return ['database', 'mail'];
        }
        
        return ['database'];
    }

    private function shouldSendEmail(object $notifiable, ?string $event): bool
    {
        if (!$event || !$notifiable) {
            return true;
        }

        // Map events to notification preferences
        $preferenceMap = [
            'ticket_created' => 'notify_email_on_ticket_created',
            'user_replied' => 'notify_email_on_ticket_reply',
            'admin_replied' => 'notify_email_on_ticket_reply',
            'status_changed' => 'notify_email_on_ticket_updated',
        ];

        // Check if status changed to 'resolved' (ticket closed)
        if ($event === 'status_changed' && isset($this->payload['meta']['newStatus']) && $this->payload['meta']['newStatus'] === 'resolved') {
            return $notifiable->notify_email_on_ticket_closed ?? true;
        }

        $preferenceKey = $preferenceMap[$event] ?? null;
        
        if (!$preferenceKey) {
            return true;
        }

        return $notifiable->{$preferenceKey} ?? true;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = (string) ($this->payload['title'] ?? 'Notifikasi');
        $message = (string) ($this->payload['message'] ?? '');

        $actorName = (string) ($this->payload['meta']['actorName'] ?? '');
        $content = (string) ($this->payload['meta']['content'] ?? '');
        $content = trim($content);

        $appUrl = 'https://ticketing.saffnco.app';
        $actionUrl = $appUrl;
        if (!empty($this->payload['meta']['ticketCode'])) {
            $actionUrl = $appUrl . '/ticket/' . $this->payload['meta']['ticketCode'];
        }

        $mail = (new MailMessage)
            ->subject($title);

        if ($actorName !== '') {
            $mail->line('Dari: ' . $actorName);
        }

        if ($content !== '') {
            $mail->line('Pesan: ' . Str::limit($content, 500));
        }

        if ($message !== '') {
            $mail->line($message);
        }

        return $mail->action('Buka di aplikasi', $actionUrl);
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }
}

