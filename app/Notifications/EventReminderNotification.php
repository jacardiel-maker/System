<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Event $event, public string $type = 'reminder')
    {
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Removed 'mail'
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'title' => $this->type === 'update' ? 'Event Update' : 'Event Reminder',
            'message' => $this->event->event_name . ' - ' . $this->event->event_date->format('M d, Y'),
            'type' => $this->type,
        ];
    }
}