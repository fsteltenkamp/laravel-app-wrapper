<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <x-fltc::heading level="2" size="xl">{{ $user->name }}</x-fltc::heading>
            <x-fltc::subheading>{{ $user->email }}</x-fltc::subheading>
        </div>
        <x-fltc::button.link :theme="$mainTheme" variant="outline" height="sm" icon="arrow-left" :href="route('admin.users.index')" navigate>
            {{ __('Back to users') }}
        </x-fltc::button.link>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Profile') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Update the name and email address') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveProfile" class="space-y-4">
                    <x-fltc::form.input.text model="name" :label="__('Name')" :theme="$mainTheme" required />
                    <x-fltc::form.input.email model="email" :label="__('Email')" :theme="$mainTheme" required />
                    <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save profile') }}</x-fltc::button>
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Team membership --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Team membership') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Choose which teams this user belongs to') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveTeams" class="space-y-4">
                    @forelse ($teams as $team)
                        <x-fltc::form.checkbox
                            :id="'team-'.$team->id"
                            model="selectedTeams"
                            :value="$team->id"
                            :label="$team->name"
                            :theme="$mainTheme"
                            class="w-full"
                            wire:key="team-{{ $team->id }}"
                        />
                    @empty
                        <x-fltc::text class="text-zinc-500">{{ __('No teams yet.') }}</x-fltc::text>
                    @endforelse

                    @if ($teams->isNotEmpty())
                        <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save membership') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>

    {{-- Team context selector --}}
    <div class="mt-6">
        <x-fltc::card :theme="$accentTheme">
            <x-fltc::card.body>
                <label for="team-context" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ __('Assignment context') }}
                </label>
                <x-fltc::subheading class="mb-3">
                    {{ __('Permission groups and direct permissions below are assigned within the selected team. Choose “Global” for team-independent assignments.') }}
                </x-fltc::subheading>
                <select
                    id="team-context"
                    wire:model.live="teamContext"
                    class="w-full max-w-sm rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-{{ $mainTheme }}-500 focus:ring-{{ $mainTheme }}-500 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="">{{ __('Global (team-independent)') }}</option>
                    @foreach ($memberTeams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                @if ($memberTeams->isEmpty())
                    <x-fltc::text class="mt-2 text-zinc-500">{{ __('This user is not a member of any team yet — only global assignments are available.') }}</x-fltc::text>
                @endif
            </x-fltc::card.body>
        </x-fltc::card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Permission groups (roles) --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Permission groups') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Roles granted to this user in the selected context') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="saveRoles" class="space-y-3">
                    @forelse ($roles as $role)
                        <x-fltc::form.checkbox
                            :id="'role-'.$role->id"
                            model="selectedRoles"
                            :value="$role->name"
                            :label="$role->name"
                            :theme="$mainTheme"
                            class="w-full"
                            wire:key="role-{{ $role->id }}"
                        />
                    @empty
                        <x-fltc::text class="text-zinc-500">{{ __('No permission groups defined yet.') }}</x-fltc::text>
                    @endforelse

                    @if ($roles->isNotEmpty())
                        <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save permission groups') }}</x-fltc::button>
                    @endif
                </form>
            </x-fltc::card.body>
        </x-fltc::card>

        {{-- Direct permissions --}}
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Direct permissions') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Permissions granted directly, in addition to permission groups') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="savePermissions" class="space-y-3">
                    @if ($permissions->isEmpty())
                        <x-fltc::text class="text-zinc-500">{{ __('No permissions defined yet.') }}</x-fltc::text>
                    @else
                        @php
                            $checklistItems = [];
                            foreach ($permissions as $permission) {
                                $isDirect    = in_array($permission->name, $selectedPermissions);
                                $sources     = $permissionSources[$permission->name] ?? [];
                                $isInherited = !empty($sources);

                                $labelHtml = e($permission->label ?: $permission->name);
                                foreach ($sources as $source) {
                                    $labelHtml .= ' <span class="inline-flex items-center rounded-full bg-'.$mainTheme.'-100 px-2 py-0.5 text-xs font-medium text-'.$mainTheme.'-700 dark:bg-'.$mainTheme.'-900/40 dark:text-'.$mainTheme.'-300">'.e('Inherited from: ' . $source).'</span>';
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
