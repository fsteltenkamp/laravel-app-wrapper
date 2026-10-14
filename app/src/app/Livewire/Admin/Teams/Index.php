<?php

namespace App\Livewire\Admin\Teams;

use App\Livewire\Concerns\ShowsToasts;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Teams')]
class Index extends Component
{
    use ShowsToasts;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $newName = '';

    public ?int $confirmingDeleteId = null;

    public function create(): void
    {
        $this->reset(['newName']);
        $this->dispatch('open-modal', name: 'team-form');
    }

    public function save(): void
    {
        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
        ]);

        Team::create(['name' => $this->newName]);

        $this->toast(__('Team created.'));
        $this->dispatch('close-modal', name: 'team-form');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $teamId): void
    {
        $this->confirmingDeleteId = $teamId;
        $this->dispatch('open-modal', name: 'confirm-team-deletion');
    }

    public function deleteTeam(): void
    {
        $team = Team::find($this->confirmingDeleteId);
        $team?->delete();

        $this->toast(__('Team deleted.'));

        $this->confirmingDeleteId = null;
        $this->dispatch('close-modal', name: 'confirm-team-deletion');
    }

    public function render(): View
    {
        $teams = Team::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('users', 'roles', 'permissions')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.teams.index', [
            'teams' => $teams,
        ]);
    }
}
