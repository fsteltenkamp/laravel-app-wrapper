# Upgrading derivative apps

Each section lists what changed in the template and what an app built from it has to do.
Newest first.

## 2026-11 — Team permissions apply to members

Permissions and roles given to a team now count for its members in `can()`, `@can` and
`hasAnyPermission()`. Saving roles or permissions on a team no longer throws `GuardDoesNotMatch`.

- `app/Models/User.php`: copy the `hasOwnPermissionTo` alias on the trait `use` statement,
  `hasPermissionTo()` and the `Spatie\Permission\Contracts\Permission` import.
- `app/Models/Team.php`: add `protected string $guard_name = 'web';` (your guard, if it isn't
  `web`).
- If the app overrides `hasPermissionTo()` or has its own team permission logic, merge it with
  the above or remove it.
- Before deploying, check what your teams have been given: anything already assigned to a team
  (by a seeder, for example) starts to apply to its members with this update.
- `hasRole()`, `@role` and `getAllPermissions()` are unchanged and still only look at the
  user's own roles and permissions. Use `can()` for anything a team should be able to grant.
- A user's teams are loaded once per model instance. After changing membership in the same
  request, check permissions on `$user->fresh()`.
- Optional: copy `app/src/tests/Feature/TeamPermissionsTest.php` and the two team assignment
  tests at the end of `app/src/tests/Feature/Admin/TeamEditTest.php`.

## 2026-11 — Notifications

Users can add notification channels (Gotify, Discord webhook, email, generic webhook) and
subscribe them to events on *Settings → Notifications*. See the README, "Notifications".

- Copy `app/src/app/Notifications/`, `app/src/app/Jobs/DeliverNotification.php`,
  `app/src/app/Models/NotificationChannel.php`, `app/src/app/Models/NotificationSubscription.php`,
  `app/src/app/Livewire/Settings/Notifications.php`,
  `app/src/resources/views/livewire/settings/notifications.blade.php`,
  `app/src/config/notifications.php` and
  `app/src/database/migrations/2026_11_09_00000*`, then migrate.
- `app/Models/User.php`: add the `notificationChannels()` relation.
- `routes/settings.php`: add, in the `['auth', 'verified']` group,
  `Route::livewire('settings/notifications', Notifications::class)->name('notifications.edit');`
  and import `App\Livewire\Settings\Notifications`.
- `resources/views/components/settings/layout.blade.php`: add
  `['route' => 'notifications.edit', 'label' => __('Notifications'), 'icon' => 'bell'],` to
  `$settingsNav`.
- Register your app's events in `config/notifications.php`, and send them with
  `app(Notifier::class)->send(…)`. See the README, "Notifications".
- Example events for users and teams, keep them or leave them out together:
  - The `send()` calls are in `Admin/Users/Index` (`save`, `deleteUser`), `Admin/Users/Edit`
    (`saveTeams`), `Admin/Teams/Index` (`save`, `deleteTeam`) and `Admin/Teams/Edit`
    (`saveMembers`). Copy them if you merge those components by hand.
  - `send()` throws for an event that isn't registered, so remove the call together with the
    event.
  - `team.members_changed` needs `app/src/app/Policies/TeamPolicy.php`. If the app has its own
    `TeamPolicy`, give it a `view()` method instead.
- `app/deployment-scripts/shared/00-env-populate.sh`: add
  `NOTIFICATIONS_ALLOW_PRIVATE_HOSTS=${NOTIFICATIONS_ALLOW_PRIVATE_HOSTS:-false}` to the
  "Extern gesetzt" block.
- `docker-compose.yml`: add
  `NOTIFICATIONS_ALLOW_PRIVATE_HOSTS: ${NOTIFICATIONS_ALLOW_PRIVATE_HOSTS:-false}` to
  `x-environment`. Set it to `true` in your deployment's env only if channels have to reach
  private addresses and every user is trusted.
- `app/src/.env.example`: add the commented `NOTIFICATIONS_ALLOW_PRIVATE_HOSTS` entry.
- The queue worker has to run, deliveries are queued.
- Channel settings are encrypted with `APP_KEY`. Rotating the key makes them unreadable, and
  users then have to enter their channels again.
- Optional: copy `app/src/tests/Feature/Notifications/` and
  `app/src/tests/Feature/Settings/NotificationsTest.php`.

## 2026-11 — Slim top bar, breadcrumbs, hideable sidebar

The top bar is now 48px (was 64px) and shows breadcrumbs. From `lg` up, users can hide the
sidebar (it then opens over the content from the top bar) and pin it back; the choice is kept
in `localStorage` under `app-sidebar`.

- Copy `app/src/resources/views/layouts/app.blade.php` and re-add your sidebar links. If you
  changed the brand row, keep the two buttons with `data-sidebar-when`. The sidebar needs the
  `app-sidebar` class with its `x-bind:data-open` / `x-on:click.outside` attributes, and the
  navbar toggle needs `app-sidebar-toggle`.
- Copy `app/src/resources/views/partials/sidebar-state.blade.php` and include it in
  `partials/head.blade.php` after `partials.appearance`.
- `resources/css/app.css`: copy the "Sidebar pinned or hidden" block at the end.
- Breadcrumbs: add an `<x-slot:breadcrumbs>` to pages that sit below another page (see the
  README, "Look and feel"). Pages without one show their title in the top bar, as before.
  The admin edit pages have them now, and their "Back to …" buttons are gone.
- `<main>` padding is `py-6` (was `py-8`).
- Optional: copy `app/src/tests/Feature/LayoutTest.php`.

## 2026-10 — Trusted proxies

`X-Forwarded-*` headers from the reverse proxy are now trusted, controlled by
`TRUSTED_PROXIES` (default `PRIVATE_SUBNETS` in the container, see the README).

- Copy `app/src/config/trustedproxy.php`.
- `app/deployment-scripts/shared/00-env-populate.sh`: add
  `TRUSTED_PROXIES=${TRUSTED_PROXIES-PRIVATE_SUBNETS}` to the "Extern gesetzt" block.
- `docker-compose.yml`: add `TRUSTED_PROXIES: ${TRUSTED_PROXIES-PRIVATE_SUBNETS}` to
  `x-environment`. Add `TRUSTED_PROXIES` to your deployment's env if the default doesn't fit.
- If the app already called `$middleware->trustProxies(at: …)` in `bootstrap/app.php`,
  remove it — that call overrides the config value.
- Optional: copy `app/src/tests/Feature/TrustedProxiesTest.php`.

## 2026-10 — PHP 8.5, fltc colour roles, Flux removed

### Runtime and dependencies

- **Dev container** is now built on `dunglas/frankenphp:php8.5-trixie`, the same image as
  production (Microsoft's `devcontainers/php` images stop at PHP 8.4). It adds a `vscode`
  user through the `common-utils` feature. Copy `.devcontainer/` and rebuild the container.
- **Production image**: `php8.5-bookworm` → `php8.5-trixie`, Composer pinned to `composer:2`.
- **Meilisearch** `v1.14.0` → `v1.55.0`. Existing `search_data` volumes need an upgrade:
  take a snapshot, then start once with `MEILI_EXPERIMENTAL_DUMPLESS_UPGRADE=true`, or
  export a dump with the old version and import it into the new one. If the app doesn't use
  Scout, just drop the volume.
- `composer.json`: `"php": "^8.5"`, remove `livewire/flux`, `phpunit/phpunit` `^13.1.7`.
  Then `composer update -W`.
- `package.json`: `@laravel/passkeys` `^0.4.0` (API compatible), `concurrently` `^10`,
  remove the stale `@rollup/rollup-linux-x64-gnu` pin (Vite 8 uses Rolldown). Then
  `bun update`.

### fltc components: `theme` → `color`

fltc now separates **colour** (a component's `color` prop: a role such as `primary` or a
fixed palette), **theme** (`data-theme`: which palette each role uses) and **skin**
(`data-skin`: the look). See the fltc README, "Colours, themes and skins".

1. Rewrite the props in your views, then review the diff:

   ```bash
   perl scripts/fltc-theme-to-color.pl $(grep -rl theme app/src/resources/views)
   ```

   | Before | After |
   | --- | --- |
   | `:theme="$mainTheme"` on a solid button | `color="primary"` |
   | `:theme="$mainTheme"` anywhere else, `theme="zinc"` | nothing (default `base`) |
   | `theme="red"` | `color="danger"` |
   | `bg-{{ $mainTheme }}-500`, `text-{{ $mainTheme }}-600` | `bg-primary-600`, `text-primary-600` |
   | `text-zinc-500`, `border-stone-200`, … in your markup | `text-base-500`, `border-base-200` |
   | `text-green-600` / `text-red-600` | `text-success-600` / `text-danger-600` |

2. `resources/css/app.css`: import fltc's `colors.css`, `themes.css` and
   `skin-effects.css`, add `@source` for fltc's `src/Support/`, and remove the Flux-era
   `--color-accent*` variables and `[data-flux-*]` rules (copy the template's file).
3. Layouts: remove the hard-coded `class="dark"` from `<html>`, use `<x-fltc::body>`, add
   `@persist('fltc-toasts') <x-fltc::toast.container /> @endpersist`. `partials/head` now
   includes `partials/appearance`, which applies skin, theme and dark mode before paint.
4. Sidebar group: `key` / `:isOpen` → `id` / `activeWhen="admin/*"`.
5. Form inputs: `<x-fltc::form.input.text type="email">` → `<x-fltc::form.input.email>`
   (same for `password`); a `value` prop must not be `null`.
6. Paginators: `$items->links('fltc::pagination.livewire')`.

### Appearance settings

The seven per-user palette columns are replaced by `skin`, `theme` and `theme_colors`
(`database/migrations/2026_10_14_000000_replace_theme_columns_on_users_table.php`). Users
who had changed colours get the `custom` theme with their palettes mapped to the new roles
(main → base, accent → primary and accent, error → danger). The migration is reversible.

- Delete `app/Http/Middleware/ApplyThemeVariables.php` and its registration in
  `bootstrap/app.php`; nothing reads `$mainTheme` & co. any more.
- Copy `app/Livewire/Settings/Appearance.php`, its view, `partials/appearance.blade.php`,
  `config/appearance.php` and the `User` model changes.
- Default look per app: `APP_SKIN` / `APP_THEME` (or `config/appearance.php`, which also
  limits what users may pick).

### Toasts

`Flux::toast()` dispatched an event no component listened for, so these toasts never
showed. Use the `App\Livewire\Concerns\ShowsToasts` trait: `$this->toast(__('Saved.'))`
(optional colour: `'info'`, `'warning'`, `'danger'`, …).

### Fixes worth copying even if you skip the rest

- **Security**: the Roles, Permissions and Teams admin routes had no authorization, so any
  verified user could add themselves to a team and grant it every permission. They now
  require `can:manage-users` (`routes/admin.php`).
- Login form was missing `@csrf`, so password logins are rejected with 419 (tests skip
  CSRF, so they didn't catch it).
- `/` rendered a view that doesn't exist; it now redirects to the dashboard.
- Reset-password page crashed when the link had no `email` query parameter.
- Don't commit or copy `bootstrap/cache/config.php` between apps: a stale one points at
  the other app's paths and breaks every view.
