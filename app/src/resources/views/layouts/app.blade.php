<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <x-fltc::body>
        <div class="flex min-h-screen">
            <x-fltc::nav.sidebar class="lg:sticky lg:top-0">
                <x-slot:brand>
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 font-semibold tracking-tight">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-white">
                            <x-fltc::icon name="cube" size="lg" />
                        </span>
                        {{ config('app.name') }}
                    </a>
                </x-slot:brand>

                <x-fltc::nav.sidebar.link :href="route('dashboard')" icon="ph ph-squares-four" wire:navigate>{{ __('Dashboard') }}</x-fltc::nav.sidebar.link>

                @canany(['user.create', 'user.edit', 'user.delete'])
                <x-fltc::nav.sidebar.group id="user-management" :label="__('User management')" icon="ph ph-users" activeWhen="admin/*">
                    <x-fltc::nav.sidebar.link :href="route('admin.users.index')" activePattern="admin/users*" icon="ph ph-user" wire:navigate>{{ __('Users') }}</x-fltc::nav.sidebar.link>
                    <x-fltc::nav.sidebar.link :href="route('admin.roles.index')" activePattern="admin/roles*" icon="ph ph-users-three" wire:navigate>{{ __('Roles') }}</x-fltc::nav.sidebar.link>
                    <x-fltc::nav.sidebar.link :href="route('admin.permissions.index')" activePattern="admin/permissions*" icon="ph ph-key" wire:navigate>{{ __('Permissions') }}</x-fltc::nav.sidebar.link>
                    <x-fltc::nav.sidebar.link :href="route('admin.teams.index')" activePattern="admin/teams*" icon="ph ph-buildings" wire:navigate>{{ __('Teams') }}</x-fltc::nav.sidebar.link>
                </x-fltc::nav.sidebar.group>
                @endcanany

                <x-slot:footer>
                    <x-fltc::nav.sidebar.footer>
                        <x-fltc::nav.sidebar.profile :name="auth()->user()->name" :email="auth()->user()->email">
                            <x-fltc::nav.sidebar.link :href="route('profile.edit')" icon="ph ph-gear" wire:navigate>{{ __('Settings') }}</x-fltc::nav.sidebar.link>
                            <x-fltc::nav.sidebar.postlink icon="ph ph-sign-out" :action="route('logout')">{{ __('Log out') }}</x-fltc::nav.sidebar.postlink>
                        </x-fltc::nav.sidebar.profile>
                    </x-fltc::nav.sidebar.footer>
                </x-slot:footer>
            </x-fltc::nav.sidebar>

            <div class="flex min-w-0 flex-1 flex-col">
                <x-fltc::nav.navbar stickyTop>
                    <x-slot:left>
                        <x-fltc::nav.navbar.toggle />
                        @if (filled($title ?? null))
                            <x-fltc::nav.navbar.item>{{ $title }}</x-fltc::nav.navbar.item>
                        @endif
                    </x-slot:left>
                    <x-slot:right>
                        <x-fltc::darkmode.toggle variant="toggle" size="sm" class="flex items-center" />
                    </x-slot:right>
                </x-fltc::nav.navbar>

                {{-- Block wrapper: as a flex item, the container's mx-auto would shrink it to its content. --}}
                <main class="flex-1 py-8">
                    <x-fltc::container.page width="full">
                        {{ $slot }}
                    </x-fltc::container.page>
                </main>
            </div>
        </div>

        @persist('fltc-toasts')
            <x-fltc::toast.container position="bottom-right" />
        @endpersist

        @livewireScripts
    </x-fltc::body>
</html>
