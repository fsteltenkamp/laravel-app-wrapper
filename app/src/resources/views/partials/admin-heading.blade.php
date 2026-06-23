@php
    $adminNav = [
        ['route' => 'admin.users.index', 'label' => __('Users'), 'icon' => 'ph ph-users'],
        ['route' => 'admin.roles.index', 'label' => __('Roles'), 'icon' => 'ph ph-users-three'],
        ['route' => 'admin.permissions.index', 'label' => __('Permissions'), 'icon' => 'ph ph-key'],
        ['route' => 'admin.teams.index', 'label' => __('Teams'), 'icon' => 'ph ph-buildings'],
    ];
@endphp

<div class="relative mb-6 w-full">
    <x-fltc::heading size="2xl" level="1">{{ __('User management') }}</x-fltc::heading>
    <x-fltc::subheading size="lg" class="mb-4">{{ __('Manage users, permission groups, permissions and teams') }}</x-fltc::subheading>

    <nav aria-label="{{ __('User management') }}" class="flex flex-wrap gap-1">
        @foreach ($adminNav as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a
                href="{{ route($item['route']) }}"
                wire:navigate
                @class([
                    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                    'bg-'.$mainTheme.'-100 text-'.$mainTheme.'-900 dark:bg-'.$mainTheme.'-900/40 dark:text-'.$mainTheme.'-100' => $active,
                    'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => ! $active,
                ])
                @if ($active) aria-current="page" @endif
            >
                <x-fltc::icon :name="$item['icon']" class="text-base" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <x-fltc::separator variant="subtle" class="mt-4" />
</div>
