<section class="w-full">
    @include('partials.settings-heading')

    <x-fltc::heading level="2" class="sr-only">{{ __('Notification settings') }}</x-fltc::heading>

    <x-settings.layout :heading="__('Notifications')" :subheading="__('Choose where notifications are sent and which events you want to hear about')">
        <div class="space-y-8">
            {{-- Channels --}}
            <div>
                <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                    <x-fltc::heading level="3" size="lg">{{ __('Channels') }}</x-fltc::heading>
                    <x-fltc::button color="primary" icon="plus" wire:click="createChannel">{{ __('New channel') }}</x-fltc::button>
                </div>

                <x-fltc::table hover>
                    <x-fltc::table.head>
                        <x-fltc::table.row>
                            <x-fltc::table.cell header>{{ __('Name') }}</x-fltc::table.cell>
                            <x-fltc::table.cell header>{{ __('Type') }}</x-fltc::table.cell>
                            <x-fltc::table.cell header>{{ __('Status') }}</x-fltc::table.cell>
                            <x-fltc::table.cell header class="text-right">{{ __('Actions') }}</x-fltc::table.cell>
                        </x-fltc::table.row>
                    </x-fltc::table.head>
                    <tbody>
                        @forelse ($this->channels as $channel)
                            <x-fltc::table.row wire:key="channel-{{ $channel->id }}">
                                <x-fltc::table.cell nowrap class="font-medium">{{ $channel->name }}</x-fltc::table.cell>
                                <x-fltc::table.cell nowrap>
                                    <span class="inline-flex items-center gap-2">
                                        <x-fltc::icon :name="$types[$channel->type]->icon()" />
                                        {{ __($types[$channel->type]->label()) }}
                                    </span>
                                </x-fltc::table.cell>
                                <x-fltc::table.cell>
                                    @if (! $channel->enabled)
                                        <x-fltc::badge>{{ __('Disabled') }}</x-fltc::badge>
                                    @elseif ($channel->last_error !== null)
                                        <x-fltc::badge color="danger">{{ __('Failing') }}</x-fltc::badge>
                                        <p class="mt-1 text-xs text-base-500 dark:text-base-400">{{ $channel->last_error }}</p>
                                    @else
                                        <x-fltc::badge color="success">{{ __('Active') }}</x-fltc::badge>
                                    @endif
                                </x-fltc::table.cell>
                                <x-fltc::table.cell>
                                    <div class="flex justify-end gap-2">
                                        <x-fltc::button variant="outline" height="sm" icon="paper-plane-tilt" wire:click="sendTest({{ $channel->id }})">{{ __('Test') }}</x-fltc::button>
                                        <x-fltc::button variant="outline" height="sm" icon="pencil-simple" wire:click="editChannel({{ $channel->id }})">{{ __('Edit') }}</x-fltc::button>
                                        <x-fltc::button color="danger" variant="outline" height="sm" icon="trash" wire:click="confirmDelete({{ $channel->id }})">{{ __('Delete') }}</x-fltc::button>
                                    </div>
                                </x-fltc::table.cell>
                            </x-fltc::table.row>
                        @empty
                            <x-fltc::table.row>
                                <x-fltc::table.cell colspan="4">
                                    <x-fltc::empty size="sm" icon="bell-slash" :title="__('No channels yet.')" />
                                </x-fltc::table.cell>
                            </x-fltc::table.row>
                        @endforelse
                    </tbody>
                </x-fltc::table>
            </div>

            {{-- Subscriptions --}}
            @if ($this->channels->isNotEmpty())
                <form wire:submit="saveSubscriptions">
                    <x-fltc::heading level="3" size="lg">{{ __('Events') }}</x-fltc::heading>
                    <x-fltc::subheading class="mb-4">{{ __('Only events for things you have access to are listed and delivered.') }}</x-fltc::subheading>

                    @if ($eventGroups->isEmpty())
                        <x-fltc::empty size="sm" icon="bell-slash" :title="__('There are no events available to you.')" />
                    @else
                        <x-fltc::table>
                            <x-fltc::table.head>
                                <x-fltc::table.row>
                                    <x-fltc::table.cell header>{{ __('Event') }}</x-fltc::table.cell>
                                    @foreach ($this->channels as $channel)
                                        <x-fltc::table.cell header wire:key="column-{{ $channel->id }}">{{ $channel->name }}</x-fltc::table.cell>
                                    @endforeach
                                </x-fltc::table.row>
                            </x-fltc::table.head>
                            <tbody>
                                @foreach ($eventGroups as $group => $events)
                                    <x-fltc::table.row wire:key="group-{{ $group }}">
                                        <x-fltc::table.cell :colspan="$this->channels->count() + 1" class="text-xs font-semibold tracking-wide uppercase">{{ __($group) }}</x-fltc::table.cell>
                                    </x-fltc::table.row>

                                    @foreach ($events as $event)
                                        <x-fltc::table.row wire:key="event-{{ $event->key }}">
                                            <x-fltc::table.cell>
                                                <div class="font-medium">{{ __($event->label) }}</div>
                                                <div class="text-xs text-base-500 dark:text-base-400">{{ __($event->description) }}</div>
                                            </x-fltc::table.cell>
                                            @foreach ($this->channels as $channel)
                                                <x-fltc::table.cell wire:key="cell-{{ $event->key }}-{{ $channel->id }}">
                                                    <x-fltc::form.checkbox
                                                        :id="'subscription-'.$event->key.'-'.$channel->id"
                                                        model="subscriptions"
                                                        :value="$event->key.'|'.$channel->id"
                                                        :bordered="false"
                                                        color="primary"
                                                        aria-label="{{ __($event->label) }}: {{ $channel->name }}"
                                                    />
                                                </x-fltc::table.cell>
                                            @endforeach
                                        </x-fltc::table.row>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </x-fltc::table>

                        <x-fltc::button color="primary" type="submit" icon="check" class="mt-4">{{ __('Save events') }}</x-fltc::button>
                    @endif
                </form>
            @endif
        </div>
    </x-settings.layout>

    {{-- Create / edit channel modal --}}
    <x-fltc::modal name="notification-channel-form" maxWidth="md">
        <form wire:submit="saveChannel" class="space-y-6">
            <x-fltc::heading level="2" size="lg">{{ $editingId ? __('Edit channel') : __('New channel') }}</x-fltc::heading>

            <x-fltc::form.input.text model="channelName" :label="__('Name')" required />

            <x-fltc::form.input.select model="channelType" live :label="__('Type')" :disabled="$editingId !== null">
                @foreach ($types as $key => $driver)
                    <option value="{{ $key }}">{{ __($driver->label()) }}</option>
                @endforeach
            </x-fltc::form.input.select>

            @foreach ($this->channelFields as $key => $field)
                <x-fltc::form.input.text
                    wire:key="field-{{ $channelType }}-{{ $key }}"
                    :model="'channelConfig.'.$key"
                    :type="$field->input"
                    :label="__($field->label)"
                    :note="$field->secret && $editingId ? __('Leave empty to keep the current value.') : __($field->note)"
                    :required="$field->required && ! ($field->secret && $editingId)"
                    :autocomplete="$field->secret ? 'new-password' : 'off'"
                />
            @endforeach

            @if ($channelType === 'email')
                <x-fltc::text>{{ __('Notifications are sent to :email.', ['email' => auth()->user()->email]) }}</x-fltc::text>
            @endif

            <x-fltc::form.textarea
                id="channelTemplate"
                model="channelTemplate"
                :rows="3"
                :label="__('Message')"
                :placeholder="($types[$channelType] ?? null)?->defaultTemplate() ?? ''"
                :note="__('Optional. Placeholders: {title}, {message}, {url}, {event}, {time}.')"
            />

            <x-fltc::form.checkbox id="channel-enabled" model="channelEnabled" variant="toggle" color="primary" :label="__('Enabled')" />

            <div class="flex justify-end gap-2">
                <x-fltc::button variant="outline" x-on:click="close()">{{ __('Cancel') }}</x-fltc::button>
                <x-fltc::button color="primary" type="submit" icon="check">{{ __('Save channel') }}</x-fltc::button>
            </div>
        </form>
    </x-fltc::modal>

    <x-fltc::modal.confirm
        name="confirm-channel-deletion"
        :title="__('Delete channel?')"
        :description="__('This removes the channel and its event subscriptions.')"
    >
        <x-fltc::button color="danger" icon="trash" wire:click="deleteChannel">{{ __('Delete channel') }}</x-fltc::button>
    </x-fltc::modal.confirm>
</section>
