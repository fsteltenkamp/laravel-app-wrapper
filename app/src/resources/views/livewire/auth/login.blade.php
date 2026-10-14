<x-layouts::auth :title="__('Log in')">
    <x-fltc::form.container.auth.two-col-right>
        <x-slot:side>
            <div class="flex h-full flex-col">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-white">
                        <x-fltc::icon name="cube" size="lg" />
                    </span>
                    {{ config('app.name') }}
                </a>

                <div class="mt-auto">
                    @php
                        [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                    @endphp
                    <blockquote class="text-lg font-medium leading-relaxed">
                        &ldquo;{{ trim($message) }}&rdquo;
                    </blockquote>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="text-sm">
                            <span class="block font-semibold">{{ trim($author) }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </x-slot:side>

        <div>
            <x-fltc::heading class="text-2xl font-bold tracking-tight">{{ __('Welcome back') }}</x-fltc::heading>
            <x-fltc::subheading class="mt-1 text-sm">{{ __('Sign in to continue to your dashboard.') }}</x-fltc::subheading>

            <x-auth-session-status class="text-center" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="mt-6">
                @csrf

                <x-passkey-verify />

                <x-fltc::form.input.email
                    id="email"
                    :label="__('Email address')"
                    placeholder="you@example.com"
                    icon="envelope"
                    autocomplete="email"
                    required
                    autofocus
                />

                <x-fltc::form.input.password
                    id="password"
                    :label="__('Password')"
                    placeholder="••••••••"
                    icon="lock"
                    autocomplete="current-password"
                    required
                />

                <div class="mb-4 flex items-center justify-between">
                    <x-fltc::form.checkbox id="remember" :label="__('Remember me')" :bordered="false" class="px-0!" />
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">{{ __('Forgot password?') }}</a>
                    @endif
                </div>

                <x-fltc::button color="primary" width="full" type="submit">{{ __('Sign in') }}</x-fltc::button>
            </form>

            @if (Route::has('auth.register'))
                <p class="mt-6 text-sm opacity-70">
                    {{ __('Don\'t have an account?') }}
                    <a href="{{ route('auth.register') }}" class="font-medium text-primary-600 hover:underline dark:text-primary-400">{{ __('Create one') }}</a>
                </p>
            @endif
        </div>
    </x-fltc::form.container.auth.two-col-right>
</x-layouts::auth>
