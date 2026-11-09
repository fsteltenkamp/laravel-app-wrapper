<?php

namespace App\Notifications\Channels;

use App\Models\NotificationChannel;
use App\Notifications\EventEmail;
use App\Notifications\NotificationMessage;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Sends to the account's own address, so the app cannot be used to mail strangers.
 */
class EmailChannel implements ChannelDriver
{
    public function label(): string
    {
        return 'Email';
    }

    public function icon(): string
    {
        return 'envelope';
    }

    public function defaultTemplate(): string
    {
        return '{message}';
    }

    public function fields(): array
    {
        return [];
    }

    public function send(NotificationChannel $channel, NotificationMessage $message): void
    {
        try {
            $channel->user->notify(new EventEmail($message, $channel->renderBody($message)));
        } catch (TransportExceptionInterface) {
            throw new ChannelDeliveryFailed(__('The email could not be sent.'));
        }
    }
}
