<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UsersTest extends TestCase
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

    public function test_users_index_requires_authentication(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_users_index_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_users_index_can_be_rendered(): void
    {
        $this->actingAs($this->userWithPermissions('user.create'));

        $this->get(route('admin.users.index'))->assertOk();
    }

    public function test_admin_can_create_user(): void
    {
        $this->actingAs($this->userWithPermissions('user.create'));

        Livewire::test(Index::class)
            ->set('newName', 'Jane Doe')
            ->set('newEmail', 'jane@example.com')
            ->set('newPassword', 'secret123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_cannot_create_user_without_permission(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('newName', 'Unauthorized')
            ->set('newEmail', 'unauth@example.com')
            ->set('newPassword', 'password123')
            ->call('save');

        $this->assertDatabaseMissing('users', ['email' => 'unauth@example.com']);
    }

    public function test_create_user_validates_required_name(): void
    {
        $this->actingAs($this->userWithPermissions('user.create'));

        Livewire::test(Index::class)
            ->set('newName', '')
            ->set('newEmail', 'jane@example.com')
            ->set('newPassword', 'secret123')
            ->call('save')
            ->assertHasErrors(['newName' => 'required']);
    }

    public function test_create_user_validates_required_email(): void
    {
        $this->actingAs($this->userWithPermissions('user.create'));

        Livewire::test(Index::class)
            ->set('newName', 'Jane Doe')
            ->set('newEmail', '')
            ->set('newPassword', 'secret123')
            ->call('save')
            ->assertHasErrors(['newEmail' => 'required']);
    }

    public function test_create_user_validates_email_format(): void
    {
        $this->actingAs($this->userWithPermissions('user.create'));

        Livewire::test(Index::class)
            ->set('newName', 'Jane Doe')
            ->set('newEmail', 'not-an-email')
            ->set('newPassword', 'secret123')
            ->call('save')
            ->assertHasErrors(['newEmail']);
    }

    public function test_create_user_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $this->actingAs($this->userWithPermissions('user.create'));

        Livewire::test(Index::class)
            ->set('newName', 'Jane Doe')
            ->set('newEmail', 'taken@example.com')
            ->set('newPassword', 'secret123')
            ->call('save')
            ->assertHasErrors(['newEmail']);
    }

    public function test_create_user_validates_minimum_password_length(): void
    {
        $this->actingAs($this->userWithPermissions('user.create'));

        Livewire::test(Index::class)
            ->set('newName', 'Jane Doe')
            ->set('newEmail', 'jane@example.com')
            ->set('newPassword', 'short')
            ->call('save')
            ->assertHasErrors(['newPassword']);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = $this->userWithPermissions('user.delete');
        $target = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('confirmDelete', $target->id)
            ->assertSet('confirmingDeleteId', $target->id)
            ->call('deleteUser');

        $this->assertNull(User::find($target->id));
    }

    public function test_cannot_delete_user_without_permission(): void
    {
        $target = User::factory()->create();
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->call('confirmDelete', $target->id)
            ->call('deleteUser');

        $this->assertNotNull(User::find($target->id));
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->userWithPermissions('user.delete');
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('confirmDelete', $admin->id)
            ->call('deleteUser');

        $this->assertNotNull(User::find($admin->id));
    }

    public function test_search_filters_users_by_name(): void
    {
        $admin = User::factory()->create(['name' => 'ZZZZZ Admin', 'email' => 'admin@x.com']);
        $admin->givePermissionTo('user.create');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@x.com']);
        User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@x.com']);

        Livewire::test(Index::class)
            ->set('search', 'Alice Smith')
            ->assertSee('Alice Smith')
            ->assertDontSee('Bob Jones');
    }

    public function test_search_filters_users_by_email(): void
    {
        $admin = User::factory()->create(['name' => 'Admin', 'email' => 'zzz@x.com']);
        $admin->givePermissionTo('user.create');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'User One', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'User Two', 'email' => 'bob@example.com']);

        Livewire::test(Index::class)
            ->set('search', 'alice@example')
            ->assertSee('alice@example.com')
            ->assertDontSee('bob@example.com');
    }

    public function test_clearing_search_shows_all_users(): void
    {
        $admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@x.com']);
        $admin->givePermissionTo('user.create');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@x.com']);
        User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@x.com']);

        Livewire::test(Index::class)
            ->set('search', 'Alice')
            ->set('search', '')
            ->assertSee('Alice Smith')
            ->assertSee('Bob Jones');
    }
}
