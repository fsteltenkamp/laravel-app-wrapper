<x-layouts::auth :title="__('Confirm password')">
    <div class="mx-auto w-full max-w-md">
        <x-fltc::form.container.auth.simple>
            <div class="flex flex-col gap-6 p-8">
                <x-auth-header
                    :title="__('Confirm password')"
                    :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
                />

                <x-auth-session-status class="text-center" :status="session('status')" />

                <x-passkey-verify
                    options-route="passkey.confirm-options"
                    submit-route="passkey.confirm"
                    :label="__('Confirm with passkey')"
                    :loading-label="__('Confirming...')"
                    :separator="__('Or confirm with password')"
                />

                <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <x-fltc::form.input.password
                        name="password"
                        :label="__('Password')"
                        :placeholder="__('Password')"
                        icon="lock"
                        required
                        autocomplete="current-password"
                    />

                    <x-fltc::button color="primary" width="full" type="submit" data-test="confirm-password-button">
                        {{ __('Confirm') }}
                    </x-fltc::button>
                </form>
            </div>
        </x-fltc::form.container.auth.simple>
    </div>
</x-layouts::auth>
