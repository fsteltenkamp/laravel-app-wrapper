<?php

namespace Tests\Feature;

use App\Jobs\DeliverNotification;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Notifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeamPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function memberOf(Team $team): User
    {
        $user = User::factory()->create();
        $team->users()->attach($user);

        return $user;
    }

    public function test_members_get_the_direct_permissions_of_their_team(): void
    {
        $team = Team::create(['name' => 'Support']);
        $team->givePermissionTo('user.edit');

        $this->assertTrue($this->memberOf($team)->can('user.edit'));
    }

    public function test_members_get_the_permissions_of_their_teams_roles(): void
    {
        $role = Role::create(['name' => 'Helpdesk']);
        $role->givePermissionTo('user.delete');
        $team = Team::create(['name' => 'Support']);
        $team->assignRole($role);

        $this->assertTrue($this->memberOf($team)->can('user.delete'));
    }

    public function test_only_the_granted_permission_is_inherited(): void
    {
        $team = Team::create(['name' => 'Support']);
        $team->givePermissionTo('user.edit');

        $this->assertFalse($this->memberOf($team)->can('user.delete'));
    }

    public function test_non_members_get_nothing(): void
    {
        $team = Team::create(['name' => 'Support']);
        $team->givePermissionTo('user.edit');

        $this->assertFalse(User::factory()->create()->can('user.edit'));
    }

    public function test_leaving_the_team_removes_the_permission(): void
    {
        $team = Team::create(['name' => 'Support']);
        $team->givePermissionTo('user.edit');
        $user = $this->memberOf($team);

        $team->users()->detach($user);

        $this->assertFalse($user->fresh()->can('user.edit'));
    }

    public function test_own_permissions_still_work(): void
    {
        $this->assertTrue($this->userWithPermissions('user.edit')->can('user.edit'));
    }

    public function test_abilities_that_are_not_permissions_are_unaffected(): void
    {
        $team = Team::create(['name' => 'Support']);
        $user = $this->memberOf($team);

        $this->assertTrue($user->can('view', $team));
        $this->assertFalse($user->can('no-such-ability'));
    }

    public function test_unknown_permissions_still_throw_for_direct_checks(): void
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->memberOf(Team::create(['name' => 'Support']))->hasPermissionTo('no.such.permission');
    }

    public function test_team_permissions_satisfy_gates_and_routes(): void
    {
        $team = Team::create(['name' => 'Support']);
        $team->givePermissionTo('user.edit');
        $user = $this->memberOf($team);

        $this->assertTrue($user->can('manage-users'));

        $this->actingAs($user)->get(route('admin.users.index'))->assertOk();
    }

    public function test_team_permissions_unlock_notification_events(): void
    {
        Queue::fake();
        $team = Team::create(['name' => 'Support']);
        $team->givePermissionTo('user.edit');
        $channel = $this->memberOf($team)->notificationChannels()->create(['name' => 'Mail', 'type' => 'email', 'config' => []]);
        $channel->subscriptions()->create(['event' => 'team.created']);

        app(Notifier::class)->send('team.created', 'Sales was added.');

        Queue::assertPushed(DeliverNotification::class, 1);
    }
}
