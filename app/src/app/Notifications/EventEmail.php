<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Not queued itself: it is sent from inside the DeliverNotification job.
 */
class EventEmail extends Notification
{
    public function __construct(
        public NotificationMessage $message,
        public string $body,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->message->title)
            ->lines(array_filter(array_map('trim', explode("\n", $this->body))));

        return $this->message->url !== null
            ? $mail->action(__('Open'), $this->message->url)
            : $mail;
    }
}
