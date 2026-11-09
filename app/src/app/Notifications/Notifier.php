<?php

namespace App\Notifications;

use App\Jobs\DeliverNotification;
use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Model;

class Notifier
{
    public function __construct(private NotificationEventRegistry $events) {}

    /**
     * Send an event to the channels subscribed to it. Permissions are checked
     * here, so a subscription stops delivering as soon as access is lost.
     *
     * @param  Model|null  $element  the element the event is about, checked against the event's element_ability
     */
    public function send(string $event, string $message, ?string $url = null, ?Model $element = null): void
    {
        $definition = $this->events->get($event);

        $notification = new NotificationMessage(
            event: $event,
            title: __($definition->label),
            body: $message,
            url: $url,
            occurredAt: now()->toIso8601String(),
        );

        NotificationChannel::query()
            ->where('enabled', true)
            ->whereHas('subscriptions', fn ($query) => $query->where('event', $event))
            ->with('user')
            ->get()
            ->filter(fn (NotificationChannel $channel) => $definition->canReceive($channel->user, $element))
            ->each(fn (NotificationChannel $channel) => DeliverNotification::dispatch($channel, $notification)->afterCommit());
    }
}
