<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <div class="flex min-h-screen">
            <x-fltc::nav.sidebar :theme="$mainTheme" class="lg:sticky lg:top-0">
                <x-slot:brand>
                    <a href="app-sidebar-profile.html" class="flex items-center gap-2 font-semibold tracking-tight">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $mainTheme }}-500 text-white">
                            <i class="ph ph-cube text-lg" aria-hidden="true"></i>
                        </span>
                        {{config('app.name')}}
                    </a>
                </x-slot:brand>

                <x-fltc::nav.sidebar.link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="ph ph-squares-four" wire:navigate>{{ __('Dashboard') }}</x-fltc::nav.sidebar.link>

                @canany(['user.create', 'user.edit', 'user.delete'])
                <x-fltc::nav.sidebar.group key="user-management" :label="__('User management')" icon="ph ph-users" :isOpen="request()->routeIs('admin.*')">
                    @canany(['user.create', 'user.edit', 'user.delete'])
                    <x-fltc::nav.sidebar.link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="ph ph-user" wire:navigate>{{ __('Users') }}</x-fltc::nav.sidebar.link>
                    @endcanany
                    <x-fltc::nav.sidebar.link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')" icon="ph ph-users-three" wire:navigate>{{ __('Roles') }}</x-fltc::nav.sidebar.link>
                    <x-fltc::nav.sidebar.link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')" icon="ph ph-key" wire:navigate>{{ __('Permissions') }}</x-fltc::nav.sidebar.link>
                    <x-fltc::nav.sidebar.link :href="route('admin.teams.index')" :active="request()->routeIs('admin.teams.*')" icon="ph ph-buildings" wire:navigate>{{ __('Teams') }}</x-fltc::nav.sidebar.link>
                </x-fltc::nav.sidebar.group>
                @endcanany

                <x-slot:footer>
                    <x-fltc::nav.sidebar.footer>
                        <x-fltc::nav.sidebar.profile name="{{auth()->user()->name}}" email="{{auth()->user()->email}}">
                            <x-fltc::nav.sidebar.link :href="route('profile.edit')" icon="ph ph-user">{{ __('Settings') }}</x-fltc::nav.sidebar.link>
                            <x-fltc::nav.sidebar.postlink icon="ph ph-sign-out" :action="route('logout')">Sign out</x-fltc::nav.sidebar.postlink>
                        </x-fltc::nav.sidebar.profile>
                    </x-fltc::nav.sidebar.footer>
                </x-slot:footer>
            </x-fltc::nav.sidebar>

            <div class="flex min-w-0 flex-1 flex-col">
                <x-fltc::nav.navbar :theme="$mainTheme" stickyTop containerClass="w-full px-4 sm:px-6 lg:px-8">
                    <x-slot:left>
                        <x-fltc::nav.navbar.toggle>
                            <x-fltc::icon name="list"/>
                        </x-fltc::nav.navbar.toggle>
                        <span class="flex items-center text-sm font-medium opacity-70">
                            <i class="ph ph-list mr-2 hidden text-lg lg:inline" aria-hidden="true"></i>
                        </span>
                    </x-slot:left>
                    <x-slot:right>
                        <span class="flex items-center"><x-fltc::darkmode.toggle variant="toggle"/></span>
                    </x-slot:right>
                </x-fltc::nav.navbar>

                <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
