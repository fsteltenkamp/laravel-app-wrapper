<x-layouts::app :title="__('Dashboard')">
    <div class="mb-6">
        <x-fltc::heading size="2xl" level="1">{{ __('Dashboard') }}</x-fltc::heading>
        <x-fltc::subheading size="lg">{{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}</x-fltc::subheading>
    </div>

    @canany(['user.create', 'user.edit', 'user.delete'])
        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <x-fltc::counter color="primary" icon="ph ph-users" :title="__('Users')" :count="(string) \App\Models\User::count()" :link="route('admin.users.index')" navigate />
            <x-fltc::counter color="secondary" icon="ph ph-buildings" :title="__('Teams')" :count="(string) \App\Models\Team::count()" :link="route('admin.teams.index')" navigate />
            <x-fltc::counter color="accent" icon="ph ph-users-three" :title="__('Permission groups')" :count="(string) \Spatie\Permission\Models\Role::count()" :link="route('admin.roles.index')" navigate />
        </div>
    @endcanany

    <x-fltc::empty icon="squares-four" :title="__('Nothing here yet')" size="lg" bordered>
        {{ __('This is where your application starts. Replace this view with your first feature.') }}
    </x-fltc::empty>
</x-layouts::app>
