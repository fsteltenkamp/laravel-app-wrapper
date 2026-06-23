<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <x-fltc::heading level="2" size="xl">{{ $team->name }}</x-fltc::heading>
            <x-fltc::subheading>{{ $team->users->count() }} {{ __('members') }}</x-fltc::subheading>
        </div>
        <x-fltc::button.link :theme="$mainTheme" variant="outline" height="sm" icon="arrow-left" :href="route('admin.teams.index')" navigate>
            {{ __('Back to teams') }}
        </x-fltc::button.link>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Team name --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Team name') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Update the display name for this team') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveName" class="space-y-4">
                    <x-fltc::form.input.text model="name" :label="__('Name')" :theme="$mainTheme" required />
                    <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save name') }}</x-fltc::button>
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Members --}}
        <x-fltc::card :theme="$mainTheme">
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
                            :theme="$mainTheme"
                            class="w-full"
                            wire:key="member-{{ $user->id }}"
                        />
                    @empty
                        <x-fltc::text class="text-zinc-500">{{ __('No users yet.') }}</x-fltc::text>
                    @endforelse

                    @if ($allUsers->isNotEmpty())
                        <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save members') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Team roles (permission groups) --}}
        <x-fltc::card :theme="$mainTheme">
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
                            :theme="$mainTheme"
                            class="w-full"
                            wire:key="tr-{{ $role->id }}"
                        />
                    @empty
                        <x-fltc::text class="text-zinc-500">{{ __('No permission groups defined yet.') }}</x-fltc::text>
                    @endforelse

                    @if ($allRoles->isNotEmpty())
                        <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save permission groups') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Team direct permissions --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Direct permissions') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Permissions granted directly to this team — members inherit these') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="savePermissions" class="space-y-3">
                    @if ($allPermissions->isEmpty())
                        <x-fltc::text class="text-zinc-500">{{ __('No permissions defined yet.') }}</x-fltc::text>
                    @else
                        @php
                            $checklistItems = [];
                            foreach ($allPermissions as $permission) {
                                $isDirect    = in_array($permission->name, $selectedPermissions);
                                $sources     = $permissionSources[$permission->name] ?? [];
                                $isInherited = !empty($sources);

                                $labelHtml = e($permission->label ?: $permission->name);
                                foreach ($sources as $source) {
                                    $labelHtml .= ' <span class="inline-flex items-center rounded-full bg-'.$mainTheme.'-100 px-2 py-0.5 text-xs font-medium text-'.$mainTheme.'-700 dark:bg-'.$mainTheme.'-900/40 dark:text-'.$mainTheme.'-300">'.e($source).'</span>';
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
                            :theme="$mainTheme"
                            :strikeThroughChecked="false"
                            manual
                        />

                        <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save direct permissions') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>
</section>
