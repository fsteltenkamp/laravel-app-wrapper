<?php

namespace App\Livewire\Admin\Permissions;

use App\Models\Permission;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit permission')]
class Edit extends Component
{
    public Permission $permission;

    public string $name = '';

    public string $label = '';

    public function mount(Permission $permission): void
    {
        $this->permission = $permission;
        $this->name = $permission->name;
        $this->label = $permission->label;
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Permission::class, 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->permission->id),
            ],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $this->permission->update([
            'name'  => $this->name,
            'label' => $this->label,
        ]);

        Flux::toast(variant: 'success', text: __('Permission updated.'));
    }

    public function render(): View
    {
        return view('livewire.admin.permissions.edit');
    }
}
