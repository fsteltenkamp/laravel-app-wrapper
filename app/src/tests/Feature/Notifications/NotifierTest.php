<?php

namespace Tests\Feature\Notifications;

use App\Jobs\DeliverNotification;
use App\Livewire\Admin\Teams\Edit;
use App\Livewire\Admin\Teams\Index;
use App\Models\NotificationChannel;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Notifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Queue::fake();
    }

    private function subscribedChannel(User $user, string $event, bool $enabled = true): NotificationChannel
    {
        $channel = $user->notificationChannels()->create([
            'name' => 'Mail',
            'type' => 'email',
            'config' => [],
            'enabled' => $enabled,
        ]);
        $channel->subscriptions()->create(['event' => $event]);

        return $channel;
    }

    public function test_subscribed_channels_get_the_event(): void
    {
        $channel = $this->subscribedChannel($this->userWithPermissions('user.edit'), 'team.created');

        app(Notifier::class)->send('team.created', 'Sales was added.', 'https://app.test/teams/1');

        Queue::assertPushed(DeliverNotification::class, fn (DeliverNotification $job) => $job->channel->is($channel)
            && $job->message->event === 'team.created'
            && $job->message->title === 'Team created'
            && $job->message->body === 'Sales was added.'
            && $job->message->url === 'https://app.test/teams/1');
    }

    public function test_channels_subscribed_to_other_events_are_skipped(): void
    {
        $this->subscribedChannel($this->userWithPermissions('user.edit'), 'team.deleted');

        app(Notifier::class)->send('team.created', 'Sales was added.');

        Queue::assertNothingPushed();
    }

    public function test_disabled_channels_are_skipped(): void
    {
        $this->subscribedChannel($this->userWithPermissions('user.edit'), 'team.created', enabled: false);

        app(Notifier::class)->send('team.created', 'Sales was added.');

        Queue::assertNothingPushed();
    }

    public function test_subscription_stops_delivering_when_the_permission_is_lost(): void
    {
        $user = $this->userWithPermissions('user.edit');
        $this->subscribedChannel($user, 'team.created');

        $user->revokePermissionTo('user.edit');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        app(Notifier::class)->send('team.created', 'Sales was added.');

        Queue::assertNothingPushed();
    }

    public function test_element_events_only_reach_users_who_can_see_the_element(): void
    {
        $team = Team::create(['name' => 'Sales']);
        $otherTeam = Team::create(['name' => 'Support']);

        $member = User::factory()->create();
        $team->users()->attach($member);
        $memberChannel = $this->subscribedChannel($member, 'team.members_changed');

        $outsider = User::factory()->create();
        $otherTeam->users()->attach($outsider);
        $outsiderChannel = $this->subscribedChannel($outsider, 'team.members_changed');

        $managerChannel = $this->subscribedChannel($this->userWithPermissions('user.edit'), 'team.members_changed');

        app(Notifier::class)->send('team.members_changed', 'Members changed.', null, $team);

        Queue::assertPushed(DeliverNotification::class, 2);
        Queue::assertPushed(DeliverNotification::class, fn (DeliverNotification $job) => $job->channel->is($memberChannel));
        Queue::assertPushed(DeliverNotification::class, fn (DeliverNotification $job) => $job->channel->is($managerChannel));
        Queue::assertNotPushed(DeliverNotification::class, fn (DeliverNotification $job) => $job->channel->is($outsiderChannel));
    }

    public function test_element_events_sent_without_an_element_reach_nobody(): void
    {
        $this->subscribedChannel($this->userWithPermissions('user.edit'), 'team.members_changed');

        app(Notifier::class)->send('team.members_changed', 'Members changed.');

        Queue::assertNothingPushed();
    }

    public function test_unregistered_events_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(Notifier::class)->send('not.registered', 'Nope.');
    }

    public function test_creating_a_team_sends_the_event(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $this->subscribedChannel($admin, 'team.created');
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->set('newName', 'Sales')
            ->call('save')
            ->assertHasNoErrors();

        Queue::assertPushed(DeliverNotification::class, fn (DeliverNotification $job) => $job->message->body === 'Sales was added.');
    }

    public function test_changing_members_sends_the_event_only_when_something_changed(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $this->subscribedChannel($admin, 'team.members_changed');
        $this->actingAs($admin);
        $team = Team::create(['name' => 'Sales']);

        $component = Livewire::test(Edit::class, ['team' => $team])
            ->set('selectedUsers', [$admin->id])
            ->call('saveMembers');

        Queue::assertPushed(DeliverNotification::class, 1);

        $component->call('saveMembers');

        Queue::assertPushed(DeliverNotification::class, 1);
    }
}
