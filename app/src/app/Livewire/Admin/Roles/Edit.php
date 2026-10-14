<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Concerns\ShowsToasts;
use App\Models\Permission;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Edit permission group')]
class Edit extends Component
{
    use ShowsToasts;

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

        $this->toast(__('Permission group name updated.'));
    }

    public function savePermissions(): void
    {
        $this->role->syncPermissions($this->selectedPermissions);

        $this->toast(__('Permissions updated.'));
    }

    public function render(): View
    {
        return view('livewire.admin.roles.edit', [
            'allPermissions' => Permission::orderBy('label')->get(),
        ]);
    }
}
