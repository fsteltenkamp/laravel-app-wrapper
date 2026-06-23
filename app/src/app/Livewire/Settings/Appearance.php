<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Appearance settings')]
class Appearance extends Component
{
    public string $preset = '';
    public string $mainTheme = 'neutral';
    public string $secondaryTheme = 'slate';
    public string $accentTheme = 'sky';
    public string $infoTheme = 'blue';
    public string $successTheme = 'green';
    public string $warningTheme = 'yellow';
    public string $errorTheme = 'red';

    public array $availableThemes = [
        'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal',
        'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink',
        'rose', 'slate', 'gray', 'zinc', 'neutral', 'stone',
        'taupe', 'mauve', 'mist', 'olive',
    ];

    public array $presets = [
        'standard'    => ['label' => 'Standard',     'mainTheme' => 'neutral', 'secondaryTheme' => 'slate',   'accentTheme' => 'sky',    'infoTheme' => 'blue',   'successTheme' => 'green',   'warningTheme' => 'yellow', 'errorTheme' => 'red'],
        'dracula'     => ['label' => 'Dracula',      'mainTheme' => 'slate',   'secondaryTheme' => 'zinc',    'accentTheme' => 'purple', 'infoTheme' => 'indigo', 'successTheme' => 'emerald', 'warningTheme' => 'amber',  'errorTheme' => 'rose'],
        'solarized'   => ['label' => 'Solarized',    'mainTheme' => 'stone',   'secondaryTheme' => 'taupe',   'accentTheme' => 'teal',   'infoTheme' => 'cyan',   'successTheme' => 'emerald', 'warningTheme' => 'amber',  'errorTheme' => 'red'],
        'monokai'     => ['label' => 'Monokai',      'mainTheme' => 'zinc',    'secondaryTheme' => 'gray',    'accentTheme' => 'lime',   'infoTheme' => 'teal',   'successTheme' => 'green',   'warningTheme' => 'yellow', 'errorTheme' => 'rose'],
        'nord'        => ['label' => 'Nord',         'mainTheme' => 'slate',   'secondaryTheme' => 'mist',    'accentTheme' => 'sky',    'infoTheme' => 'blue',   'successTheme' => 'teal',    'warningTheme' => 'amber',  'errorTheme' => 'rose'],
        'gruvbox'     => ['label' => 'Gruvbox',      'mainTheme' => 'stone',   'secondaryTheme' => 'taupe',   'accentTheme' => 'orange', 'infoTheme' => 'sky',    'successTheme' => 'olive',   'warningTheme' => 'amber',  'errorTheme' => 'red'],
        'tokyo-night' => ['label' => 'Tokyo Night',  'mainTheme' => 'slate',   'secondaryTheme' => 'zinc',    'accentTheme' => 'violet', 'infoTheme' => 'indigo', 'successTheme' => 'teal',    'warningTheme' => 'amber',  'errorTheme' => 'rose'],
        'catppuccin'  => ['label' => 'Catppuccin',   'mainTheme' => 'zinc',    'secondaryTheme' => 'mauve',   'accentTheme' => 'pink',   'infoTheme' => 'sky',    'successTheme' => 'teal',    'warningTheme' => 'amber',  'errorTheme' => 'rose'],
        'one-dark'    => ['label' => 'One Dark',     'mainTheme' => 'zinc',    'secondaryTheme' => 'gray',    'accentTheme' => 'sky',    'infoTheme' => 'blue',   'successTheme' => 'emerald', 'warningTheme' => 'amber',  'errorTheme' => 'red'],
        'everforest'  => ['label' => 'Everforest',   'mainTheme' => 'stone',   'secondaryTheme' => 'olive',   'accentTheme' => 'green',  'infoTheme' => 'teal',   'successTheme' => 'emerald', 'warningTheme' => 'yellow', 'errorTheme' => 'orange'],
        'rose-pine'   => ['label' => 'Rosé Pine',    'mainTheme' => 'mauve',   'secondaryTheme' => 'taupe',   'accentTheme' => 'rose',   'infoTheme' => 'indigo', 'successTheme' => 'teal',    'warningTheme' => 'amber',  'errorTheme' => 'red'],
    ];

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->mainTheme = $user->main_theme ?? 'neutral';
        $this->secondaryTheme = $user->secondary_theme ?? 'slate';
        $this->accentTheme = $user->accent_theme ?? 'sky';
        $this->infoTheme = $user->info_theme ?? 'blue';
        $this->successTheme = $user->success_theme ?? 'green';
        $this->warningTheme = $user->warning_theme ?? 'yellow';
        $this->errorTheme = $user->error_theme ?? 'red';
        $this->preset = $this->detectPreset();
    }

    private function detectPreset(): string
    {
        foreach ($this->presets as $key => $p) {
            if (
                $p['mainTheme']      === $this->mainTheme &&
                $p['secondaryTheme'] === $this->secondaryTheme &&
                $p['accentTheme']    === $this->accentTheme &&
                $p['infoTheme']      === $this->infoTheme &&
                $p['successTheme']   === $this->successTheme &&
                $p['warningTheme']   === $this->warningTheme &&
                $p['errorTheme']     === $this->errorTheme
            ) {
                return $key;
            }
        }
        return '';
    }

    public function updated(string $property): void
    {
        if ($property === 'preset') {
            return;
        }
        $this->preset = $this->detectPreset();
        $this->save();
    }

    public function updatedPreset(): void
    {
        if ($this->preset === '' || !isset($this->presets[$this->preset])) {
            return;
        }
        $p = $this->presets[$this->preset];
        $this->mainTheme = $p['mainTheme'];
        $this->secondaryTheme = $p['secondaryTheme'];
        $this->accentTheme = $p['accentTheme'];
        $this->infoTheme = $p['infoTheme'];
        $this->successTheme = $p['successTheme'];
        $this->warningTheme = $p['warningTheme'];
        $this->errorTheme = $p['errorTheme'];
        $this->save();
    }

    public function save(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $user->main_theme = $this->mainTheme;
        $user->secondary_theme = $this->secondaryTheme;
        $user->accent_theme = $this->accentTheme;
        $user->info_theme = $this->infoTheme;
        $user->success_theme = $this->successTheme;
        $user->warning_theme = $this->warningTheme;
        $user->error_theme = $this->errorTheme;
        $user->save();

        $this->redirect(route('appearance.edit'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
