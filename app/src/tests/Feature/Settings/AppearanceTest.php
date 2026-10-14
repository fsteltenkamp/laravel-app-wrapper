<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Appearance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('appearance.edit'))
            ->assertOk()
            ->assertSee('fltc-theme-picker-select', false);
    }

    public function test_skin_can_be_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Appearance::class)
            ->call('saveSkin', 'cyberpunk')
            ->assertHasNoErrors();

        $this->assertSame('cyberpunk', $user->refresh()->skin);
    }

    public function test_unknown_skin_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Appearance::class)
            ->call('saveSkin', 'not-a-skin')
            ->assertHasErrors('skin');

        $this->assertNull($user->refresh()->skin);
    }

    public function test_theme_and_custom_colors_can_be_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Appearance::class)
            ->call('saveTheme', 'custom', ['primary' => 'rose', 'base' => 'zinc'])
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('custom', $user->theme);
        $this->assertSame(['primary' => 'rose', 'base' => 'zinc'], $user->theme_colors);
    }

    public function test_unknown_theme_role_or_palette_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Appearance::class)
            ->call('saveTheme', 'custom', ['primary' => 'not-a-palette'])
            ->assertHasErrors('colors.primary');

        Livewire::test(Appearance::class)
            ->call('saveTheme', 'custom', ['not-a-role' => 'rose'])
            ->assertHasErrors('colors');

        $this->assertNull($user->refresh()->theme);
    }

    public function test_saved_appearance_is_applied_on_every_page(): void
    {
        $user = User::factory()->create([
            'skin' => 'fantasy',
            'theme' => 'custom',
            'theme_colors' => ['primary' => 'rose'],
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee("localStorage.setItem('fltc-skin', 'fantasy')", false)
            ->assertSee("localStorage.setItem('fltc-theme', 'custom')", false)
            ->assertSee('primary', false);
    }

    public function test_guests_get_the_configured_default_look(): void
    {
        config(['appearance.skin' => 'wh40k']);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee("localStorage.setItem('fltc-skin'", false)
            ->assertSee("localStorage.removeItem('fltc-skin')", false)
            ->assertSee("skin: 'wh40k'", false);
    }
}
