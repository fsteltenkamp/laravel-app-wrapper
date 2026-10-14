@php
    $adminNav = [
        ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => __('Users'), 'icon' => 'users'],
        ['route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'label' => __('Roles'), 'icon' => 'users-three'],
        ['route' => 'admin.permissions.index', 'active' => 'admin.permissions.*', 'label' => __('Permissions'), 'icon' => 'key'],
        ['route' => 'admin.teams.index', 'active' => 'admin.teams.*', 'label' => __('Teams'), 'icon' => 'buildings'],
    ];
@endphp

<div class="relative mb-6 w-full">
    <x-fltc::heading size="2xl" level="1">{{ __('User management') }}</x-fltc::heading>
    <x-fltc::subheading size="lg" class="mb-4">{{ __('Manage users, permission groups, permissions and teams') }}</x-fltc::subheading>

    <x-fltc::tabs color="primary" aria-label="{{ __('User management') }}">
        <x-slot:list>
            @foreach ($adminNav as $item)
                <x-fltc::tabs.tab
                    :href="route($item['route'])"
                    :current="request()->routeIs($item['active'])"
                    :icon="$item['icon']"
                    wire:navigate
                >{{ $item['label'] }}</x-fltc::tabs.tab>
            @endforeach
        </x-slot:list>
    </x-fltc::tabs>
</div>
