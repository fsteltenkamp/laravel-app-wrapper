<?php

use App\Notifications\Channels\DiscordChannel;
use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\GotifyChannel;
use App\Notifications\Channels\WebhookChannel;

return [

    /*
    |--------------------------------------------------------------------------
    | Private network targets
    |--------------------------------------------------------------------------
    |
    | Users enter the URLs that the server then calls, so by default a URL may
    | only point to a public address. Otherwise anyone with an account could
    | reach the database, Redis or other internal services. Enable this only
    | if every user is trusted, e.g. to use a Gotify server on the LAN.
    |
    */

    'allow_private_hosts' => (bool) env('NOTIFICATIONS_ALLOW_PRIVATE_HOSTS', false),

    /*
    |--------------------------------------------------------------------------
    | Channel types
    |--------------------------------------------------------------------------
    |
    | The services a user can add as a channel, each implementing ChannelDriver.
    |
    */

    'channels' => [
        'gotify' => GotifyChannel::class,
        'discord' => DiscordChannel::class,
        'email' => EmailChannel::class,
        'webhook' => WebhookChannel::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscribable events
    |--------------------------------------------------------------------------
    |
    | Every event users can subscribe to. Send one from anywhere with
    | app(Notifier::class)->send('team.created', $message, $url, $element).
    |
    | ability          Gate ability or permission needed to see and receive the
    |                  event at all. Omit it to offer the event to every user.
    | element_ability  Checked against the element passed to send(), so users
    |                  only hear about elements they can see, e.g. the "view"
    |                  method of a policy. The event must then be sent with an
    |                  element, otherwise nobody receives it.
    |
    */

    'events' => [
        'user.created' => [
            'label' => 'User created',
            'description' => 'A new user account was added.',
            'group' => 'Users',
            'ability' => 'manage-users',
        ],
        'user.deleted' => [
            'label' => 'User deleted',
            'description' => 'A user account was removed.',
            'group' => 'Users',
            'ability' => 'manage-users',
        ],
        'team.created' => [
            'label' => 'Team created',
            'description' => 'A new team was added.',
            'group' => 'Teams',
            'ability' => 'manage-users',
        ],
        'team.deleted' => [
            'label' => 'Team deleted',
            'description' => 'A team was removed.',
            'group' => 'Teams',
            'ability' => 'manage-users',
        ],
        'team.members_changed' => [
            'label' => 'Team members changed',
            'description' => 'Someone joined or left a team you can see.',
            'group' => 'Teams',
            'element_ability' => 'view',
        ],
    ],

];
