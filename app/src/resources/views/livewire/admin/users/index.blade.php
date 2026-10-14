<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div class="w-full max-w-sm">
            <x-fltc::form.input.text
                model="search"
                live
                inline
                :placeholder="__('Search by name or email…')"
                icon="magnifying-glass"
            />
        </div>

        @can('user.create')
            <x-fltc::button color="primary" icon="plus" wire:click="create">{{ __('New user') }}</x-fltc::button>
        @endcan
    </div>

    <x-fltc::table hover>
        <x-fltc::table.head>
            <x-fltc::table.row>
                <x-fltc::table.cell header>{{ __('Name') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Email') }}</x-fltc::table.cell>
                <x-fltc::table.cell header>{{ __('Teams') }}</x-fltc::table.cell>
                <x-fltc::table.cell header class="text-right">{{ __('Actions') }}</x-fltc::table.cell>
            </x-fltc::table.row>
        </x-fltc::table.head>
        <tbody>
            @forelse ($users as $user)
                <x-fltc::table.row wire:key="user-{{ $user->id }}">
                    <x-fltc::table.cell nowrap class="font-medium">{{ $user->name }}</x-fltc::table.cell>
                    <x-fltc::table.cell>{{ $user->email }}</x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <x-fltc::badge>{{ $user->teams_count }}</x-fltc::badge>
                    </x-fltc::table.cell>
                    <x-fltc::table.cell>
                        <div class="flex justify-end gap-2">
                            @can('user.edit')
                            <x-fltc::button.link
                                variant="outline"
                                height="sm"
                                icon="pencil-simple"
                                :href="route('admin.users.edit', $user)"
                                navigate
                            >{{ __('Edit') }}</x-fltc::button.link>
                            @endcan

                            @can('user.delete')
                            @if ($user->isNot(auth()->user()))
                                <x-fltc::button
                                    color="danger"
                                    variant="outline"
                                    height="sm"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $user->id }})"
                                >{{ __('Delete') }}</x-fltc::button>
                            @endif
                            @endcan
                        </div>
                    </x-fltc::table.cell>
                </x-fltc::table.row>
            @empty
                <x-fltc::table.row>
                    <x-fltc::table.cell colspan="4">
                        <x-fltc::empty size="sm" icon="users" :title="__('No users found.')" />
                    </x-fltc::table.cell>
                </x-fltc::table.row>
            @endforelse
        </tbody>
    </x-fltc::table>

    <div class="mt-4">
        {{ $users->links('fltc::pagination.livewire') }}
    </div>

    {{-- Create user modal --}}
    <x-fltc::modal name="user-form" maxWidth="md">
        <form wire:submit="save" class="space-y-6">
            <div class="space-y-1">
                <x-fltc::heading level="2" size="lg">{{ __('New user') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Create a new user account manually.') }}</x-fltc::subheading>
            </div>

            <x-fltc::form.input.text model="newName" :label="__('Name')" required />
            <x-fltc::form.input.email model="newEmail" :label="__('Email')" required />
            <x-fltc::form.input.password model="newPassword" :label="__('Password')" required />

            <div class="flex justify-end gap-2">
                <x-fltc::button variant="outline" x-on:click="close()">{{ __('Cancel') }}</x-fltc::button>
                <x-fltc::button color="primary" type="submit" icon="check">{{ __('Create user') }}</x-fltc::button>
            </div>
        </form>
    </x-fltc::modal>

    <x-fltc::modal.confirm
        name="confirm-user-deletion"
        :title="__('Delete user?')"
        :description="__('This permanently deletes the user and all of their resources. This cannot be undone.')"
    >
        <x-fltc::button color="danger" icon="trash" wire:click="deleteUser">{{ __('Delete user') }}</x-fltc::button>
    </x-fltc::modal.confirm>
</section>
