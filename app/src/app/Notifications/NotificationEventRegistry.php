<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class NotificationEventRegistry
{
    /**
     * @return Collection<string, NotificationEvent>
     */
    public function all(): Collection
    {
        /** @var array<string, array{label: string, description: string, group: string, ability?: string, element_ability?: string}> $events */
        $events = config('notifications.events');

        return collect($events)->map(fn (array $event, string $key) => new NotificationEvent(
            key: $key,
            label: $event['label'],
            description: $event['description'],
            group: $event['group'],
            ability: $event['ability'] ?? null,
            elementAbility: $event['element_ability'] ?? null,
        ));
    }

    /**
     * @throws InvalidArgumentException if the event is not registered
     */
    public function get(string $key): NotificationEvent
    {
        return $this->all()->get($key)
            ?? throw new InvalidArgumentException("Notification event [{$key}] is not registered.");
    }

    /**
     * The events the user is allowed to subscribe to.
     *
     * @return Collection<string, NotificationEvent>
     */
    public function visibleTo(User $user): Collection
    {
        return $this->all()->filter(fn (NotificationEvent $event) => $event->canSubscribe($user));
    }
}
