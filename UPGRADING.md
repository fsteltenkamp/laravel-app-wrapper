# Upgrading derivative apps

Each section lists what changed in the template and what an app built from it has to do.
Newest first.

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
