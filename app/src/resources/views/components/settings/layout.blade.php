@php
    $settingsNav = [
        ['route' => 'profile.edit', 'label' => __('Profile')],
        ['route' => 'security.edit', 'label' => __('Security')],
        ['route' => 'appearance.edit', 'label' => __('Appearance')],
    ];
@endphp

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <nav aria-label="{{ __('Settings') }}" class="flex flex-col gap-1">
            @foreach ($settingsNav as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    @class([
                        'rounded-lg px-3 py-2 text-sm font-medium transition',
                        'bg-'.$mainTheme.'-100 text-'.$mainTheme.'-900 dark:bg-'.$mainTheme.'-900/40 dark:text-'.$mainTheme.'-100' => $active,
                        'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => ! $active,
                    ])
                    @if ($active) aria-current="page" @endif
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>
    </div>

    <x-fltc::separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <x-fltc::heading level="2">{{ $heading ?? '' }}</x-fltc::heading>
        <x-fltc::subheading>{{ $subheading ?? '' }}</x-fltc::subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
