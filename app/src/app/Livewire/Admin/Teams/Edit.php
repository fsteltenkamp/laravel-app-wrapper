<?php

namespace App\Livewire\Admin\Teams;

use App\Models\Permission;
use App\Models\Team;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Edit team')]
class Edit extends Component
{
    public Team $team;

    public string $name = '';

    /** @var array<int, int> */
    public array $selectedUsers = [];

    /** @var array<int, string> */
    public array $selectedRoles = [];

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->name = $team->name;
        $this->selectedUsers = $team->users()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $this->selectedRoles = $team->roles()->pluck('name')->all();
        $this->selectedPermissions = $team->getDirectPermissions()->pluck('name')->all();
    }

    public function saveName(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->team->update(['name' => $this->name]);

        Flux::toast(variant: 'success', text: __('Team name updated.'));
    }

    public function saveMembers(): void
    {
        $this->team->users()->sync($this->selectedUsers);

        Flux::toast(variant: 'success', text: __('Team members updated.'));
    }

    public function saveRoles(): void
    {
        $this->team->syncRoles($this->selectedRoles);

        Flux::toast(variant: 'success', text: __('Team permission groups updated.'));
    }

    public function savePermissions(): void
    {
        $this->team->syncPermissions($this->selectedPermissions);

        Flux::toast(variant: 'success', text: __('Team direct permissions updated.'));
    }

    public function render(): View
    {
        $permissionSources = [];
        foreach ($this->team->roles as $role) {
            foreach ($role->permissions as $perm) {
                $permissionSources[$perm->name][] = $role->name;
            }
        }

        return view('livewire.admin.teams.edit', [
            'allUsers' => User::orderBy('name')->get(),
            'allRoles' => Role::orderBy('name')->get(),
            'allPermissions' => Permission::orderBy('name')->get(),
            'permissionSources' => $permissionSources,
        ]);
    }
}
