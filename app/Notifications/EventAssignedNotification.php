<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Event $event)
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
            'title' => 'New Event Assignment',
            'message' => 'You are assigned to: ' . $this->event->event_name,
            'event_date' => $this->event->event_date,
        ];
    }
}