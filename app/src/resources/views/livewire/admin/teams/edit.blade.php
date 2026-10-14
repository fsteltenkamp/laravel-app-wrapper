<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <x-fltc::heading level="2" size="xl">{{ $team->name }}</x-fltc::heading>
            <x-fltc::subheading>{{ $team->users->count() }} {{ __('members') }}</x-fltc::subheading>
        </div>
        <x-fltc::button.link variant="outline" height="sm" icon="arrow-left" :href="route('admin.teams.index')" navigate>
            {{ __('Back to teams') }}
        </x-fltc::button.link>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Team name --}}
        <x-fltc::card>
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Team name') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Update the display name for this team') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveName" class="space-y-4">
                    <x-fltc::form.input.text model="name" :label="__('Name')" required />
                    <x-fltc::button color="primary" type="submit" icon="check">{{ __('Save name') }}</x-fltc::button>
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Members --}}
        <x-fltc::card>
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Members') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Choose which users belong to this team') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveMembers" class="space-y-4">
                    @forelse ($allUsers as $user)
                        <x-fltc::form.checkbox
                            :id="'member-'.$user->id"
                            model="selectedUsers"
                            :value="$user->id"
                            :label="$user->name.' ('.$user->email.')'"
                            class="w-full"
                            wire:key="member-{{ $user->id }}"
                        />
                    @empty
                        <x-fltc::empty size="sm" icon="users" :title="__('No users yet.')" />
                    @endforelse

                    @if ($allUsers->isNotEmpty())
                        <x-fltc::button color="primary" type="submit" icon="check">{{ __('Save members') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Team roles (permission groups) --}}
        <x-fltc::card>
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Permission groups') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Roles assigned to this team — members inherit these groups') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveRoles" class="space-y-3">
                    @forelse ($allRoles as $role)
                        <x-fltc::form.checkbox
                            :id="'team-role-'.$role->id"
                            model="selectedRoles"
                            :value="$role->name"
                            :label="$role->name"
                            class="w-full"
                            wire:key="tr-{{ $role->id }}"
                        />
                    @empty
                        <x-fltc::empty size="sm" icon="users-three" :title="__('No permission groups defined yet.')" />
                    @endforelse

                    @if ($allRoles->isNotEmpty())
                        <x-fltc::button color="primary" type="submit" icon="check">{{ __('Save permission groups') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Team direct permissions --}}
        <x-fltc::card>
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Direct permissions') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Permissions granted directly to this team — members inherit these') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="savePermissions" class="space-y-3">
                    @if ($allPermissions->isEmpty())
                        <x-fltc::empty size="sm" icon="key" :title="__('No permissions defined yet.')" />
                    @else
                        @php
                            $checklistItems = [];
                            foreach ($allPermissions as $permission) {
                                $isDirect    = in_array($permission->name, $selectedPermissions);
                                $sources     = $permissionSources[$permission->name] ?? [];
                                $isInherited = !empty($sources);

                                $labelHtml = e($permission->label ?: $permission->name);
                                foreach ($sources as $source) {
                                    $labelHtml .= ' <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">'.e($source).'</span>';
                                }

                                $checklistItems[$permission->name] = [
                                    'label'        => $permission->label ?: $permission->name,
                                    'labelHtml'    => $labelHtml,
                                    'forceChecked' => $isInherited && !$isDirect,
                                    'status'       => 'pending',
                                ];
                            }
                        @endphp

                        <x-fltc::form.checklist
                            :items="$checklistItems"
                            model="selectedPermissions"
                            :strikeThroughChecked="false"
                            manual
                        />

                        <x-fltc::button color="primary" type="submit" icon="check">{{ __('Save direct permissions') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>
</section>
