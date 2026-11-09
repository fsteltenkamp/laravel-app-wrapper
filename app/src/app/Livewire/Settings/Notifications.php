<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\ShowsToasts;
use App\Models\NotificationChannel;
use App\Notifications\Channels\ChannelDeliveryFailed;
use App\Notifications\Channels\ChannelField;
use App\Notifications\NotificationEvent;
use App\Notifications\NotificationEventRegistry;
use App\Notifications\NotificationMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Notification settings')]
class Notifications extends Component
{
    use ShowsToasts;

    public ?int $editingId = null;

    public string $channelName = '';

    public string $channelType = '';

    public string $channelTemplate = '';

    public bool $channelEnabled = true;

    /** @var array<string, string> */
    public array $channelConfig = [];

    public ?int $confirmingDeleteId = null;

    /**
     * Ticked boxes of the subscription table, as "event|channelId".
     *
     * @var array<int, string>
     */
    public array $subscriptions = [];

    public function mount(): void
    {
        $this->subscriptions = $this->channels()
            ->flatMap(fn (NotificationChannel $channel) => $channel->subscriptions->map(
                fn ($subscription) => "{$subscription->event}|{$channel->id}",
            ))
            ->all();
    }

    public function createChannel(): void
    {
        $this->reset(['editingId', 'channelName', 'channelTemplate', 'channelEnabled']);
        $this->resetValidation();
        $this->channelType = array_key_first(NotificationChannel::types()) ?? '';
        $this->updatedChannelType();

        $this->dispatch('open-modal', name: 'notification-channel-form');
    }

    public function editChannel(int $channelId): void
    {
        $channel = $this->ownedChannel($channelId);

        $this->resetValidation();
        $this->editingId = $channel->id;
        $this->channelName = $channel->name;
        $this->channelType = $channel->type;
        $this->channelTemplate = $channel->message_template ?? '';
        $this->channelEnabled = $channel->enabled;
        $this->channelConfig = collect($channel->driver()->fields())
            ->map(fn (ChannelField $field, string $key) => $field->secret ? '' : ($channel->config[$key] ?? $field->default))
            ->all();

        $this->dispatch('open-modal', name: 'notification-channel-form');
    }

    public function updatedChannelType(): void
    {
        $driver = NotificationChannel::types()[$this->channelType] ?? null;

        $this->channelConfig = collect($driver?->fields() ?? [])
            ->map(fn (ChannelField $field) => $field->default)
            ->all();
    }

    public function saveChannel(): void
    {
        $channel = $this->editingId !== null ? $this->ownedChannel($this->editingId) : null;

        // The type of an existing channel is fixed, its settings depend on it.
        $this->channelType = $channel->type ?? $this->channelType;

        $this->validate(['channelType' => ['required', Rule::in(array_keys(NotificationChannel::types()))]]);

        $fields = NotificationChannel::types()[$this->channelType]->fields();
        $rules = [
            'channelName' => ['required', 'string', 'max:255'],
            'channelTemplate' => ['nullable', 'string', 'max:2000'],
        ];
        $attributes = ['channelName' => __('Name'), 'channelTemplate' => __('Message')];

        foreach ($fields as $key => $field) {
            $keepsStoredValue = $field->secret && $channel !== null;

            $rules["channelConfig.{$key}"] = [$field->required && ! $keepsStoredValue ? 'required' : 'nullable', ...$field->rules];
            $attributes["channelConfig.{$key}"] = __($field->label);
        }

        $validated = $this->validate($rules, [], $attributes);

        $config = [];
        foreach ($fields as $key => $field) {
            $value = (string) ($validated['channelConfig'][$key] ?? '');

            $config[$key] = $value === '' && $field->secret && $channel !== null
                ? ($channel->config[$key] ?? '')
                : $value;
        }

        $values = [
            'name' => $validated['channelName'],
            'enabled' => $this->channelEnabled,
            'config' => $config,
            'message_template' => filled($this->channelTemplate) ? $this->channelTemplate : null,
            'last_error' => null,
            'last_failed_at' => null,
        ];

        if ($channel !== null) {
            $channel->update($values);
        } else {
            Auth::user()->notificationChannels()->create([...$values, 'type' => $this->channelType]);
        }

        unset($this->channels);

        $this->toast(__('Channel saved.'));
        $this->dispatch('close-modal', name: 'notification-channel-form');
    }

    public function sendTest(int $channelId): void
    {
        $channel = $this->ownedChannel($channelId);

        if (! RateLimiter::attempt('notification-test:'.Auth::id(), 5, fn () => true, 60)) {
            $this->toast(__('Too many test notifications. Try again in a minute.'), 'warning');

            return;
        }

        $message = new NotificationMessage(
            event: 'test',
            title: __('Test notification'),
            body: __('Your channel ":name" works.', ['name' => $channel->name]),
            url: route('notifications.edit'),
            occurredAt: now()->toIso8601String(),
        );

        try {
            $channel->driver()->send($channel, $message);
        } catch (ChannelDeliveryFailed $e) {
            $channel->markFailed($e->getMessage());
            unset($this->channels);

            $this->toast(__('Test notification failed.'), 'danger', $e->getMessage());

            return;
        }

        $channel->markDelivered();
        unset($this->channels);

        $this->toast(__('Test notification sent.'));
    }

    public function confirmDelete(int $channelId): void
    {
        $this->confirmingDeleteId = $this->ownedChannel($channelId)->id;

        $this->dispatch('open-modal', name: 'confirm-channel-deletion');
    }

    public function deleteChannel(): void
    {
        if ($this->confirmingDeleteId !== null) {
            $this->ownedChannel($this->confirmingDeleteId)->delete();
            unset($this->channels);

            $this->toast(__('Channel deleted.'));
        }

        $this->confirmingDeleteId = null;

        $this->dispatch('close-modal', name: 'confirm-channel-deletion');
    }

    /**
     * Subscriptions to events the user can no longer see are left alone, they
     * do not deliver and come back to life if the permission returns.
     */
    public function saveSubscriptions(): void
    {
        $events = $this->visibleEvents()->keys();

        foreach ($this->channels() as $channel) {
            $wanted = $events->filter(fn (string $event) => in_array("{$event}|{$channel->id}", $this->subscriptions, true));

            $channel->subscriptions()->whereIn('event', $events)->whereNotIn('event', $wanted)->delete();

            foreach ($wanted as $event) {
                $channel->subscriptions()->firstOrCreate(['event' => $event]);
            }
        }

        $this->toast(__('Subscriptions updated.'));
    }

    /**
     * @return Collection<int, NotificationChannel>
     */
    #[Computed]
    public function channels(): Collection
    {
        return Auth::user()->notificationChannels()->with('subscriptions')->orderBy('name')->get()->toBase();
    }

    /**
     * @return array<string, ChannelField>
     */
    #[Computed]
    public function channelFields(): array
    {
        return (NotificationChannel::types()[$this->channelType] ?? null)?->fields() ?? [];
    }

    /**
     * @return Collection<string, NotificationEvent>
     */
    protected function visibleEvents(): Collection
    {
        return app(NotificationEventRegistry::class)->visibleTo(Auth::user());
    }

    protected function ownedChannel(int $channelId): NotificationChannel
    {
        return Auth::user()->notificationChannels()->findOrFail($channelId);
    }

    public function render(): View
    {
        return view('livewire.settings.notifications', [
            'types' => NotificationChannel::types(),
            'eventGroups' => $this->visibleEvents()->groupBy('group'),
        ]);
    }
}
