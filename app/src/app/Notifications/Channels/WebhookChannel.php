<?php

namespace App\Notifications\Channels;

use App\Models\NotificationChannel;
use App\Notifications\NotificationMessage;

class WebhookChannel extends HttpChannelDriver
{
    public function label(): string
    {
        return 'Generic webhook';
    }

    public function icon(): string
    {
        return 'webhooks-logo';
    }

    public function defaultTemplate(): string
    {
        return '{message}';
    }

    public function fields(): array
    {
        return [
            'url' => new ChannelField(
                label: 'URL',
                input: 'url',
                rules: ['url:http,https', 'max:2048', $this->allowedUrlRule()],
                note: 'Receives a JSON POST request for every notification.',
            ),
            'secret' => new ChannelField(
                label: 'Signing secret',
                input: 'password',
                required: false,
                rules: ['string', 'max:255'],
                note: 'Optional. Adds an X-Signature header: sha256= and the HMAC-SHA256 of the request body.',
                secret: true,
            ),
        ];
    }

    public function send(NotificationChannel $channel, NotificationMessage $message): void
    {
        $body = json_encode([
            'event' => $message->event,
            'title' => $message->title,
            'message' => $channel->renderBody($message),
            'url' => $message->url,
            'occurred_at' => $message->occurredAt,
        ], JSON_THROW_ON_ERROR);

        $secret = $channel->config['secret'] ?? '';

        $this->postJson(
            $channel->config['url'],
            $body,
            $secret !== '' ? ['X-Signature' => 'sha256='.hash_hmac('sha256', $body, $secret)] : [],
        );
    }
}
