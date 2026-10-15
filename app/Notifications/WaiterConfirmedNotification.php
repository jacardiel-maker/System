<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Waiter;
use App\Notifications\Channels\SafeMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaiterConfirmedNotification extends Notification
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
        $role = $this->waiter->pivot->role ?? null;

        $mail = (new MailMessage)
            ->subject('Waiter Confirmed: ' . $this->event->event_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($waiterName . ' has **confirmed** their participation.')
            ->line('**Event:** ' . $this->event->event_name)
            ->line('**Date:** ' . $this->event->event_date->format('l, F d, Y h:i A'))
            ->line('**Waiter:** ' . $waiterName);

        if ($role) {
            $mail->line('**Role:** ' . ucwords(str_replace('_', ' ', $role)));
        }

        return $mail
            ->action('View Event', route('events.show', $this->event))
            ->salutation('E Serve');
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