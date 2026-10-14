<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Concerns\ShowsToasts;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Permission groups')]
class Index extends Component
{
    use ShowsToasts;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $name = '';

    public ?int $confirmingDeleteId = null;

    public function create(): void
    {
        $this->reset(['name']);
        $this->dispatch('open-modal', name: 'role-form');
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
        $role->syncPermissions([]);

        $this->toast(__('Permission group created.'));

        $this->dispatch('close-modal', name: 'role-form');
        $this->reset(['name']);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $roleId): void
    {
        $this->confirmingDeleteId = $roleId;
        $this->dispatch('open-modal', name: 'confirm-role-deletion');
    }

    public function deleteRole(): void
    {
        $role = Role::find($this->confirmingDeleteId);
        $role?->delete();

        $this->toast(__('Permission group deleted.'));

        $this->confirmingDeleteId = null;
        $this->dispatch('close-modal', name: 'confirm-role-deletion');
    }

    public function render(): View
    {
        $roles = Role::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('permissions', 'users')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
