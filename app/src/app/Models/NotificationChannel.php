<?php

namespace App\Models;

use App\Notifications\Channels\ChannelDriver;
use App\Notifications\NotificationMessage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property array<string, string> $config
 * @property string|null $message_template
 * @property bool $enabled
 * @property Carbon|null $last_sent_at
 * @property Carbon|null $last_failed_at
 * @property string|null $last_error
 * @property-read User $user
 */
#[Fillable(['name', 'type', 'config', 'message_template', 'enabled', 'last_sent_at', 'last_failed_at', 'last_error'])]
#[Hidden(['config'])]
class NotificationChannel extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = ['enabled' => true];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'encrypted:array',
            'enabled' => 'boolean',
            'last_sent_at' => 'datetime',
            'last_failed_at' => 'datetime',
        ];
    }

    /**
     * The available channel types, keyed by type.
     *
     * @return array<string, ChannelDriver>
     */
    public static function types(): array
    {
        /** @var array<string, class-string<ChannelDriver>> $types */
        $types = config('notifications.channels');

        return array_map(fn (string $driver) => app($driver), $types);
    }

    public function driver(): ChannelDriver
    {
        return self::types()[$this->type];
    }

    public function renderBody(NotificationMessage $message): string
    {
        return $message->render($this->message_template ?? $this->driver()->defaultTemplate());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<NotificationSubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(NotificationSubscription::class);
    }

    public function markDelivered(): void
    {
        $this->update(['last_sent_at' => now(), 'last_error' => null, 'last_failed_at' => null]);
    }

    public function markFailed(string $error): void
    {
        $this->update(['last_failed_at' => now(), 'last_error' => $error]);
    }
}
