<section class="w-full">
    @include('partials.settings-heading')

    <x-fltc::heading level="2" class="sr-only">{{ __('Security settings') }}</x-fltc::heading>

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <x-fltc::form.input.password
                model="current_password"
                :label="__('Current password')"
                :theme="$mainTheme"
                required
                autocomplete="current-password"
            />
            <x-fltc::form.input.password
                model="password"
                :label="__('New password')"
                :theme="$mainTheme"
                required
                autocomplete="new-password"
            />
            <x-fltc::form.input.password
                model="password_confirmation"
                :label="__('Confirm password')"
                :theme="$mainTheme"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <x-fltc::button :theme="$mainTheme" type="submit" data-test="update-password-button">{{ __('Save') }}</x-fltc::button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12">
                <x-fltc::heading level="2">{{ __('Two-factor authentication') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('Manage your two-factor authentication settings') }}</x-fltc::subheading>

                <div class="flex flex-col w-full mx-auto space-y-6 text-sm mt-6" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                            </p>

                            <div class="flex justify-start">
                                <x-fltc::button theme="red" wire:click="disable">
                                    {{ __('Disable 2FA') }}
                                </x-fltc::button>
                            </div>

                            <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />
                        </div>
                    @else
                        <div class="space-y-4">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                            </p>

                            <x-fltc::button :theme="$mainTheme" wire:click="enable">
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
                        <div class="p-0.5 w-auto rounded-full border border-stone-100 dark:border-stone-600 bg-white dark:bg-stone-800 shadow-sm">
                            <div class="p-2.5 rounded-full border border-stone-200 dark:border-stone-600 overflow-hidden bg-stone-100 dark:bg-stone-200 relative">
                                <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <x-fltc::icon name="qr-code" size="xl" class="relative z-20 text-stone-900" />
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
                                    :theme="$mainTheme"
                                />
                            </div>

                            <div class="flex items-center space-x-3">
                                <x-fltc::button
                                    variant="outline"
                                    theme="zinc"
                                    class="flex-1"
                                    wire:click="resetVerification"
                                >
                                    {{ __('Back') }}
                                </x-fltc::button>

                                <x-fltc::button
                                    :theme="$mainTheme"
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
                            <x-fltc::alert theme="red">
                                <x-fltc::icon name="x-circle" after="2" />{{ $message }}
                            </x-fltc::alert>
                        @enderror

                        <div class="flex justify-center">
                            <div class="relative w-64 overflow-hidden border rounded-lg border-stone-200 dark:border-stone-700 aspect-square">
                                @empty($qrCodeSvg)
                                    <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-stone-700 animate-pulse">
                                        <x-fltc::icon name="spinner" class="animate-spin" size="xl" />
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
                                :theme="$mainTheme"
                                width="full"
                                wire:click="showVerificationIfNecessary"
                            >
                                {{ $this->modalConfig['buttonText'] }}
                            </x-fltc::button>
                        </div>

                        <div class="space-y-4">
                            <div class="relative flex items-center justify-center w-full">
                                <div class="absolute inset-0 w-full h-px top-1/2 bg-stone-200 dark:bg-stone-600"></div>
                                <span class="relative px-2 text-sm bg-white dark:bg-zinc-900 text-stone-600 dark:text-stone-400">
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
                                <div class="flex items-stretch w-full border rounded-xl dark:border-stone-700">
                                    @empty($manualSetupKey)
                                        <div class="flex items-center justify-center w-full p-3 bg-stone-100 dark:bg-stone-700">
                                            <x-fltc::icon name="spinner" class="animate-spin" />
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $manualSetupKey }}"
                                            class="w-full p-3 bg-transparent outline-none text-stone-900 dark:text-stone-100"
                                        />

                                        <button
                                            type="button"
                                            @click="copy()"
                                            class="px-3 transition-colors border-l cursor-pointer border-stone-200 dark:border-stone-600"
                                        >
                                            <x-fltc::icon name="copy" variant="regular" x-show="!copied" />
                                            <x-fltc::icon name="check" x-show="copied" x-cloak class="text-green-500" />
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
                    <div class="border rounded-lg border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        @forelse ($passkeys as $passkey)
                            <div class="flex items-center justify-between p-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}">
                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                        <x-fltc::icon name="key" size="lg" class="text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2.5">
                                            <p class="font-medium tracking-tight">{{ $passkey['name'] }}</p>
                                            @if ($passkey['authenticator'])
                                                <x-fltc::badge :theme="$mainTheme">{{ $passkey['authenticator'] }}</x-fltc::badge>
                                            @endif
                                        </div>
                                        <p class="text-zinc-500 dark:text-zinc-400 text-xs">
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
                                    theme="red"
                                    height="sm"
                                    icon="trash"
                                    icon-variant="regular"
                                    wire:click="confirmDelete({{ $passkey['id'] }})"
                                />
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                                    <x-fltc::icon name="key" size="2xl" class="text-zinc-400 dark:text-zinc-500" />
                                </div>
                                <p class="font-medium">{{ __('No passkeys yet') }}</p>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add a passkey to sign in without a password') }}</p>
                            </div>
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
                <x-fltc::button variant="outline" theme="zinc" wire:click="closeDeleteModal">
                    {{ __('Cancel') }}
                </x-fltc::button>
                <x-fltc::button theme="red" wire:click="deletePasskey">
                    {{ __('Remove passkey') }}
                </x-fltc::button>
            </div>
        </div>
    </x-fltc::modal>
</section>
