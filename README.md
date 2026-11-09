# Laravel App Wrapper

This is the wrapper i use to run laravel based apps on my infrastructure.  
The App container is built automatically, so a lot of customization is possible.  
I am using Frankenphp as a base for the app image, but that can easily be changed to apache or anything else.  
The dev container is built on the same FrankenPHP image, so PHP and its extensions match production.  
  
The setup process is pretty simple:  

1. Put your laravel application into `app/src`
2. Update all names, urls, details etc. in the following files:
    - `app/Dockerfile`
    - `app/deployment-scripts/shared/*`
    - `docker-compose.yml`
    - `test.sh`
3. If needed, put SQL init files into `docker/init/mysql`
4. Update the Base-Path in `test.sh` to make sure the final containers are uploaded to your container registry.
5. Use `test.sh` to check if the project runs correctly
6. Deploy on your production hardware 👌 (I use Dokploy, but it should work with many more similar projects or just use `docker compose up -d`)

## Reverse proxy

The app container serves plain HTTP on port 80 and expects a reverse proxy (Traefik,
nginx, …) in front of it to terminate TLS. `TRUSTED_PROXIES` says whose `X-Forwarded-*`
headers Laravel believes, so client IPs, `https` URLs and secure cookies come out right:

| Value | Trusts |
| --- | --- |
| `PRIVATE_SUBNETS` (default in the container) | any proxy on a private/loopback network, e.g. the Docker network |
| `10.0.1.5,172.18.0.0/16` | only these IPs/CIDRs |
| `*` | every peer — only if nothing but the proxy can reach the container |
| empty | nobody (forwarded headers are ignored) |

It is read by `app/src/config/trustedproxy.php`. If the container's port is published to
the internet directly, set an explicit proxy IP instead of `PRIVATE_SUBNETS`.

## Look and feel

The starter app uses [fltc](https://github.com/fsteltenkamp/laravel-twcss-components) components.
Components take a colour role (`color="primary"`, `danger`, … default `base`); the theme
decides which palette each role uses and the skin decides the look. Set an app's default
with `APP_SKIN` / `APP_THEME`, and limit what users can pick on *Settings → Appearance* in
`app/src/config/appearance.php`. Users' choices are stored on their account.

The layout (`resources/views/layouts/app.blade.php`) is a sidebar with a slim top bar. The top
bar shows the page's breadcrumbs, or its title when it has none. A page component passes
breadcrumbs as a top-level slot, outside its root element:

```blade
<x-slot:breadcrumbs>
    <x-fltc::nav.breadcrumbs.item :href="route('admin.users.index')" icon="ph ph-user">{{ __('Users') }}</x-fltc::nav.breadcrumbs.item>
    <x-fltc::nav.breadcrumbs.item :is-active="true">{{ $user->name }}</x-fltc::nav.breadcrumbs.item>
</x-slot:breadcrumbs>
```

On wide screens users can hide the sidebar and open it from the top bar when they need it, or
pin it back. The choice is kept per browser.

## Notifications

On *Settings → Notifications* users add their own channels (Gotify, Discord webhook, email to
their account address, generic webhook) and tick which events each channel receives.

Events are registered in one place, `app/src/config/notifications.php`:

```php
'team.members_changed' => [
    'label' => 'Team members changed',
    'description' => 'Someone joined or left a team you can see.',
    'group' => 'Teams',
    'ability' => 'manage-users',     // optional: needed to see and receive the event at all
    'element_ability' => 'view',     // optional: checked against the element, e.g. a policy method
],
```

Send one from anywhere. Delivery runs on the queue, so a worker has to be running:

```php
app(Notifier::class)->send('team.members_changed', $message, $url, $team);
```

Users only see events their `ability` allows. Both abilities are checked again on every send, so
a user who loses access stops receiving notifications right away. With `element_ability`, users
are only notified about elements they can see. Pass the element to `send()`, without one nobody
receives the event.

Each channel can have its own message text, with the placeholders `{title}`, `{message}`, `{url}`,
`{event}` and `{time}`. Empty means the default for that channel type.

To add a service, implement `ChannelDriver` and add it under `channels` in the same config file.
The form on the settings page is built from the driver's `fields()`.

Generic webhooks receive a JSON `POST` with `event`, `title`, `message`, `url` and `occurred_at`,
where a channel's message text sets `message`.
With a signing secret, `X-Signature` is `sha256=` followed by the HMAC-SHA256 of the raw body.

Users enter the URLs the server calls, so channels may only point to public addresses. Set
`NOTIFICATIONS_ALLOW_PRIVATE_HOSTS=true` to allow private ones, e.g. a Gotify server on the same
network. Only do that if every user of the app is trusted, since they could then reach internal
services such as the database or Redis.

## Updating apps built from this template

See [UPGRADING.md](UPGRADING.md) for what changed in each template update and what an app
built from it has to do.
