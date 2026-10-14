<x-layouts::auth :title="__('Reset password')">
    <div class="mx-auto w-full max-w-md">
        <x-fltc::form.container.auth.simple>
            <div class="flex flex-col gap-6 p-8">
                <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

                <!-- Session Status -->
                <x-auth-session-status class="text-center" :status="session('status')" />

                <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
                    @csrf
                    <!-- Token -->
                    <input type="hidden" name="token" value="{{ request()->route('token') }}">

                    <!-- Email Address -->
                    <x-fltc::form.input.email
                        name="email"
                        :value="old('email', request('email', ''))"
                        :label="__('Email')"
                        icon="envelope"
                        required
                        autocomplete="email"
                    />

                    <!-- Password -->
                    <x-fltc::form.input.password
                        name="password"
                        :label="__('Password')"
                        :placeholder="__('Password')"
                        icon="lock"
                        required
                        autocomplete="new-password"
                    />

                    <!-- Confirm Password -->
                    <x-fltc::form.input.password
                        name="password_confirmation"
                        :label="__('Confirm password')"
                        :placeholder="__('Confirm password')"
                        icon="lock"
                        required
                        autocomplete="new-password"
                    />

                    <x-fltc::button color="primary" width="full" type="submit" data-test="reset-password-button">
                        {{ __('Reset password') }}
                    </x-fltc::button>
                </form>
            </div>
        </x-fltc::form.container.auth.simple>
    </div>
</x-layouts::auth>
