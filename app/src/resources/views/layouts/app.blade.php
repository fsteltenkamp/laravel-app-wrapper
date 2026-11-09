<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <x-fltc::body>
        <div class="flex min-h-screen">
            {{--
                From lg up the sidebar is a pinned column; users can hide it and open it over the
                content from the top bar instead (partials/sidebar-state.blade.php, app.css).
                Below lg it is fltc's drawer. The brand row matches the slim top bar (h-12).
            --}}
            <x-fltc::nav.sidebar
                class="app-sidebar lg:sticky lg:top-0 [&>div:first-of-type]:h-12"
                x-bind:data-open="open"
                x-on:click.outside="$event.target.closest('.app-sidebar-toggle') || hide()"
            >
                <x-slot:brand>
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-2 font-semibold tracking-tight">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-600 text-white">
                            <x-fltc::icon name="cube" />
                        </span>
                        <span class="truncate">{{ config('app.name') }}</span>
                    </a>

                    <button type="button" data-sidebar-when="pinned" x-on:click="appSidebar.collapse()" title="{{ __('Hide sidebar') }}" aria-label="{{ __('Hide sidebar') }}"
                        class="ml-auto hidden cursor-pointer rounded-md p-1.5 text-base-500 hover:bg-base-100 hover:text-base-900 dark:text-base-400 dark:hover:bg-base-800 dark:hover:text-base-100">
                        <x-fltc::icon name="sidebar-simple" />
                    </button>
                    <button type="button" data-sidebar-when="collapsed" x-on:click="appSidebar.pin(); hide()" title="{{ __('Pin sidebar') }}" aria-label="{{ __('Pin sidebar') }}"
                        class="ml-auto hidden cursor-pointer rounded-md p-1.5 text-base-500 hover:bg-base-100 hover:text-base-900 dark:text-base-400 dark:hover:bg-base-800 dark:hover:text-base-100">
                        <x-fltc::icon name="push-pin" />
                    </button>
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
                {{-- Slim top bar: fltc's navbar row is fixed at h-16, lowered to h-12 here. --}}
                <x-fltc::nav.navbar stickyTop class="[&>div>div]:h-12">
                    <x-slot:left>
                        <x-fltc::nav.navbar.toggle class="app-sidebar-toggle" />
                        @isset($breadcrumbs)
                            {{-- One line; scrolls sideways on narrow screens instead of wrapping. --}}
                            <x-fltc::nav.breadcrumbs container-class="min-w-0" class="flex min-w-0 items-center [&_li]:shrink-0 [&_ol]:flex-nowrap [&_ol]:overflow-x-auto [&_ol]:[scrollbar-width:none]">
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
