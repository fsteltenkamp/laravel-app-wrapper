<section class="w-full">
    @include('partials.settings-heading')

    <x-fltc::heading level="2" class="sr-only">{{ __('Security settings') }}</x-fltc::heading>

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <x-fltc::form.input.password
                model="current_password"
                :label="__('Current password')"
                required
                autocomplete="current-password"
            />
            <x-fltc::form.input.password
                model="password"
                :label="__('New password')"
                required
                autocomplete="new-password"
            />
            <x-fltc::form.input.password
                model="password_confirmation"
                :label="__('Confirm password')"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <x-fltc::button color="primary" type="submit" data-test="update-password-button">{{ __('Save') }}</x-fltc::button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12">
                <x-fltc::heading level="2">{{ __('Two-factor authentication') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Manage your two-factor authentication settings') }}</x-fltc::subheading>

                <div class="flex flex-col w-full mx-auto space-y-6 text-sm mt-6" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <p class="text-sm text-base-600 dark:text-base-300">
                                {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                            </p>

                            <div class="flex justify-start">
                                <x-fltc::button color="danger" wire:click="disable">
                                    {{ __('Disable 2FA') }}
                                </x-fltc::button>
                            </div>

                            <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />
                        </div>
                    @else
                        <div class="space-y-4">
                            <p class="text-sm text-base-500 dark:text-base-400">
                                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                            </p>

                            <x-fltc::button color="primary" wire:click="enable">
                                {{ __('Enable 2FA') }}
                            </x-fltc::button>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($canManageTwoFactor)
            <x-fltc::modal
                wire:model="showModal"
                @close="$wire.closeModal()"
                max-width="md"
            >
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-4">
                        <div class="p-0.5 w-auto rounded-full border border-base-100 dark:border-base-600 bg-white dark:bg-base-800 shadow-sm">
                            <div class="p-2.5 rounded-full border border-base-200 dark:border-base-600 overflow-hidden bg-base-100 dark:bg-base-200 relative">
                                <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-base-200 dark:divide-base-300 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-base-200 dark:divide-base-300 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <x-fltc::icon name="qr-code" size="xl" class="relative z-20 text-base-900" />
                            </div>
                        </div>

                        <div class="space-y-2 text-center">
                            <x-fltc::heading level="2" size="lg">{{ $this->modalConfig['title'] }}</x-fltc::heading>
                            <x-fltc::subheading>{{ $this->modalConfig['description'] }}</x-fltc::subheading>
                        </div>
                    </div>

                    @if ($showVerificationStep)
                        <div class="space-y-6">
                            <div
                                class="flex flex-col items-center space-y-3 justify-center"
                                x-data
                                x-init="$nextTick(() => $el.querySelector('input')?.focus())"
                            >
                                <x-fltc::form.otp
                                    name="code"
                                    wire:model.live="code"
                                    length="6"
                                />
                            </div>

                            <div class="flex items-center space-x-3">
                                <x-fltc::button
                                    variant="outline"
                                    class="flex-1"
                                    wire:click="resetVerification"
                                >
                                    {{ __('Back') }}
                                </x-fltc::button>

                                <x-fltc::button
                                    color="primary"
                                    class="flex-1"
                                    wire:click="confirmTwoFactor"
                                    x-bind:disabled="$wire.code.length < 6"
                                >
                                    {{ __('Confirm') }}
                                </x-fltc::button>
                            </div>
                        </div>
                    @else
                        @error('setupData')
                            <x-fltc::alert color="danger">
                                <x-fltc::icon name="x-circle" after="2" />{{ $message }}
                            </x-fltc::alert>
                        @enderror

                        <div class="flex justify-center">
                            <div class="relative w-64 overflow-hidden border rounded-lg border-base-200 dark:border-base-700 aspect-square">
                                @empty($qrCodeSvg)
                                    <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-base-700 animate-pulse">
                                        <x-fltc::spinner size="xl" />
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-full p-4">
                                        <div class="bg-white p-3 rounded">
                                            {!! $qrCodeSvg !!}
                                        </div>
                                    </div>
                                @endempty
                            </div>
                        </div>

                        <div>
                            <x-fltc::button
                                :disabled="$errors->has('setupData')"
                                color="primary"
                                width="full"
                                wire:click="showVerificationIfNecessary"
                            >
                                {{ $this->modalConfig['buttonText'] }}
                            </x-fltc::button>
                        </div>

                        <div class="space-y-4">
                            <div class="relative flex items-center justify-center w-full">
                                <div class="absolute inset-0 w-full h-px top-1/2 bg-base-200 dark:bg-base-600"></div>
                                <span class="relative px-2 text-sm bg-white dark:bg-base-900 text-base-600 dark:text-base-400">
                                    {{ __('or, enter the code manually') }}
                                </span>
                            </div>

                            <div
                                class="flex items-center space-x-2"
                                x-data="{
                                    copied: false,
                                    async copy() {
                                        try {
                                            await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                            this.copied = true;
                                            setTimeout(() => this.copied = false, 1500);
                                        } catch (e) {
                                            console.warn('Could not copy to clipboard');
                                        }
                                    }
                                }"
                            >
                                <div class="flex items-stretch w-full border rounded-xl dark:border-base-700">
                                    @empty($manualSetupKey)
                                        <div class="flex items-center justify-center w-full p-3 bg-base-100 dark:bg-base-700">
                                            <x-fltc::spinner size="sm" />
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $manualSetupKey }}"
                                            class="w-full p-3 bg-transparent outline-none text-base-900 dark:text-base-100"
                                        />

                                        <button
                                            type="button"
                                            @click="copy()"
                                            class="px-3 transition-colors border-l cursor-pointer border-base-200 dark:border-base-600"
                                        >
                                            <x-fltc::icon name="copy" variant="regular" x-show="!copied" />
                                            <x-fltc::icon name="check" x-show="copied" x-cloak class="text-success-500" />
                                        </button>
                                    @endempty
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-fltc::modal>
        @endif

        @if ($canManagePasskeys)
            <section class="mt-12">
                <x-fltc::heading level="2">{{ __('Passkeys') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Manage your passkeys for passwordless sign-in') }}</x-fltc::subheading>

                <div class="mt-6 flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
                    <div class="border rounded-lg border-base-200 dark:border-base-700 overflow-hidden">
                        @forelse ($passkeys as $passkey)
                            <div class="flex items-center justify-between p-4 {{ ! $loop->last ? 'border-b border-base-200 dark:border-base-700' : '' }}">
                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-base-100 dark:bg-base-800">
                                        <x-fltc::icon name="key" size="lg" class="text-base-500 dark:text-base-400" />
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2.5">
                                            <p class="font-medium tracking-tight">{{ $passkey['name'] }}</p>
                                            @if ($passkey['authenticator'])
                                                <x-fltc::badge>{{ $passkey['authenticator'] }}</x-fltc::badge>
                                            @endif
                                        </div>
                                        <p class="text-base-500 dark:text-base-400 text-xs">
                                            {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}
                                            @if ($passkey['last_used_at_diff'])
                                                <span class="opacity-50 mx-1">/</span>
                                                {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <x-fltc::button
                                    variant="outline"
                                    color="danger"
                                    height="sm"
                                    icon="trash"
                                    icon-variant="regular"
                                    wire:click="confirmDelete({{ $passkey['id'] }})"
                                />
                            </div>
                        @empty
                            <x-fltc::empty icon="key" :title="__('No passkeys yet')">
                                {{ __('Add a passkey to sign in without a password') }}
                            </x-fltc::empty>
                        @endforelse
                    </div>

                    <x-passkey-registration />
                </div>
            </section>
        @endif
    </x-settings.layout>

    <x-fltc::modal
        wire:model="showDeleteModal"
        @close="$wire.closeDeleteModal()"
        max-width="md"
    >
        <div class="space-y-6">
            <div class="space-y-2">
                <x-fltc::heading level="2" size="lg">{{ __('Remove passkey') }}</x-fltc::heading>
                <x-fltc::subheading>
                    {{ __('Are you sure you want to remove the passkey ":name"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName]) }}
                </x-fltc::subheading>
            </div>

            <div class="flex gap-3 justify-end">
                <x-fltc::button variant="outline" wire:click="closeDeleteModal">
                    {{ __('Cancel') }}
                </x-fltc::button>
                <x-fltc::button color="danger" wire:click="deletePasskey">
                    {{ __('Remove passkey') }}
                </x-fltc::button>
            </div>
        </div>
    </x-fltc::modal>
</section>
