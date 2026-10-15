<?php

namespace App\Notifications;

use App\Models\Event;
use App\Notifications\Channels\SafeMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Event $event)
    {
    }

    /** Bell notification + email (sent right away, no queue worker needed) */
    public function via(object $notifiable): array
    {
        return ['database', SafeMailChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->event;

        $mail = (new MailMessage)
            ->subject('New Event Assignment: ' . $event->event_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been assigned to an event. Please log in to confirm or decline your participation.')
            ->line('**Event:** ' . $event->event_name)
            ->line('**Date:** ' . $event->event_date->format('l, F d, Y h:i A'));

        if ($event->setup_time) {
            $mail->line('**Setup Time:** ' . $event->setup_time->format('F d, Y h:i A'));
        }
        if ($event->venue_address) {
            $mail->line('**Venue:** ' . $event->venue_address);
        }

        return $mail
            ->action('View My Event', route('waiters.my-event', $event))
            ->salutation("Steph's Catering by Chef Nono");
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