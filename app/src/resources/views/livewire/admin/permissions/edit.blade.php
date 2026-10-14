<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <x-fltc::heading level="2" size="xl">{{ $permission->label ?: $permission->name }}</x-fltc::heading>
            <x-fltc::subheading>
                <code class="rounded bg-base-100 px-1.5 py-0.5 text-xs text-base-700 dark:bg-base-800 dark:text-base-300">{{ $permission->name }}</code>
            </x-fltc::subheading>
        </div>
        <x-fltc::button.link variant="outline" height="sm" icon="arrow-left" :href="route('admin.permissions.index')" navigate>
            {{ __('Back to permissions') }}
        </x-fltc::button.link>
    </div>

    <div>
        <x-fltc::card>
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Permission details') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('The key is used in @can() checks; the label is shown in the UI.') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="save" class="space-y-4">
                    <x-fltc::form.input.text model="label" :label="__('Label')" :placeholder="__('Create Users')" required />
                    <x-fltc::form.input.text model="name" :label="__('Key')" :placeholder="__('users.create')" required />
                    <x-fltc::button color="primary" type="submit" icon="check">{{ __('Save changes') }}</x-fltc::button>
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>
</section>
