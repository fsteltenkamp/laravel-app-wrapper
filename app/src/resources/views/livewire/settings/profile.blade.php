<section class="w-full">
    @include('partials.settings-heading')

    <x-fltc::heading level="2" class="sr-only">{{ __('Profile settings') }}</x-fltc::heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <x-fltc::form.input.text model="name" :label="__('Name')" required autofocus autocomplete="name" />

            <div>
                <x-fltc::form.input.email model="email" :label="__('Email')" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <p class="mt-4 text-sm text-base-600 dark:text-base-300">
                        {{ __('Your email address is unverified.') }}

                        <button
                            type="button"
                            wire:click.prevent="resendVerificationNotification"
                            class="cursor-pointer font-medium text-primary-600 hover:underline dark:text-primary-400"
                        >
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <x-fltc::button color="primary" type="submit">{{ __('Save') }}</x-fltc::button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
