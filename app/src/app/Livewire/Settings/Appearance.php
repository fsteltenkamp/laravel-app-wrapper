<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Fsteltenkamp\TwcssComponents\View\Components\Theme\Picker;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The fltc theme picker applies a choice in the browser at once and announces it with
 * a window event; this component stores it on the user, so it follows them to every
 * device (see partials/appearance.blade.php).
 */
#[Title('Appearance settings')]
class Appearance extends Component
{
    #[Renderless]
    public function saveSkin(string $skin): void
    {
        Validator::make(['skin' => $skin], [
            'skin' => ['required', Rule::in(config('appearance.skins'))],
        ])->validate();

        $this->user()->update(['skin' => $skin]);
    }

    /**
     * @param  array<string, string>  $colors  role => palette, used by the custom theme
     */
    #[Renderless]
    public function saveTheme(string $theme, array $colors = []): void
    {
        $validated = Validator::make(['theme' => $theme, 'colors' => $colors], [
            'theme' => ['required', Rule::in(config('appearance.themes'))],
            'colors' => ['array:'.implode(',', Picker::ROLES)],
            'colors.*' => [Rule::in(Picker::PALETTES)],
        ])->validate();

        $this->user()->update([
            'theme' => $validated['theme'],
            'theme_colors' => $validated['colors'] ?: null,
        ]);
    }

    public function render(): View
    {
        return view('livewire.settings.appearance');
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
