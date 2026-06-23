<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Roles\Index;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
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
        View::share([
            'mainTheme' => 'neutral',
            'secondaryTheme' => 'slate',
            'accentTheme' => 'sky',
            'infoTheme' => 'blue',
            'successTheme' => 'green',
            'warningTheme' => 'yellow',
            'errorTheme' => 'red',
            'darkmode' => false,
        ]);
    }

    public function test_roles_index_requires_authentication(): void
    {
        $this->get(route('admin.roles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_roles_index_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.roles.index'))->assertOk();
    }

    public function test_admin_can_create_role(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', 'super-admin')
            ->call('save')
            ->assertHasNoErrors();

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

    public function test_admin_can_create_role_with_permissions(): void
    {
        $this->actingAs(User::factory()->create());
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->set('name', 'editor')
            ->set('selectedPermissions', ['edit-posts'])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::findByName('editor', 'web');
        $this->assertTrue($role->hasPermissionTo('edit-posts'));
    }

    public function test_admin_can_edit_role_name(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'writer', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $role->id)
            ->assertSet('editingId', $role->id)
            ->assertSet('name', 'writer')
            ->set('name', 'author')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'author']);
        $this->assertDatabaseMissing('roles', ['id' => $role->id, 'name' => 'writer']);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete-posts', 'label' => 'Delete Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $role->id)
            ->set('selectedPermissions', ['delete-posts'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('delete-posts'));
    }

    public function test_editing_role_loads_existing_permissions(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $role->givePermissionTo('edit-posts');

        Livewire::test(Index::class)
            ->call('edit', $role->id)
            ->assertSet('selectedPermissions', ['edit-posts']);
    }

    public function test_admin_can_remove_permissions_from_role(): void
    {
        $this->actingAs(User::factory()->create());
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $role->givePermissionTo('edit-posts');

        Livewire::test(Index::class)
            ->call('edit', $role->id)
            ->set('selectedPermissions', [])
            ->call('save')
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
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $role->id)
            ->assertSet('editingId', $role->id)
            ->call('create')
            ->assertSet('editingId', null)
            ->assertSet('name', '')
            ->assertSet('selectedPermissions', []);
    }
}
