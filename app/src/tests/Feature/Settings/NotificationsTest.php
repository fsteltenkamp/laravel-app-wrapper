<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Notifications;
use App\Models\NotificationChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config(['notifications.allow_private_hosts' => true]);
        Http::preventStrayRequests();
    }

    /**
     * @param  array<string, string>  $config
     */
    private function channel(User $user, string $type = 'webhook', array $config = ['url' => 'https://hooks.test/in', 'secret' => 'stored-secret']): NotificationChannel
    {
        return $user->notificationChannels()->create(['name' => 'My channel', 'type' => $type, 'config' => $config]);
    }

    public function test_page_requires_authentication(): void
    {
        $this->get(route('notifications.edit'))->assertRedirect(route('login'));
    }

    public function test_page_is_displayed(): void
    {
        $user = $this->userWithPermissions('user.edit');
        $this->channel($user);

        $this->actingAs($user)
            ->get(route('notifications.edit'))
            ->assertOk()
            ->assertSee('My channel')
            ->assertSee('Team created');
    }

    public function test_only_events_the_user_may_see_are_listed(): void
    {
        $user = User::factory()->create();
        $this->channel($user);

        $this->actingAs($user)
            ->get(route('notifications.edit'))
            ->assertOk()
            ->assertSee('Team members changed')
            ->assertDontSee('User created');
    }

    public function test_every_channel_type_shows_its_fields(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (array_keys(NotificationChannel::types()) as $type) {
            Livewire::test(Notifications::class)
                ->call('createChannel')
                ->set('channelType', $type)
                ->assertOk();
        }
    }

    public function test_gotify_channel_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('createChannel')
            ->set('channelName', 'Phone')
            ->set('channelType', 'gotify')
            ->set('channelConfig.url', 'https://gotify.test')
            ->set('channelConfig.token', 'abc')
            ->call('saveChannel')
            ->assertHasNoErrors();

        $channel = $user->notificationChannels()->sole();

        $this->assertSame('Phone', $channel->name);
        $this->assertSame('gotify', $channel->type);
        $this->assertSame(['url' => 'https://gotify.test', 'token' => 'abc', 'priority' => '5'], $channel->config);
    }

    public function test_config_is_encrypted_at_rest(): void
    {
        $channel = $this->channel(User::factory()->create());

        $this->assertStringNotContainsString('stored-secret', $channel->getRawOriginal('config'));
    }

    public function test_email_channel_needs_no_settings(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('createChannel')
            ->set('channelName', 'Inbox')
            ->set('channelType', 'email')
            ->call('saveChannel')
            ->assertHasNoErrors();

        $this->assertSame('email', $user->notificationChannels()->sole()->type);
    }

    public function test_only_real_discord_webhooks_are_accepted(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Notifications::class)
            ->call('createChannel')
            ->set('channelName', 'Chat')
            ->set('channelType', 'discord')
            ->set('channelConfig.url', 'https://evil.test/api/webhooks/1/token')
            ->call('saveChannel')
            ->assertHasErrors('channelConfig.url');
    }

    public function test_private_urls_are_rejected_when_saving(): void
    {
        config(['notifications.allow_private_hosts' => false]);
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('createChannel')
            ->set('channelName', 'Internal')
            ->set('channelType', 'webhook')
            ->set('channelConfig.url', 'http://127.0.0.1:6379/')
            ->call('saveChannel')
            ->assertHasErrors('channelConfig.url');

        $this->assertSame(0, $user->notificationChannels()->count());
    }

    public function test_unknown_types_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Notifications::class)
            ->set('channelName', 'Nope')
            ->set('channelType', 'carrier-pigeon')
            ->call('saveChannel')
            ->assertHasErrors('channelType');
    }

    public function test_secrets_are_not_sent_to_the_browser_when_editing(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('editChannel', $channel->id)
            ->assertSet('channelConfig.url', 'https://hooks.test/in')
            ->assertSet('channelConfig.secret', '')
            ->assertDontSee('stored-secret');
    }

    public function test_empty_secret_keeps_the_stored_value_on_edit(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('editChannel', $channel->id)
            ->set('channelName', 'Renamed')
            ->set('channelConfig.url', 'https://hooks.test/new')
            ->call('saveChannel')
            ->assertHasNoErrors();

        $channel->refresh();
        $this->assertSame('Renamed', $channel->name);
        $this->assertSame(['url' => 'https://hooks.test/new', 'secret' => 'stored-secret'], $channel->config);
    }

    public function test_message_template_can_be_set_and_cleared(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('editChannel', $channel->id)
            ->set('channelTemplate', 'Alert: {message}')
            ->call('saveChannel')
            ->assertHasNoErrors();

        $this->assertSame('Alert: {message}', $channel->refresh()->message_template);

        Livewire::test(Notifications::class)
            ->call('editChannel', $channel->id)
            ->assertSet('channelTemplate', 'Alert: {message}')
            ->set('channelTemplate', '')
            ->call('saveChannel')
            ->assertHasNoErrors();

        $this->assertNull($channel->refresh()->message_template);
    }

    public function test_message_template_length_is_limited(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Notifications::class)
            ->call('createChannel')
            ->set('channelName', 'Inbox')
            ->set('channelType', 'email')
            ->set('channelTemplate', str_repeat('a', 2001))
            ->call('saveChannel')
            ->assertHasErrors('channelTemplate');
    }

    public function test_the_form_shows_the_default_template_of_the_type(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Notifications::class)
            ->call('createChannel')
            ->set('channelType', 'discord')
            ->assertSeeHtml('placeholder="{message}')
            ->assertSee('{title}, {message}, {url}, {event}, {time}');
    }

    public function test_the_type_of_an_existing_channel_cannot_change(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('editChannel', $channel->id)
            ->set('channelType', 'email')
            ->set('channelConfig.url', 'https://hooks.test/in')
            ->call('saveChannel')
            ->assertHasNoErrors();

        $this->assertSame('webhook', $channel->refresh()->type);
    }

    public function test_channels_of_other_users_cannot_be_touched(): void
    {
        $channel = $this->channel(User::factory()->create());
        $this->actingAs(User::factory()->create());

        foreach (['editChannel', 'sendTest', 'confirmDelete'] as $action) {
            Livewire::test(Notifications::class)->call($action, $channel->id)->assertNotFound();
        }
    }

    public function test_channel_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $channel->subscriptions()->create(['event' => 'team.members_changed']);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('confirmDelete', $channel->id)
            ->call('deleteChannel');

        $this->assertDatabaseMissing('notification_channels', ['id' => $channel->id]);
        $this->assertDatabaseMissing('notification_subscriptions', ['notification_channel_id' => $channel->id]);
    }

    public function test_subscriptions_can_be_saved_and_removed(): void
    {
        $user = $this->userWithPermissions('user.edit');
        $channel = $this->channel($user);
        $this->actingAs($user);

        $component = Livewire::test(Notifications::class)
            ->set('subscriptions', ["team.created|{$channel->id}", "user.deleted|{$channel->id}"])
            ->call('saveSubscriptions');

        $this->assertEqualsCanonicalizing(['team.created', 'user.deleted'], $channel->subscriptions()->pluck('event')->all());

        $component->set('subscriptions', ["team.created|{$channel->id}"])->call('saveSubscriptions');

        $this->assertSame(['team.created'], $channel->subscriptions()->pluck('event')->all());
    }

    public function test_existing_subscriptions_are_loaded(): void
    {
        $user = $this->userWithPermissions('user.edit');
        $channel = $this->channel($user);
        $channel->subscriptions()->create(['event' => 'team.created']);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->assertSet('subscriptions', ["team.created|{$channel->id}"]);
    }

    public function test_events_without_permission_cannot_be_subscribed_to(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->set('subscriptions', ["team.created|{$channel->id}", "team.members_changed|{$channel->id}"])
            ->call('saveSubscriptions');

        $this->assertSame(['team.members_changed'], $channel->subscriptions()->pluck('event')->all());
    }

    public function test_unknown_events_and_foreign_channels_are_ignored(): void
    {
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $foreign = $this->channel(User::factory()->create());
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->set('subscriptions', ["not.registered|{$channel->id}", "team.members_changed|{$foreign->id}"])
            ->call('saveSubscriptions');

        $this->assertSame(0, $channel->subscriptions()->count());
        $this->assertSame(0, $foreign->subscriptions()->count());
    }

    public function test_test_notification_reports_success(): void
    {
        Http::fake(['hooks.test/*' => Http::response()]);
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('sendTest', $channel->id)
            ->assertDispatched('fltc:toast', title: 'Test notification sent.');

        $this->assertNotNull($channel->refresh()->last_sent_at);
    }

    public function test_test_notification_reports_failure(): void
    {
        Http::fake(['hooks.test/*' => Http::response('', 500)]);
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        Livewire::test(Notifications::class)
            ->call('sendTest', $channel->id)
            ->assertDispatched('fltc:toast', title: 'Test notification failed.', message: 'The service answered with HTTP 500.');

        $this->assertSame('The service answered with HTTP 500.', $channel->refresh()->last_error);
    }

    public function test_test_notifications_are_rate_limited(): void
    {
        Http::fake(['hooks.test/*' => Http::response()]);
        $user = User::factory()->create();
        $channel = $this->channel($user);
        $this->actingAs($user);

        $component = Livewire::test(Notifications::class);

        for ($i = 0; $i < 6; $i++) {
            $component->call('sendTest', $channel->id);
        }

        Http::assertSentCount(5);
    }
}
