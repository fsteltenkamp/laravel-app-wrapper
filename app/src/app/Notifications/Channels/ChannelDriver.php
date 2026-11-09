<?php

namespace App\Notifications\Channels;

use App\Models\NotificationChannel;
use App\Notifications\NotificationMessage;

interface ChannelDriver
{
    public function label(): string;

    public function icon(): string;

    /**
     * The message text of channels without a template of their own.
     */
    public function defaultTemplate(): string;

    /**
     * The settings a user fills in for this channel type, keyed by config key.
     *
     * @return array<string, ChannelField>
     */
    public function fields(): array;

    /**
     * @throws ChannelDeliveryFailed with a message that is safe to show the user
     */
    public function send(NotificationChannel $channel, NotificationMessage $message): void;
}
