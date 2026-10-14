<section class="w-full">
    @include('partials.settings-heading')

    <x-fltc::heading level="2" class="sr-only">{{ __('Appearance settings') }}</x-fltc::heading>

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Choose how the application looks for you')">
        {{-- The picker applies a choice in the browser and announces it; store it on the account. --}}
        <div
            x-data="{
                status: 'saved',
                persist(request) {
                    this.status = 'saving';
                    request.then(() => this.status = 'saved', () => this.status = 'error');
                },
            }"
            x-on:fltc-skin-change.window="persist($wire.saveSkin($event.detail))"
            x-on:fltc-theme-change.window="persist($wire.saveTheme($event.detail.theme, $event.detail.roles))"
        >
            <x-fltc::form.section
                :title="__('Look and colours')"
                :hint="__('Skin and colour theme are saved to your account. Light or dark mode is remembered on this device.')"
            >
                <x-fltc::theme.picker
                    :skins="config('appearance.skins')"
                    :themes="config('appearance.themes')"
                    stacked
                />

                <x-slot:actions>
                    <x-fltc::status x-bind:data-state="status" />
                </x-slot:actions>
            </x-fltc::form.section>
        </div>
    </x-settings.layout>
</section>
