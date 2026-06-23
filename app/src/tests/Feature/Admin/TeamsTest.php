<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Teams\Index;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Tests\TestCase;

class TeamsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        View::share([
            'mainTheme' => 'neutral',
            'secondaryTheme' => 'slate',
            'accentTheme' => 'sky',
            'infoTheme' => 'blue',
            'successTheme' => 'green',
            'warningTheme' => 'yellow',
            'errorTheme' => 'red',
            'darkmode' => false,
        ]);
    }

    public function test_teams_index_requires_authentication(): void
    {
        $this->get(route('admin.teams.index'))
            ->assertRedirect(route('login'));
    }

    public function test_teams_index_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.teams.index'))->assertOk();
    }

    public function test_admin_can_create_team(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('newName', 'Engineering')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', ['name' => 'Engineering']);
    }

    public function test_create_team_validates_required_name(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->set('newName', '')
            ->call('save')
            ->assertHasErrors(['newName' => 'required']);
    }

    public function test_admin_can_delete_team(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Old Team']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $team->id)
            ->assertSet('confirmingDeleteId', $team->id)
            ->call('deleteTeam');

        $this->assertNull(Team::find($team->id));
    }

    public function test_deleting_team_removes_user_memberships(): void
    {
        $this->actingAs(User::factory()->create());
        $team = Team::create(['name' => 'Old Team']);
        $user = User::factory()->create();
        $team->users()->attach($user);

        Livewire::test(Index::class)
            ->call('confirmDelete', $team->id)
            ->call('deleteTeam');

        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id]);
    }

    public function test_search_filters_teams_by_name(): void
    {
        $this->actingAs(User::factory()->create());
        Team::create(['name' => 'Engineering']);
        Team::create(['name' => 'Marketing']);

        Livewire::test(Index::class)
            ->set('search', 'Engineering')
            ->assertSee('Engineering')
            ->assertDontSee('Marketing');
    }

    public function test_clearing_search_shows_all_teams(): void
    {
        $this->actingAs(User::factory()->create());
        Team::create(['name' => 'Engineering']);
        Team::create(['name' => 'Marketing']);

        Livewire::test(Index::class)
            ->set('search', 'Engineering')
            ->set('search', '')
            ->assertSee('Engineering')
            ->assertSee('Marketing');
    }

    public function test_teams_are_displayed_alphabetically(): void
    {
        $this->actingAs(User::factory()->create());
        Team::create(['name' => 'Zebra Team']);
        Team::create(['name' => 'Alpha Team']);

        $component = Livewire::test(Index::class);

        $html = $component->html();
        $this->assertLessThan(
            strpos($html, 'Zebra Team'),
            strpos($html, 'Alpha Team'),
            'Teams should be displayed in alphabetical order'
        );
    }
}
