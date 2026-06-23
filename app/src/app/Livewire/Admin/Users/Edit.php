<?php

namespace App\Livewire\Admin\Users;

use App\Models\Team;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Title('Edit user')]
class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    /**
     * The team the role/permission assignments are scoped to.
     * Null represents the global (team-independent) context.
     */
    public ?int $teamContext = null;

    /** @var array<int, string> */
    public array $selectedRoles = [];

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    /** @var array<int, int> */
    public array $selectedTeams = [];

    public function mount(User $user): void
    {
        $this->authorize('user.edit');

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedTeams = $user->teams()->pluck('teams.id')->all();

        $this->loadAssignments();
    }

    /**
     * Tell spatie/permission which team the next role/permission
     * read or write should be scoped to.
     */
    protected function applyTeamContext(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->teamContext);
    }

    public function loadAssignments(): void
    {
        $this->applyTeamContext();

        // Drop any cached relations so they are re-read within the new team context.
        $this->user->unsetRelation('roles')->unsetRelation('permissions');

        $this->selectedRoles = $this->user->roles()->pluck('name')->all();
        $this->selectedPermissions = $this->user->getDirectPermissions()->pluck('name')->all();
    }

    public function updatedTeamContext(): void
    {
        $this->loadAssignments();
    }

    public function saveProfile(): void
    {
        $this->authorize('user.edit');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
        ]);

        $this->user->update($validated);

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function saveRoles(): void
    {
        $this->authorize('user.edit');

        $this->applyTeamContext();

        $this->user->syncRoles($this->selectedRoles);

        Flux::toast(variant: 'success', text: __('Permission groups updated.'));
    }

    public function savePermissions(): void
    {
        $this->authorize('user.edit');

        $this->applyTeamContext();

        $this->user->syncPermissions($this->selectedPermissions);

        Flux::toast(variant: 'success', text: __('Direct permissions updated.'));
    }

    public function saveTeams(): void
    {
        $this->authorize('user.edit');

        $this->user->teams()->sync($this->selectedTeams);

        // The current team context may no longer be a team the user belongs to.
        if ($this->teamContext !== null && ! in_array($this->teamContext, $this->selectedTeams, true)) {
            $this->teamContext = null;
            $this->loadAssignments();
        }

        Flux::toast(variant: 'success', text: __('Team membership updated.'));
    }

    public function render(): View
    {
        $this->applyTeamContext();

        $permissionSources = [];
        foreach ($this->user->roles as $role) {
            foreach ($role->permissions as $perm) {
                $permissionSources[$perm->name][] = $role->name;
            }
        }

        return view('livewire.admin.users.edit', [
            'roles' => Role::orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(),
            'teams' => Team::orderBy('name')->get(),
            'memberTeams' => Team::whereIn('id', $this->selectedTeams)->orderBy('name')->get(),
            'permissionSources' => $permissionSources,
        ]);
    }
}
