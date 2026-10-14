<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users\Edit;
use App\Models\Permission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_user_edit_page_requires_authentication(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.users.edit', $user))
            ->assertRedirect(route('login'));
    }

    public function test_user_edit_requires_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.edit', $user))
            ->assertForbidden();
    }

    public function test_user_edit_page_can_be_rendered(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk();
    }

    public function test_edit_page_loads_user_profile_data(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'testuser@example.com']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->assertSet('name', 'Test User')
            ->assertSet('email', 'testuser@example.com');
    }

    public function test_admin_can_update_user_profile(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('name', 'New Name')
            ->set('email', 'new@example.com')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_save_profile_validates_required_name(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('name', '')
            ->call('saveProfile')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_save_profile_validates_required_email(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('email', '')
            ->call('saveProfile')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_save_profile_allows_unchanged_email(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create(['email' => 'user@example.com']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('name', $user->name)
            ->set('email', 'user@example.com')
            ->call('saveProfile')
            ->assertHasNoErrors();
    }

    public function test_save_profile_rejects_email_taken_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('name', $user->name)
            ->set('email', 'taken@example.com')
            ->call('saveProfile')
            ->assertHasErrors(['email']);
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedRoles', ['editor'])
            ->call('saveRoles')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasRole('editor'));
    }

    public function test_admin_can_remove_role_from_user(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user->assignRole('editor');
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedRoles', [])
            ->call('saveRoles')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->hasRole('editor'));
    }

    public function test_admin_can_assign_multiple_roles_to_user(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Role::create(['name' => 'viewer', 'guard_name' => 'web']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedRoles', ['editor', 'viewer'])
            ->call('saveRoles')
            ->assertHasNoErrors();

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasRole('editor'));
        $this->assertTrue($fresh->hasRole('viewer'));
    }

    public function test_admin_can_assign_direct_permission_to_user(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedPermissions', ['edit-posts'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasDirectPermission('edit-posts'));
    }

    public function test_admin_can_remove_direct_permission_from_user(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $user->givePermissionTo('edit-posts');
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedPermissions', [])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->hasDirectPermission('edit-posts'));
    }

    public function test_admin_can_assign_team_to_user(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $team = Team::create(['name' => 'Engineering']);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedTeams', [$team->id])
            ->call('saveTeams')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->teams()->where('teams.id', $team->id)->exists());
    }

    public function test_admin_can_remove_user_from_team(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $team = Team::create(['name' => 'Engineering']);
        $user->teams()->attach($team);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('selectedTeams', [])
            ->call('saveTeams')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->teams()->exists());
    }

    public function test_team_context_resets_when_user_removed_from_active_team(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $team = Team::create(['name' => 'Engineering']);
        $user->teams()->attach($team);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('teamContext', $team->id)
            ->set('selectedTeams', [])
            ->call('saveTeams')
            ->assertSet('teamContext', null);
    }

    public function test_team_context_unchanged_when_user_kept_in_active_team(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $team = Team::create(['name' => 'Engineering']);
        $user->teams()->attach($team);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->set('teamContext', $team->id)
            ->set('selectedTeams', [$team->id])
            ->call('saveTeams')
            ->assertSet('teamContext', $team->id);
    }

    public function test_edit_page_loads_existing_team_memberships(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        $team = Team::create(['name' => 'Engineering']);
        $user->teams()->attach($team);
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->assertSet('selectedTeams', [$team->id]);
    }

    public function test_edit_page_loads_existing_roles(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create();
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user->assignRole('editor');
        $this->actingAs($admin);

        Livewire::test(Edit::class, ['user' => $user])
            ->assertSet('selectedRoles', ['editor']);
    }
}
