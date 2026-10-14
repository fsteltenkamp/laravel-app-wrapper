<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default look
    |--------------------------------------------------------------------------
    |
    | The fltc skin (fonts, corners, effects) and theme (which palette each colour
    | role uses) for guests and for users who have not picked their own.
    |
    */

    'skin' => env('APP_SKIN', 'default'),

    'theme' => env('APP_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Choices offered on the appearance settings page
    |--------------------------------------------------------------------------
    |
    | Remove entries to restrict what users can pick; an empty list hides that
    | select. Keep "custom" in the themes to let users choose a palette per role.
    |
    */

    'skins' => ['default', 'cyberpunk', 'fantasy', 'wh40k'],

    'themes' => ['default', 'dracula', 'nord', 'catppuccin', 'solarized', 'gruvbox', 'cyberpunk', 'fantasy', 'wh40k', 'custom'],

];
