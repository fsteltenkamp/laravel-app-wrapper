<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <x-fltc::body>
        <div class="flex min-h-screen">
            {{--
                From lg up the sidebar is a pinned column; users can hide it and open it over the
                content from the top bar instead, kept per browser under `app-sidebar`.
                Below lg it is fltc's drawer. The brand row matches the slim top bar.
            --}}
            <x-fltc::nav.sidebar pinnable persist="app-sidebar" header-height="sm" class="lg:sticky lg:top-0">
                <x-slot:brand>
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-2 font-semibold tracking-tight">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-600 text-white">
                            <x-fltc::icon name="cube" />
                        </span>
                        <span class="truncate">{{ config('app.name') }}</span>
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
                <x-fltc::nav.navbar stickyTop height="sm">
                    <x-slot:left>
                        <x-fltc::nav.navbar.toggle pinnable />
                        @isset($breadcrumbs)
                            {{-- One line; scrolls sideways on narrow screens instead of wrapping. --}}
                            <x-fltc::nav.breadcrumbs scroll container-class="min-w-0" class="flex min-w-0 items-center">
                                {{ $breadcrumbs }}
                            </x-fltc::nav.breadcrumbs>
                        @elseif (filled($title ?? null))
                            <x-fltc::nav.navbar.item class="text-sm font-medium">{{ $title }}</x-fltc::nav.navbar.item>
                        @endisset
                    </x-slot:left>
                    <x-slot:right>
                        <div class="flex items-center">
                            <x-fltc::darkmode.toggle variant="toggle" size="sm" />
                        </div>
                    </x-slot:right>
                </x-fltc::nav.navbar>

                {{-- Block wrapper: as a flex item, the container's mx-auto would shrink it to its content. --}}
                <main class="flex-1 py-6">
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
