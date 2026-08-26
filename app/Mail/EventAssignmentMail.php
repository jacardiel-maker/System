<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public User $waiter
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: '🍽️ New Event Assignment: ' . $this->event->event_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-assignment',
            with: [
                'eventName' => $this->event->event_name,
                'eventDate' => $this->event->event_date->format('l, F d, Y'),
                'eventTime' => $this->event->event_date->format('h:i A'),
                'setupTime' => $this->event->setup_time->format('h:i A'),
                'venue' => $this->event->venue_address,
                'waiterName' => $this->waiter->name,
                'eventUrl' => url('/events/' . $this->event->id),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}