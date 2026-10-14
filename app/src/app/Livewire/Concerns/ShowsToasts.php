<?php

namespace App\Livewire\Concerns;

/**
 * Shows a toast through the <x-fltc::toast.container> in the layouts.
 */
trait ShowsToasts
{
    /**
     * @param  string  $color  an fltc colour role (success, info, warning, danger, …) or palette
     */
    protected function toast(string $title, string $color = 'success', ?string $message = null): void
    {
        $this->dispatch('fltc:toast', title: $title, message: $message, color: $color);
    }
}
