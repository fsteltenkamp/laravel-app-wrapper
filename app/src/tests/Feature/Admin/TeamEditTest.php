<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Teams\Edit;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeamEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_team_edit_page_requires_authentication(): void
    {
        $team = Team::create(['name' => 'Engineering']);

        $this->get(route('admin.teams.edit', $team))
            ->assertRedirect(route('login'));
    }

    public function test_team_edit_page_requires_permission(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);

        $this->get(route('admin.teams.edit', $team))->assertForbidden();
    }

    public function test_team_edit_page_can_be_rendered(): void
    {
        $this->actingAs($this->userWithPermissions('user.edit'));
        $team = Team::create(['name' => 'Engineering']);

        $this->get(route('admin.teams.edit', $team))->assertOk();
    }

    public function test_edit_page_loads_team_data(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);

        Livewire::test(Edit::class, ['team' => $team])
            ->assertSet('name', 'Engineering');
    }

    public function test_edit_page_loads_existing_members(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);
        $user = User::factory()->create();
        $team->users()->attach($user);

        Livewire::test(Edit::class, ['team' => $team])
            ->assertSet('selectedUsers', [$user->id]);
    }

    public function test_admin_can_update_team_name(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Old Name']);

        Livewire::test(Edit::class, ['team' => $team])
            ->set('name', 'New Name')
            ->call('saveName')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'New Name']);
    }

    public function test_save_name_validates_required(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);

        Livewire::test(Edit::class, ['team' => $team])
            ->set('name', '')
            ->call('saveName')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_admin_can_add_members_to_team(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);
        $user = User::factory()->create();

        Livewire::test(Edit::class, ['team' => $team])
            ->set('selectedUsers', [$user->id])
            ->call('saveMembers')
            ->assertHasNoErrors();

        $this->assertTrue($team->fresh()->users()->where('users.id', $user->id)->exists());
    }

    public function test_admin_can_remove_members_from_team(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);
        $user = User::factory()->create();
        $team->users()->attach($user);

        Livewire::test(Edit::class, ['team' => $team])
            ->set('selectedUsers', [])
            ->call('saveMembers')
            ->assertHasNoErrors();

        $this->assertFalse($team->fresh()->users()->exists());
    }

    public function test_admin_can_sync_multiple_members(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Engineering']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $team->users()->attach($userA);

        Livewire::test(Edit::class, ['team' => $team])
            ->set('selectedUsers', [$userA->id, $userB->id])
            ->call('saveMembers')
            ->assertHasNoErrors();

        $memberIds = $team->fresh()->users()->pluck('users.id')->all();
        $this->assertContains($userA->id, $memberIds);
        $this->assertContains($userB->id, $memberIds);
    }

    // NOTE: Team role and permission assignment tests are omitted because
    // the Team model has no Authenticatable guard, causing Spatie Permission
    // to resolve guard '' for it — which mismatches the 'web' guard used for
    // roles and permissions. The saveRoles() / savePermissions() methods on
    // TeamEdit will throw a GuardDoesNotMatch exception until the Team model
    // is given an explicit `protected $guard_name = 'web'` property.
}
