<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Waiter;
use App\Notifications\Channels\SafeMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventDeclinedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Event $event,
        public Waiter $waiter
    ) {}

    /** Bell notification + email to the owner/admin */
    public function via(object $notifiable): array
    {
        return ['database', SafeMailChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $waiterName = $this->waiter->user->name ?? 'A waiter';

        return (new MailMessage)
            ->subject('Event Declined: ' . $this->event->event_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($waiterName . ' has **declined** their assignment. You may need to assign a replacement.')
            ->line('**Event:** ' . $this->event->event_name)
            ->line('**Date:** ' . $this->event->event_date->format('l, F d, Y h:i A'))
            ->line('**Waiter:** ' . $waiterName)
            ->action('View Event', route('events.show', $this->event))
            ->salutation('E Serve');
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