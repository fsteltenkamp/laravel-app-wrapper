<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Permission;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Edit permission group')]
class Edit extends Component
{
    public Role $role;

    public string $name = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions()->pluck('name')->all();
    }

    public function saveName(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->role->update(['name' => $this->name]);

        Flux::toast(variant: 'success', text: __('Permission group name updated.'));
    }

    public function savePermissions(): void
    {
        $this->role->syncPermissions($this->selectedPermissions);

        Flux::toast(variant: 'success', text: __('Permissions updated.'));
    }

    public function render(): View
    {
        return view('livewire.admin.roles.edit', [
            'allPermissions' => Permission::orderBy('label')->get(),
        ]);
    }
}
