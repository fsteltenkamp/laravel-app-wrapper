<?php

namespace Tests\Feature\Notifications;

use App\Jobs\DeliverNotification;
use App\Models\NotificationChannel;
use App\Models\User;
use App\Notifications\Channels\ChannelDeliveryFailed;
use App\Notifications\Channels\OutboundUrlGuard;
use App\Notifications\EventEmail;
use App\Notifications\NotificationMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class ChannelDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['notifications.allow_private_hosts' => true]);
        Http::preventStrayRequests();
    }

    /**
     * @param  array<string, string>  $config
     */
    private function channel(string $type, array $config = []): NotificationChannel
    {
        return User::factory()->create()->notificationChannels()->create([
            'name' => 'Channel',
            'type' => $type,
            'config' => $config,
        ]);
    }

    private function message(?string $url = 'https://app.test/teams/1'): NotificationMessage
    {
        return new NotificationMessage('team.created', 'Team created', 'Sales was added.', $url, '2026-11-09T12:00:00+00:00');
    }

    private function deliver(NotificationChannel $channel): void
    {
        (new DeliverNotification($channel, $this->message()))->handle();
    }

    public function test_gotify_posts_the_message_with_the_token(): void
    {
        Http::fake(['gotify.test/*' => Http::response(['id' => 1])]);

        $this->deliver($this->channel('gotify', ['url' => 'https://gotify.test/', 'token' => 'abc', 'priority' => '8']));

        Http::assertSent(fn (Request $request) => $request->url() === 'https://gotify.test/message'
            && $request->hasHeader('X-Gotify-Key', 'abc')
            && $request['title'] === 'Team created'
            && $request['message'] === "Sales was added.\n\nhttps://app.test/teams/1"
            && $request['priority'] === 8);
    }

    public function test_discord_posts_an_embed_that_cannot_ping_anyone(): void
    {
        Http::fake(['discord.com/*' => Http::response(null, 204)]);

        $this->deliver($this->channel('discord', ['url' => 'https://discord.com/api/webhooks/1/token']));

        Http::assertSent(fn (Request $request) => $request['allowed_mentions'] === ['parse' => []]
            && $request['embeds'][0]['title'] === 'Team created'
            && $request['embeds'][0]['description'] === "Sales was added.\n\nhttps://app.test/teams/1");
    }

    public function test_webhook_posts_json_without_a_signature_by_default(): void
    {
        Http::fake(['hooks.test/*' => Http::response()]);

        $this->deliver($this->channel('webhook', ['url' => 'https://hooks.test/in', 'secret' => '']));

        Http::assertSent(fn (Request $request) => $request['event'] === 'team.created'
            && $request['message'] === 'Sales was added.'
            && $request['url'] === 'https://app.test/teams/1'
            && $request['occurred_at'] === '2026-11-09T12:00:00+00:00'
            && ! $request->hasHeader('X-Signature'));
    }

    public function test_webhook_signs_the_body_when_a_secret_is_set(): void
    {
        Http::fake(['hooks.test/*' => Http::response()]);

        $this->deliver($this->channel('webhook', ['url' => 'https://hooks.test/in', 'secret' => 's3cret']));

        Http::assertSent(fn (Request $request) => $request->hasHeader(
            'X-Signature',
            'sha256='.hash_hmac('sha256', $request->body(), 's3cret'),
        ));
    }

    public function test_email_goes_to_the_channel_owner(): void
    {
        Notification::fake();
        $channel = $this->channel('email');

        $this->deliver($channel);

        Notification::assertSentTo($channel->user, EventEmail::class, fn (EventEmail $mail) => $mail->message->title === 'Team created');
    }

    public function test_discord_uses_the_channels_message_template(): void
    {
        Http::fake(['discord.com/*' => Http::response(null, 204)]);
        $channel = $this->channel('discord', ['url' => 'https://discord.com/api/webhooks/1/token']);
        $channel->update(['message_template' => "**{event}**: {message}\nSee {url}"]);

        $this->deliver($channel);

        Http::assertSent(fn (Request $request) => $request['embeds'][0]['description'] === "**team.created**: Sales was added.\nSee https://app.test/teams/1"
            && $request['embeds'][0]['title'] === 'Team created');
    }

    public function test_gotify_and_webhook_use_the_channels_message_template(): void
    {
        Http::fake(['*' => Http::response()]);

        foreach ([['gotify', ['url' => 'https://gotify.test', 'token' => 'abc', 'priority' => '5']], ['webhook', ['url' => 'https://hooks.test/in']]] as [$type, $config]) {
            $channel = $this->channel($type, $config);
            $channel->update(['message_template' => 'Alert: {message}']);
            $this->deliver($channel);
        }

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request['message'] === 'Alert: Sales was added.');
        Http::assertNotSent(fn (Request $request) => $request['message'] !== 'Alert: Sales was added.');
    }

    public function test_email_uses_the_channels_message_template_line_by_line(): void
    {
        Notification::fake();
        $channel = $this->channel('email');
        $channel->update(['message_template' => "Alert: {message}\n\nAt {time}"]);

        $this->deliver($channel);

        Notification::assertSentTo($channel->user, EventEmail::class, function (EventEmail $mail) use ($channel) {
            return $mail->toMail($channel->user)->introLines === ['Alert: Sales was added.', 'At 2026-11-09 12:00'];
        });
    }

    public function test_placeholders_inside_values_are_not_replaced_again(): void
    {
        $message = new NotificationMessage('team.created', 'Title', 'Team {url}', 'https://app.test', '2026-11-09T12:00:00+00:00');

        $this->assertSame('Team {url} https://app.test', $message->render('{message} {url}'));
    }

    public function test_unknown_placeholders_stay_as_written(): void
    {
        $this->assertSame('{nope} Sales was added.', $this->message()->render('{nope} {message}'));
    }

    public function test_a_template_that_renders_empty_falls_back_to_the_message(): void
    {
        $this->assertSame('Sales was added.', $this->message(url: null)->render('{url}'));
    }

    public function test_successful_delivery_clears_the_last_error(): void
    {
        Http::fake(['hooks.test/*' => Http::response()]);
        $channel = $this->channel('webhook', ['url' => 'https://hooks.test/in']);
        $channel->markFailed('Old error');

        $this->deliver($channel);

        $channel->refresh();
        $this->assertNull($channel->last_error);
        $this->assertNotNull($channel->last_sent_at);
    }

    public function test_error_responses_fail_without_exposing_the_body(): void
    {
        Http::fake(['hooks.test/*' => Http::response('internal secrets', 500)]);

        $this->expectException(ChannelDeliveryFailed::class);
        $this->expectExceptionMessage('The service answered with HTTP 500.');

        $this->deliver($this->channel('webhook', ['url' => 'https://hooks.test/in']));
    }

    public function test_redirects_count_as_failures(): void
    {
        Http::fake(['hooks.test/*' => Http::response(null, 302, ['Location' => 'http://127.0.0.1/'])]);

        $this->expectException(ChannelDeliveryFailed::class);

        $this->deliver($this->channel('webhook', ['url' => 'https://hooks.test/in']));
    }

    public function test_exhausted_jobs_record_a_safe_error(): void
    {
        $channel = $this->channel('webhook', ['url' => 'https://hooks.test/in']);
        $job = new DeliverNotification($channel, $this->message());

        $job->failed(new ChannelDeliveryFailed('The service answered with HTTP 500.'));
        $this->assertSame('The service answered with HTTP 500.', $channel->refresh()->last_error);

        $job->failed(new RuntimeException('secret connection details'));
        $this->assertSame('Delivery failed.', $channel->refresh()->last_error);
    }

    public function test_disabled_channels_are_not_called_from_queued_jobs(): void
    {
        $channel = $this->channel('webhook', ['url' => 'https://hooks.test/in']);
        $channel->update(['enabled' => false]);

        Http::fake();

        $this->deliver($channel);

        Http::assertNothingSent();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function forbiddenUrls(): array
    {
        return [
            'loopback' => ['http://127.0.0.1/hook'],
            'private network' => ['http://10.0.0.5/hook'],
            'docker network' => ['http://172.18.0.2:6379/'],
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data'],
            'carrier-grade NAT' => ['http://100.64.0.1/'],
            'ipv6 loopback' => ['http://[::1]/'],
            'ipv4-mapped ipv6' => ['http://[::ffff:127.0.0.1]/'],
            'decimal ip' => ['http://2130706433/'],
            'credentials in url' => ['http://user@93.184.216.34/'],
            'other scheme' => ['ftp://93.184.216.34/'],
            'no host' => ['not a url'],
        ];
    }

    #[DataProvider('forbiddenUrls')]
    public function test_private_targets_are_refused_by_default(string $url): void
    {
        config(['notifications.allow_private_hosts' => false]);

        $this->assertFalse(app(OutboundUrlGuard::class)->allows($url));
    }

    public function test_public_addresses_are_allowed(): void
    {
        config(['notifications.allow_private_hosts' => false]);

        $this->assertTrue(app(OutboundUrlGuard::class)->allows('https://93.184.216.34/hook'));
    }

    public function test_private_targets_can_be_allowed(): void
    {
        $this->assertTrue(app(OutboundUrlGuard::class)->allows('http://10.0.0.5/hook'));
    }

    public function test_the_guard_also_runs_at_delivery_time(): void
    {
        $channel = $this->channel('webhook', ['url' => 'http://127.0.0.1/hook']);
        config(['notifications.allow_private_hosts' => false]);
        Http::fake();

        try {
            $this->deliver($channel);
            $this->fail('Delivery should have been refused.');
        } catch (ChannelDeliveryFailed) {
            Http::assertNothingSent();
        }
    }
}
