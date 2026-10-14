<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div class="w-full max-w-sm">
            <x-fltc::form.input.text
                model="search"
                live
                inline
                :placeholder="__('Search by label or name…')"
                icon="magnifying-glass"
            />
        </div>

        <x-fltc::button color="primary" icon="plus" wire:click="create">{{ __('New permission') }}</x-fltc::button>
    </div>

    <x-fltc::table hover>
        <x-fltc::table.head>
            <x-fltc::table.row>
                <x-fltc::table.cell header>{{ __('Label') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Key') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Groups') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Users') }}</x-fltc::table.cell>
                <x-fltc::table.cell header class="text-right">{{ __('Actions') }}</x-fltc::table.cell>
            </x-fltc::table.row>
        </x-fltc::table.head>
        <tbody>
            @forelse ($permissions as $permission)
                <x-fltc::table.row wire:key="perm-{{ $permission->id }}">
                    <x-fltc::table.cell nowrap class="font-medium">{{ $permission->label }}</x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <code class="rounded bg-base-100 px-1.5 py-0.5 text-xs text-base-700 dark:bg-base-800 dark:text-base-300">{{ $permission->name }}</code>
                    </x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <x-fltc::badge>{{ $permission->roles_count }}</x-fltc::badge>
                    </x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <x-fltc::badge>{{ $permission->users_count }}</x-fltc::badge>
                    </x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <div class="flex justify-end gap-2">
                            <x-fltc::button.link
                                variant="outline"
                                height="sm"
                                icon="pencil-simple"
                                :href="route('admin.permissions.edit', $permission)"
                                navigate
                            >{{ __('Edit') }}</x-fltc::button.link>
                            <x-fltc::button
                                color="danger"
                                variant="outline"
                                height="sm"
                                icon="trash"
                                wire:click="confirmDelete({{ $permission->id }})"
                            >{{ __('Delete') }}</x-fltc::button>
                        </div>
                    </x-fltc::table.cell>
                </x-fltc::table.row>
            @empty
                <x-fltc::table.row>
                    <x-fltc::table.cell colspan="5">
                        <x-fltc::empty size="sm" icon="key" :title="__('No permissions found.')" />
                    </x-fltc::table.cell>
                </x-fltc::table.row>
            @endforelse
        </tbody>
    </x-fltc::table>

    <div class="mt-4">
        {{ $permissions->links('fltc::pagination.livewire') }}
    </div>

    {{-- Create permission modal --}}
    <x-fltc::modal name="permission-form" maxWidth="md">
        <form wire:submit="save" class="space-y-6">
            <div class="space-y-1">
                <x-fltc::heading level="2" size="lg">{{ __('New permission') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('The key is used in @can() checks; the label is shown in the UI.') }}</x-fltc::subheading>
            </div>

            <x-fltc::form.input.text model="label" :label="__('Label')" :placeholder="__('Create Users')" required />
            <x-fltc::form.input.text model="name" :label="__('Key')" :placeholder="__('users.create')" required />

            <div class="flex justify-end gap-2">
                <x-fltc::button variant="outline" x-on:click="close()">{{ __('Cancel') }}</x-fltc::button>
                <x-fltc::button color="primary" type="submit" icon="check">{{ __('Create permission') }}</x-fltc::button>
            </div>
        </form>
    </x-fltc::modal>

    <x-fltc::modal.confirm
        name="confirm-permission-deletion"
        :title="__('Delete permission?')"
        :description="__('This removes the permission from all groups and users. This cannot be undone.')"
    >
        <x-fltc::button color="danger" icon="trash" wire:click="deletePermission">{{ __('Delete permission') }}</x-fltc::button>
    </x-fltc::modal.confirm>
</section>
