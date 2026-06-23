<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Permissions\Index;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionsTest extends TestCase
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

    public function test_permissions_index_requires_authentication(): void
    {
        $this->get(route('admin.permissions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_permissions_index_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.permissions.index'))->assertOk();
    }

    public function test_admin_can_create_permission(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', 'edit-posts')
            ->set('label', 'Edit Posts')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', [
            'name' => 'edit-posts',
            'label' => 'Edit Posts',
            'guard_name' => 'web',
        ]);
    }

    public function test_create_permission_validates_required_name(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', '')
            ->set('label', 'Edit Posts')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_create_permission_validates_required_label(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('name', 'edit-posts')
            ->set('label', '')
            ->call('save')
            ->assertHasErrors(['label' => 'required']);
    }

    public function test_create_permission_rejects_duplicate_name(): void
    {
        $this->actingAs(User::factory()->create());
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->set('name', 'edit-posts')
            ->set('label', 'Different Label')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_admin_can_edit_permission(): void
    {
        $this->actingAs(User::factory()->create());
        $permission = Permission::create(['name' => 'view-reports', 'label' => 'View Reports', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $permission->id)
            ->assertSet('editingId', $permission->id)
            ->assertSet('name', 'view-reports')
            ->assertSet('label', 'View Reports')
            ->set('label', 'View All Reports')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'label' => 'View All Reports',
        ]);
    }

    public function test_edit_permission_allows_same_name(): void
    {
        $this->actingAs(User::factory()->create());
        $permission = Permission::create(['name' => 'delete-posts', 'label' => 'Delete Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $permission->id)
            ->set('name', 'delete-posts')
            ->set('label', 'Updated Label')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'label' => 'Updated Label']);
    }

    public function test_edit_permission_rejects_name_taken_by_another_permission(): void
    {
        $this->actingAs(User::factory()->create());
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);
        $other = Permission::create(['name' => 'delete-posts', 'label' => 'Delete Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $other->id)
            ->set('name', 'edit-posts')
            ->set('label', 'Delete Posts')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_admin_can_delete_permission(): void
    {
        $this->actingAs(User::factory()->create());
        $permission = Permission::create(['name' => 'temp-perm', 'label' => 'Temp Permission', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $permission->id)
            ->assertSet('confirmingDeleteId', $permission->id)
            ->call('deletePermission');

        $this->assertNull(Permission::find($permission->id));
    }

    public function test_search_filters_permissions_by_name(): void
    {
        $this->actingAs(User::factory()->create());
        Permission::create(['name' => 'edit-posts', 'label' => 'Edit Blog Posts', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete-users', 'label' => 'Remove Users', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->set('search', 'edit-posts')
            ->assertSee('edit-posts')
            ->assertDontSee('delete-users');
    }

    public function test_search_filters_permissions_by_label(): void
    {
        $this->actingAs(User::factory()->create());
        Permission::create(['name' => 'perm-one', 'label' => 'Manage Content', 'guard_name' => 'web']);
        Permission::create(['name' => 'perm-two', 'label' => 'View Analytics', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->set('search', 'Manage Content')
            ->assertSee('Manage Content')
            ->assertDontSee('View Analytics');
    }

    public function test_create_resets_form_state(): void
    {
        $this->actingAs(User::factory()->create());
        $permission = Permission::create(['name' => 'edit-posts', 'label' => 'Edit Posts', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('edit', $permission->id)
            ->assertSet('editingId', $permission->id)
            ->call('create')
            ->assertSet('editingId', null)
            ->assertSet('name', '')
            ->assertSet('label', '');
    }
}
