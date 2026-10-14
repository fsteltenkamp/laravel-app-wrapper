<x-layouts::auth :title="__('Forgot password')">
    <div class="mx-auto w-full max-w-md">
        <x-fltc::form.container.auth.simple>
            <div class="flex flex-col gap-6 p-8">
                <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

                <!-- Session Status -->
                <x-auth-session-status class="text-center" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
                    @csrf

                    <!-- Email Address -->
                    <x-fltc::form.input.email
                        name="email"
                        :label="__('Email address')"
                        placeholder="email@example.com"
                        icon="envelope"
                        required
                        autofocus
                    />

                    <x-fltc::button color="primary" width="full" type="submit" data-test="email-password-reset-link-button">
                        {{ __('Email password reset link') }}
                    </x-fltc::button>
                </form>

                <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-base-400">
                    <span>{{ __('Or, return to') }}</span>
                    <a href="{{ route('login') }}" wire:navigate class="font-medium text-primary-600 hover:underline dark:text-primary-400">{{ __('log in') }}</a>
                </div>
            </div>
        </x-fltc::form.container.auth.simple>
    </div>
</x-layouts::auth>
