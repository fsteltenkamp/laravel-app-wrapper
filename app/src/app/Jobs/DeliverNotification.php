<?php

namespace App\Jobs;

use App\Models\NotificationChannel;
use App\Notifications\Channels\ChannelDeliveryFailed;
use App\Notifications\NotificationMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class DeliverNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 300];

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public NotificationChannel $channel,
        public NotificationMessage $message,
    ) {}

    public function handle(): void
    {
        if (! $this->channel->enabled) {
            return;
        }

        $this->channel->driver()->send($this->channel, $this->message);

        $this->channel->markDelivered();
    }

    public function failed(?Throwable $exception): void
    {
        $this->channel->markFailed(
            $exception instanceof ChannelDeliveryFailed ? $exception->getMessage() : __('Delivery failed.'),
        );
    }
}
