<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-4 flex justify-end">
        <x-fltc::button :theme="$mainTheme" icon="plus" wire:click="create">{{ __('New permission group') }}</x-fltc::button>
    </div>

    <div class="mb-4 w-full max-w-sm">
        <x-fltc::form.input.text
            model="search"
            live
            :theme="$mainTheme"
            :placeholder="__('Search by name…')"
            icon="magnifying-glass"
        />
    </div>

    <x-fltc::table :theme="$mainTheme" hover>
        <x-fltc::table.head>
            <x-fltc::table.row>
                <x-fltc::table.cell header>{{ __('Name') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Permissions') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Users') }}</x-fltc::table.cell>
                <x-fltc::table.cell header class="text-right">{{ __('Actions') }}</x-fltc::table.cell>
            </x-fltc::table.row>
        </x-fltc::table.head>
        <tbody>
            @forelse ($roles as $role)
                <x-fltc::table.row wire:key="role-{{ $role->id }}">
                    <x-fltc::table.cell nowrap class="font-medium">{{ $role->name }}</x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <x-fltc::badge :theme="$mainTheme">{{ $role->permissions_count }}</x-fltc::badge>
                    </x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <x-fltc::badge :theme="$mainTheme">{{ $role->users_count }}</x-fltc::badge>
                    </x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <div class="flex justify-end gap-2">
                            <x-fltc::button.link
                                :theme="$mainTheme"
                                variant="outline"
                                height="sm"
                                icon="pencil-simple"
                                :href="route('admin.roles.edit', $role)"
                                navigate
                            >{{ __('Edit') }}</x-fltc::button.link>
                            <x-fltc::button
                                theme="red"
                                variant="outline"
                                height="sm"
                                icon="trash"
                                wire:click="confirmDelete({{ $role->id }})"
                            >{{ __('Delete') }}</x-fltc::button>
                        </div>
                    </x-fltc::table.cell>
                </x-fltc::table.row>
            @empty
                <x-fltc::table.row>
                    <x-fltc::table.cell colspan="4" class="text-center text-zinc-500">
                        {{ __('No permission groups found.') }}
                    </x-fltc::table.cell>
                </x-fltc::table.row>
            @endforelse
        </tbody>
    </x-fltc::table>

    <div class="mt-4">
        {{ $roles->links() }}
    </div>

    {{-- Create role modal --}}
    <x-fltc::modal name="role-form" maxWidth="md">
        <form wire:submit="save" class="space-y-6">
            <div class="space-y-1">
                <x-fltc::heading level="2" size="lg">{{ __('New permission group') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Give the group a name. Assign permissions on the edit page.') }}</x-fltc::subheading>
            </div>

            <x-fltc::form.input.text model="name" :label="__('Name')" :theme="$mainTheme" required />

            <div class="flex justify-end gap-2">
                <x-fltc::button variant="outline" theme="zinc" x-on:click="close()">{{ __('Cancel') }}</x-fltc::button>
                <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Create group') }}</x-fltc::button>
            </div>
        </form>
    </x-fltc::modal>

    <x-fltc::modal.confirm
        name="confirm-role-deletion"
        :title="__('Delete permission group?')"
        :description="__('This removes the group from all users. Permissions themselves are not deleted.')"
    >
        <x-fltc::button theme="red" icon="trash" wire:click="deleteRole">{{ __('Delete group') }}</x-fltc::button>
    </x-fltc::modal.confirm>
</section>
