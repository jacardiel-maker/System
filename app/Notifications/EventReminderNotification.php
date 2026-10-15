<?php

namespace App\Notifications;

use App\Models\Event;
use App\Notifications\Channels\SafeMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Event $event, public string $type = 'reminder')
    {
    }

    /** Bell notification + email */
    public function via(object $notifiable): array
    {
        return ['database', SafeMailChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->event;
        $isUpdate = $this->type === 'update';

        $mail = (new MailMessage)
            ->subject(($isUpdate ? 'Event Update: ' : 'Event Reminder: ') . $event->event_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($isUpdate
                ? 'An event you are assigned to has been updated. Please review the latest details.'
                : 'This is a reminder about an upcoming event you are assigned to.')
            ->line('**Event:** ' . $event->event_name)
            ->line('**Date:** ' . $event->event_date->format('l, F d, Y h:i A'));

        if ($event->setup_time) {
            $mail->line('**Setup Time:** ' . $event->setup_time->format('F d, Y h:i A'));
        }
        if ($event->venue_address) {
            $mail->line('**Venue:** ' . $event->venue_address);
        }

        // Owner/admin goes to the admin event page, waiters to their own event page
        $url = (method_exists($notifiable, 'isAdmin') && $notifiable->isAdmin())
            ? route('events.show', $event)
            : route('waiters.my-event', $event);

        return $mail
            ->action('View Event', $url)
            ->salutation("Steph's Catering by Chef Nono");
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