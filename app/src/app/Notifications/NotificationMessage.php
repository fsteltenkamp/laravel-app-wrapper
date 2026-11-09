<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Date;

final readonly class NotificationMessage
{
    public function __construct(
        public string $event,
        public string $title,
        public string $body,
        public ?string $url,
        public string $occurredAt,
    ) {}

    /**
     * Fills in a channel's message template. Falls back to the body when nothing
     * is left, e.g. a template of only {url} for an event without a link.
     */
    public function render(string $template): string
    {
        // strtr is a single pass, so a value containing "{url}" stays as it is.
        $text = trim(strtr($template, [
            '{title}' => $this->title,
            '{message}' => $this->body,
            '{url}' => $this->url ?? '',
            '{event}' => $this->event,
            '{time}' => Date::parse($this->occurredAt)->format('Y-m-d H:i'),
        ]));

        return $text !== '' ? $text : $this->body;
    }
}
