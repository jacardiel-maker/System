<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Waiter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventDeclinedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Event $event,
        public Waiter $waiter
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_name' => $this->event->event_name,
            'waiter_id' => $this->waiter->id,
            'waiter_name' => $this->waiter->user->name,
            'title' => 'Event Declined',
            'message' => $this->waiter->user->name . ' declined the event: ' . $this->event->event_name,
            'declined_at' => now()->toDateTimeString(),
            'type' => 'event_declined',
        ];
    }
}