<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_has_sidebar_with_pin_controls_and_no_footer_bar(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('app-sidebar', false)
            ->assertSee('data-sidebar-when="pinned"', false)
            ->assertSee('data-sidebar-when="collapsed"', false)
            ->assertSee('window.appSidebar', false)
            ->assertDontSee('fixed inset-x-0 bottom-0', false);
    }

    public function test_top_bar_shows_the_title_without_breadcrumbs(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('aria-label="Breadcrumb"', false);
    }

    public function test_page_components_pass_breadcrumbs_into_the_top_bar(): void
    {
        $admin = $this->userWithPermissions('user.edit');
        $user = User::factory()->create(['name' => 'Crumb Target']);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSeeInOrder(['aria-label="Breadcrumb"', 'User management', 'Users', 'aria-current="page"', 'Crumb Target', '<main'], false);
    }
}
