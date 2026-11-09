<?php

namespace App\Notifications\Channels;

use App\Models\NotificationChannel;
use App\Notifications\NotificationMessage;

class GotifyChannel extends HttpChannelDriver
{
    public function label(): string
    {
        return 'Gotify';
    }

    public function icon(): string
    {
        return 'bell-ringing';
    }

    public function defaultTemplate(): string
    {
        return "{message}\n\n{url}";
    }

    public function fields(): array
    {
        return [
            'url' => new ChannelField(
                label: 'Server URL',
                input: 'url',
                rules: ['url:http,https', 'max:2048', $this->allowedUrlRule()],
                note: 'For example https://gotify.example.com',
            ),
            'token' => new ChannelField(
                label: 'Application token',
                input: 'password',
                rules: ['string', 'max:255'],
                secret: true,
            ),
            'priority' => new ChannelField(
                label: 'Priority',
                input: 'number',
                rules: ['integer', 'between:0,10'],
                note: 'From 0 to 10.',
                default: '5',
            ),
        ];
    }

    public function send(NotificationChannel $channel, NotificationMessage $message): void
    {
        $this->postJson(
            rtrim($channel->config['url'], '/').'/message',
            json_encode([
                'title' => $message->title,
                'message' => $channel->renderBody($message),
                'priority' => (int) $channel->config['priority'],
            ], JSON_THROW_ON_ERROR),
            ['X-Gotify-Key' => $channel->config['token']],
        );
    }
}
