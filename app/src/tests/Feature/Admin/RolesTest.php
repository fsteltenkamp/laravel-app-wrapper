<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Roles\Edit;
use App\Livewire\Admin\Roles\Index;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_roles_index_requires_authentication(): void
    {
        $this->get(route('admin.roles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_roles_index_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_roles_index_can_be_rendered(): void
    {
        $this->actingAs($this->userWithPermissions('user.edit'));

        $this->get(route('admin.roles.index'))->assertOk();
    }

    public function test_admin_can_create_role(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', 'super-admin')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('fltc:toast', title: __('Permission group created.'), color: 'success');

        $this->assertDatabaseHas('roles', ['name' => 'super-admin', 'guard_name' => 'web']);
    }

    public function test_create_role_validates_required_name(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_admin_can_create_role_and_assign_permissions(): void
    {
        $this->actingAs(User::factory()->create());
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->set('name', 'editor')
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::findByName('editor', 'web');

        Livewire::test(Edit::class, ['role' => $role])
            ->set('selectedPermissions', ['edit-posts'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('edit-posts'));
    }

    public function test_admin_can_edit_role_name(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'writer', 'guard_name' => 'web']);

        Livewire::test(Edit::class, ['role' => $role])
            ->assertSet('name', 'writer')
            ->set('name', 'author')
            ->call('saveName')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'author']);
        $this->assertDatabaseMissing('roles', ['id' => $role->id, 'name' => 'writer']);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete-posts', 'label' => 'Delete Posts', 'guard_name' => 'web']);

        Livewire::test(Edit::class, ['role' => $role])
            ->set('selectedPermissions', ['delete-posts'])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('delete-posts'));
    }

    public function test_editing_role_loads_existing_permissions(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $role->givePermissionTo('edit-posts');

        Livewire::test(Edit::class, ['role' => $role])
            ->assertSet('selectedPermissions', ['edit-posts']);
    }

    public function test_admin_can_remove_permissions_from_role(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $role->givePermissionTo('edit-posts');

        Livewire::test(Edit::class, ['role' => $role])
            ->set('selectedPermissions', [])
            ->call('savePermissions')
            ->assertHasNoErrors();

        $this->assertFalse($role->fresh()->hasPermissionTo('edit-posts'));
    }

    public function test_admin_can_delete_role(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'temp-role', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $role->id)
            ->assertSet('confirmingDeleteId', $role->id)
            ->call('deleteRole');

        $this->assertNull(Role::find($role->id));
    }

    public function test_search_filters_roles_by_name(): void
    {
        $this->actingAs(User::factory()->create());
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->set('search', 'super-admin')
            ->assertSee('super-admin')
            ->assertDontSee('editor');
    }

    public function test_create_resets_form_state(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', 'leftover')
            ->call('create')
            ->assertSet('name', '')
            ->assertDispatched('open-modal');
    }
}
