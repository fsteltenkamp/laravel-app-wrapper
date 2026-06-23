<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User management')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $newName = '';

    public string $newEmail = '';

    public string $newPassword = '';

    public ?int $confirmingDeleteId = null;

    public function create(): void
    {
        $this->reset(['newName', 'newEmail', 'newPassword']);

        $this->dispatch('open-modal', name: 'user-form');
    }

    public function save(): void
    {
        $this->authorize('user.create');

        $this->validate([
            'newName'     => ['required', 'string', 'max:255'],
            'newEmail'    => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'newPassword' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name'     => $this->newName,
            'email'    => $this->newEmail,
            'password' => Hash::make($this->newPassword),
        ]);

        Flux::toast(variant: 'success', text: __('User created.'));

        $this->dispatch('close-modal', name: 'user-form');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $userId): void
    {
        $this->confirmingDeleteId = $userId;

        $this->dispatch('open-modal', name: 'confirm-user-deletion');
    }

    public function deleteUser(): void
    {
        $this->authorize('user.delete');

        $user = User::find($this->confirmingDeleteId);

        if ($user && $user->isNot(Auth::user())) {
            $user->delete();

            Flux::toast(variant: 'success', text: __('User deleted.'));
        }

        $this->confirmingDeleteId = null;

        $this->dispatch('close-modal', name: 'confirm-user-deletion');
    }

    public function render(): View
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->withCount('teams')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.users.index', [
            'users' => $users,
        ]);
    }
}
