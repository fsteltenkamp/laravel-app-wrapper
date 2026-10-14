<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <x-fltc::body>
        <div class="relative flex min-h-screen items-center justify-center px-4 py-12">
            <div class="absolute right-4 top-4"><x-fltc::darkmode.toggle /></div>

            <div class="w-full max-w-4xl">
                {{ $slot }}
            </div>
        </div>

        @persist('fltc-toasts')
            <x-fltc::toast.container position="bottom-right" />
        @endpersist

        @livewireScripts
    </x-fltc::body>
</html>
