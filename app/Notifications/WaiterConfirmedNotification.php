<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Waiter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WaiterConfirmedNotification extends Notification
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Waiter Confirmed: ' . $this->event->event_name)
            ->greeting('Hello Admin,')
            ->line($this->waiter->user->name . ' has confirmed their participation for an event.')
            ->line(' ')
            ->line('📅 **Event:** ' . $this->event->event_name)
            ->line('🕐 **Date:** ' . $this->event->event_date->format('F d, Y h:i A'))
            ->line('👤 **Waiter:** ' . $this->waiter->user->name)
            ->line('🎭 **Role:** ' . ucfirst($this->waiter->pivot->role ?? 'waiter'))
            ->action('View Event', url('/events/' . $this->event->id))
            ->salutation('Steph\'s Catering System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'waiter_id' => $this->waiter->id,
            'waiter_name' => $this->waiter->user->name,
            'title' => 'Waiter Confirmed Participation',
            'message' => $this->waiter->user->name . ' confirmed for: ' . $this->event->event_name,
            'event_name' => $this->event->event_name,
            'event_date' => $this->event->event_date,
            'type' => 'waiter_confirmed',
        ];
    }
}