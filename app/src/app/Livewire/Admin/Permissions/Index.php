<?php

namespace App\Livewire\Admin\Permissions;

use App\Livewire\Concerns\ShowsToasts;
use App\Models\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Permissions')]
class Index extends Component
{
    use ShowsToasts;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $name = '';

    public string $label = '';

    public ?int $confirmingDeleteId = null;

    public function create(): void
    {
        $this->reset(['name', 'label']);
        $this->dispatch('open-modal', name: 'permission-form');
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Permission::class, 'name')->where('guard_name', 'web')],
            'label' => ['required', 'string', 'max:255'],
        ]);

        Permission::create([
            'name' => $this->name,
            'label' => $this->label,
            'guard_name' => 'web',
        ]);

        $this->toast(__('Permission created.'));

        $this->dispatch('close-modal', name: 'permission-form');
        $this->reset(['name', 'label']);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $permissionId): void
    {
        $this->confirmingDeleteId = $permissionId;
        $this->dispatch('open-modal', name: 'confirm-permission-deletion');
    }

    public function deletePermission(): void
    {
        Permission::find($this->confirmingDeleteId)?->delete();

        $this->toast(__('Permission deleted.'));

        $this->confirmingDeleteId = null;
        $this->dispatch('close-modal', name: 'confirm-permission-deletion');
    }

    public function render(): View
    {
        $permissions = Permission::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('label', 'like', "%{$this->search}%"))
            ->withCount('roles', 'users')
            ->orderBy('label')
            ->paginate(15);

        return view('livewire.admin.permissions.index', [
            'permissions' => $permissions,
        ]);
    }
}
