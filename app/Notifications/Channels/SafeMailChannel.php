<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Same as Laravel's normal "mail" channel, but if the email cannot be sent
 * (wrong SMTP password, mail server down, bad address...) it only writes the
 * error to storage/logs/laravel.log instead of crashing the page.
 * The in-app (bell) notification is still saved either way.
 */
class SafeMailChannel extends MailChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (empty($notifiable->email ?? null)) {
            return null; // nobody to email
        }

        try {
            return parent::send($notifiable, $notification);
        } catch (Throwable $e) {
            Log::warning('Email notification failed: ' . class_basename($notification), [
                'to' => $notifiable->email,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}