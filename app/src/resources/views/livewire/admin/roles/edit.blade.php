<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <x-fltc::heading level="2" size="xl">{{ $role->name }}</x-fltc::heading>
            <x-fltc::subheading>{{ $role->permissions()->count() }} {{ __('permissions') }}</x-fltc::subheading>
        </div>
        <x-fltc::button.link :theme="$mainTheme" variant="outline" height="sm" icon="arrow-left" :href="route('admin.roles.index')" navigate>
            {{ __('Back to roles') }}
        </x-fltc::button.link>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Name --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Group name') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Update the display name for this permission group') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveName" class="space-y-4">
                    <x-fltc::form.input.text model="name" :label="__('Name')" :theme="$mainTheme" required />
                    <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save name') }}</x-fltc::button>
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Permissions --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Permissions') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Choose which permissions are granted by this group') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="savePermissions" class="space-y-3">
                    @forelse ($allPermissions as $permission)
                        <x-fltc::form.checkbox
                            :id="'perm-'.$permission->id"
                            model="selectedPermissions"
                            :value="$permission->name"
                            :label="$permission->label ?: $permission->name"
                            :theme="$mainTheme"
                            class="w-full"
                            wire:key="perm-{{ $permission->id }}"
                        />
                    @empty
                        <x-fltc::text class="text-zinc-500">{{ __('No permissions defined yet.') }}</x-fltc::text>
                    @endforelse

                    @if ($allPermissions->isNotEmpty())
                        <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save permissions') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>
</section>
