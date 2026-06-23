<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative flex min-h-screen items-center justify-center px-4 py-12">
            <div class="absolute right-4 top-4"><x-fltc::darkmode.toggle /></div>

            <div class="w-full max-w-4xl">
                {{$slot}}
            </div>
        </div>

        @livewireScripts
    </body>
</html>

