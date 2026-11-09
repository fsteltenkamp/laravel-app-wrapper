<?php

namespace App\Notifications\Channels;

use App\Models\NotificationChannel;
use App\Notifications\NotificationMessage;
use Illuminate\Support\Str;

class DiscordChannel extends HttpChannelDriver
{
    private const WEBHOOK_URL = '#^https://((ptb|canary)\.)?discord(app)?\.com/api(/v\d+)?/webhooks/\d+/[\w-]+/?$#';

    public function label(): string
    {
        return 'Discord webhook';
    }

    public function icon(): string
    {
        return 'discord-logo';
    }

    /**
     * Links go in the text: Discord rejects embeds whose url is e.g. localhost.
     */
    public function defaultTemplate(): string
    {
        return "{message}\n\n{url}";
    }

    public function fields(): array
    {
        return [
            'url' => new ChannelField(
                label: 'Webhook URL',
                input: 'password',
                rules: ['regex:'.self::WEBHOOK_URL, 'max:2048', $this->allowedUrlRule()],
                note: 'In Discord: channel settings, Integrations, Webhooks.',
                secret: true,
            ),
        ];
    }

    public function send(NotificationChannel $channel, NotificationMessage $message): void
    {
        $this->postJson(
            $channel->config['url'],
            json_encode([
                // Names in a message must not be able to ping @everyone.
                'allowed_mentions' => ['parse' => []],
                'embeds' => [[
                    'title' => Str::limit($message->title, 250),
                    'description' => Str::limit($channel->renderBody($message), 4000),
                    'timestamp' => $message->occurredAt,
                ]],
            ], JSON_THROW_ON_ERROR),
        );
    }
}
