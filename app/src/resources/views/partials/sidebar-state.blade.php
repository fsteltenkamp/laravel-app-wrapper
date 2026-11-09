{{--
    Pinned or hidden sidebar (from lg up), per browser like the dark mode.

    Sets data-sidebar="pinned|collapsed" on <html> before the first paint; app.css hides
    the column while it is collapsed and lets the top bar's toggle open it over the content.
    On wire:navigate Livewire copies the server's <html> attributes over the live ones, so
    the state is re-applied after every page swap (as in partials/appearance).

    The buttons in the sidebar call appSidebar.pin() / appSidebar.collapse().
--}}
<script>
    (function () {
        var key = 'app-sidebar';

        function apply() {
            var state = 'pinned';
            try { state = localStorage.getItem(key) === 'collapsed' ? 'collapsed' : 'pinned'; } catch (e) {}
            document.documentElement.setAttribute('data-sidebar', state);
        }

        function set(state) {
            try { localStorage.setItem(key, state); } catch (e) {}
            apply();
        }

        window.appSidebar = {
            pin: function () { set('pinned'); },
            collapse: function () { set('collapsed'); },
        };

        apply();

        if (! window.__sidebarNavigateHook) {
            window.__sidebarNavigateHook = true;
            document.addEventListener('livewire:navigating', function (e) {
                e.detail.onSwap(apply);
            });
        }
    })();
</script>
