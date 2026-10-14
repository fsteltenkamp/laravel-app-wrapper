{{--
    Applies the fltc skin, theme and light/dark mode before the first paint.

    The look lives in localStorage, where the theme picker and dark-mode toggle keep
    it. For a signed-in user the account is the source of truth, so their saved
    choices are written there first and the look follows them to every device.
    Guest pages (login, …) clear those choices and fall back to the configured
    default look.

    This replaces x-fltc::theme.script, which runs once per full page load: on
    wire:navigate, Livewire copies the server's <html> attributes over the live ones
    (dropping data-skin, data-theme and the `dark` class) and skips head scripts it
    has already run. So the look is re-applied after every page swap.
--}}
@auth
    @php($appearanceUser = auth()->user())
    <script>
        try {
            localStorage.setItem('fltc-skin', @js($appearanceUser->skin ?? config('appearance.skin')));
            localStorage.setItem('fltc-theme', @js($appearanceUser->theme ?? config('appearance.theme')));
            @if ($appearanceUser->theme_colors)
                localStorage.setItem('fltc-theme-custom', @js(json_encode($appearanceUser->theme_colors)));
            @else
                localStorage.removeItem('fltc-theme-custom');
            @endif
        } catch (e) {}
    </script>
@else
    <script>
        try {
            localStorage.removeItem('fltc-skin');
            localStorage.removeItem('fltc-theme');
            localStorage.removeItem('fltc-theme-custom');
        } catch (e) {}
    </script>
@endauth

<script>
    (function () {
        var defaults = { skin: @js(config('appearance.skin')), theme: @js(config('appearance.theme')) };

        function apply() {
            var root = document.documentElement;
            try {
                root.setAttribute('data-skin', localStorage.getItem('fltc-skin') || defaults.skin);
                root.setAttribute('data-theme', localStorage.getItem('fltc-theme') || defaults.theme);
                for (var i = root.attributes.length - 1; i >= 0; i--) {
                    var name = root.attributes[i].name;
                    if (name.indexOf('data-theme-') === 0) root.removeAttribute(name);
                }
                var roles = JSON.parse(localStorage.getItem('fltc-theme-custom') || '{}');
                for (var role in roles) {
                    if (/^[a-z]+$/.test(role) && /^[a-z]+$/.test(roles[role])) root.setAttribute('data-theme-' + role, roles[role]);
                }
            } catch (e) {}

            // Same key and values as x-fltc::darkmode.toggle: light | dark | system.
            var mode = 'system';
            try { mode = localStorage.getItem('color') || 'system'; } catch (e) {}
            root.classList.toggle('dark', mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));
        }

        apply();

        if (! window.__appearanceNavigateHook) {
            window.__appearanceNavigateHook = true;
            document.addEventListener('livewire:navigating', function (e) {
                e.detail.onSwap(apply);
            });
        }
    })();
</script>
