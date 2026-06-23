<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <x-fltc::heading level="2">{{ __('Delete account') }}</x-fltc::heading>
        <x-fltc::subheading>{{ __('Delete your account and all of its resources') }}</x-fltc::subheading>
    </div>

    <x-fltc::button
        theme="red"
        x-data
        x-on:click="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('Delete account') }}
    </x-fltc::button>

    <x-fltc::modal name="confirm-user-deletion" :open="$errors->isNotEmpty()">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div class="space-y-2">
                <x-fltc::heading level="2" size="lg">{{ __('Are you sure you want to delete your account?') }}</x-fltc::heading>

                <x-fltc::subheading>
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </x-fltc::subheading>
            </div>

            <x-fltc::form.input.password model="password" :label="__('Password')" :theme="$mainTheme" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <x-fltc::button variant="outline" theme="zinc" type="button" x-on:click="close()">{{ __('Cancel') }}</x-fltc::button>

                <x-fltc::button theme="red" type="submit">{{ __('Delete account') }}</x-fltc::button>
            </div>
        </form>
    </x-fltc::modal>
</section>
