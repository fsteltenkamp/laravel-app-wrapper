<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * An event users can subscribe to, as registered in config/notifications.php.
 */
final readonly class NotificationEvent
{
    public function __construct(
        public string $key,
        public string $label,
        public string $description,
        public string $group,
        public ?string $ability = null,
        public ?string $elementAbility = null,
    ) {}

    public function canSubscribe(User $user): bool
    {
        return $this->ability === null || $user->can($this->ability);
    }

    public function canReceive(User $user, ?Model $element): bool
    {
        if (! $this->canSubscribe($user)) {
            return false;
        }

        if ($this->elementAbility === null) {
            return true;
        }

        return $element !== null && $user->can($this->elementAbility, $element);
    }
}
