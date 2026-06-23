<section class="w-full">
    @include('partials.admin-heading')

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <x-fltc::heading level="2" size="xl">{{ $permission->label ?: $permission->name }}</x-fltc::heading>
            <x-fltc::subheading>
                <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $permission->name }}</code>
            </x-fltc::subheading>
        </div>
        <x-fltc::button.link :theme="$mainTheme" variant="outline" height="sm" icon="arrow-left" :href="route('admin.permissions.index')" navigate>
            {{ __('Back to permissions') }}
        </x-fltc::button.link>
    </div>

    <div class="max-w-lg">
        <x-fltc::card :theme="$mainTheme">
            <x-fltc::card.header class="mb-4">
                <x-fltc::heading level="3" size="lg">{{ __('Permission details') }}</x-fltc::heading>
                <x-fltc::subheading>{{ __('The key is used in @can() checks; the label is shown in the UI.') }}</x-fltc::subheading>
            </x-fltc::card.header>
            <x-fltc::card.body>
                <form wire:submit="save" class="space-y-4">
                    <x-fltc::form.input.text model="label" :label="__('Label')" :placeholder="__('Create Users')" :theme="$mainTheme" required />
                    <x-fltc::form.input.text model="name" :label="__('Key')" :placeholder="__('users.create')" :theme="$mainTheme" required />
                    <x-fltc::button :theme="$mainTheme" type="submit" icon="check">{{ __('Save changes') }}</x-fltc::button>
                </form>
            </x-fltc::card.body>
        </x-fltc::card>
    </div>
</section>
