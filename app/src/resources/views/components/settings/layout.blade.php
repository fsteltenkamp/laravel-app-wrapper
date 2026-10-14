@php
    $settingsNav = [
        ['route' => 'profile.edit', 'label' => __('Profile'), 'icon' => 'user'],
        ['route' => 'security.edit', 'label' => __('Security'), 'icon' => 'shield-check'],
        ['route' => 'appearance.edit', 'label' => __('Appearance'), 'icon' => 'palette'],
    ];
@endphp

<div class="w-full">
    <x-fltc::tabs color="primary" aria-label="{{ __('Settings') }}" class="mb-6">
        <x-slot:list>
            @foreach ($settingsNav as $item)
                <x-fltc::tabs.tab
                    :href="route($item['route'])"
                    :current="request()->routeIs($item['route'])"
                    :icon="$item['icon']"
                    wire:navigate
                >{{ $item['label'] }}</x-fltc::tabs.tab>
            @endforeach
        </x-slot:list>
    </x-fltc::tabs>

    <x-fltc::heading level="2">{{ $heading ?? '' }}</x-fltc::heading>
    <x-fltc::subheading>{{ $subheading ?? '' }}</x-fltc::subheading>

    <div class="mt-5 w-full">
        {{ $slot }}
    </div>
</div>
