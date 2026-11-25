{{-- Layout slot: Livewire passes top-level slots of a page component to the layout. --}}
<x-slot:breadcrumbs>
    <x-fltc::nav.breadcrumbs.item icon="ph ph-users">{{ __('User management') }}</x-fltc::nav.breadcrumbs.item>
    <x-fltc::nav.breadcrumbs.item :href="route('admin.permissions.index')" icon="ph ph-key">{{ __('Permissions') }}</x-fltc::nav.breadcrumbs.item>
    <x-fltc::nav.breadcrumbs.item :is-active="true">{{ $permission->label ?: $permission->name }}</x-fltc::nav.breadcrumbs.item>
</x-slot:breadcrumbs>

<section class="w-full">
    @include('partials.admin-heading')

    <x-fltc::page-header :level="2" :title="$permission->label ?: $permission->name">
        <x-fltc::code inline class="text-xs" :value="$permission->name" />
    </x-fltc::page-header>

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
