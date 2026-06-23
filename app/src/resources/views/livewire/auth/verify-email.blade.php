<x-layouts::auth :title="__('Email verification')">
    <div class="mx-auto w-full max-w-md">
        <x-fltc::form.container.auth.simple :theme="$mainTheme">
            <div class="flex flex-col gap-6 p-8">
                <p class="text-center text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
                </p>

                @if (session('status') == 'verification-link-sent')
                    <p class="text-center text-sm font-medium text-green-600 dark:text-green-400">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </p>
                @endif

                <div class="flex flex-col items-center justify-between space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                        @csrf
                        <x-fltc::button :theme="$mainTheme" width="full" type="submit">
                            {{ __('Resend verification email') }}
                        </x-fltc::button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-fltc::button variant="outline" theme="zinc" type="submit" class="text-sm" data-test="logout-button">
                            {{ __('Log out') }}
                        </x-fltc::button>
                    </form>
                </div>
            </div>
        </x-fltc::form.container.auth.simple>
    </div>
</x-layouts::auth>
