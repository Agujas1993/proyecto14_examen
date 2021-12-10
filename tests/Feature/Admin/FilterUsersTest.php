<?php

namespace Tests\Feature\Admin;

use App\Skill;
use App\Team;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function filter_users_by_state_active()
    {
        $activeUser = factory(User::class)->create();
        $inactiveUser = factory(User::class)->state('inactive')->create();

        $response = $this->get('usuarios?state=active');

        $response->assertViewCollection('users')
            ->contains($activeUser)
            ->notContains($inactiveUser);
    }

    /** @test */
    public function filter_users_by_state_inactive()
    {
        $activeUser = factory(User::class)->create();
        $inactiveUser = factory(User::class)->state('inactive')->create();

        $response = $this->get('usuarios?state=inactive');

        $response->assertViewCollection('users')
            ->contains($inactiveUser)
            ->notContains($activeUser);
    }

    /** @test */
    public function filter_users_by_role_admin()
    {
        $admin = factory(User::class)->create(['role' => 'admin']);

        $user = factory(User::class)->create(['role' => 'user']);

        $response = $this->get('usuarios?role=admin');

        $response->assertStatus(200);

        $response->assertViewCollection('users')
            ->contains($admin)
            ->notContains($user);
    }

    /** @test */
    public function filter_users_by_role_user()
    {
        $admin = factory(User::class)->create(['role' => 'admin']);

        $user = factory(User::class)->create(['role' => 'user']);

        $response = $this->get('usuarios?role=user');

        $response->assertStatus(200);

        $response->assertViewCollection('users')
            ->contains($user)
            ->notContains($admin);
    }

    /** @test */
    public function filter_users_by_skill()
    {
        $php = factory(Skill::class)->create(['name' => 'php']);
        $css = factory(Skill::class)->create(['name' => 'css']);

        $backendDev = factory(User::class)->create();
        $backendDev->skills()->attach($php);

        $fullStackDev = factory(User::class)->create();
        $fullStackDev->skills()->attach([$php->id, $css->id]);

        $frontendDev = factory(User::class)->create();
        $frontendDev->skills()->attach($css);

        $response = $this->get("usuarios?skills[0]={$php->id}&skills[1]={$css->id}");

        $response->assertStatus(200);

        $response->assertViewCollection('users')
            ->contains($fullStackDev)
            ->notContains($frontendDev)
            ->notContains($backendDev);
    }

    /** @test */
    public function filter_users_created_from_date()
    {
        $newestUser = factory(User::class)->create([
            'created_at' => '2018-10-02 12:00:00',
        ]);

        $oldestUser = factory(User::class)->create([
            'created_at' => '2018-09-29 12:00:00',
        ]);

        $newUser = factory(User::class)->create([
            'created_at' => '2018-10-01 00:00:00',
        ]);

        $oldUser = factory(User::class)->create([
            'created_at' => '2018-09-30 23:59:59',
        ]);

        $response = $this->get('usuarios?from=01/10/2018');

        $response->assertOk();

        $response->assertViewCollection('users')
            ->contains($newUser)
            ->contains($newestUser)
            ->notContains($oldUser)
            ->notContains($oldestUser);
    }

    /** @test */
    public function filter_users_created_to_date()
    {
        $newestUser = factory(User::class)->create([
            'created_at' => '2018-10-02 12:00:00',
        ]);

        $oldestUser = factory(User::class)->create([
            'created_at' => '2018-09-29 12:00:00',
        ]);

        $newUser = factory(User::class)->create([
            'created_at' => '2018-10-01 00:00:00',
        ]);

        $oldUser = factory(User::class)->create([
            'created_at' => '2018-09-30 23:59:59',
        ]);

        $response = $this->get('usuarios?to=30/09/2018');

        $response->assertOk();

        $response->assertViewCollection('users')
            ->contains($oldestUser)
            ->contains($oldUser)
            ->notContains($newestUser)
            ->notContains($newUser);
    }

    /** @test */
    public function filter_users_by_state_search_role()
    {
        $jose = factory(User::class)->create([
            'first_name' => 'Jose',
            'role' => 'user'
        ]);
        $pepe = factory(User::class)->state('inactive')->create([
            'first_name' => 'Pepe',
            'role' => 'admin'
        ]);
        $mario = factory(User::class)->create([
            'first_name' => 'Mario',
            'role' => 'user'
        ]);

        $luis = factory(User::class)->state('inactive')->create([
            'first_name' => 'Luis',
            'role' => 'user'
        ]);

        $response = $this->get('usuarios?state=active&search=Jo&role=user');

        $response->assertViewCollection('users')
            ->contains($jose)
            ->notContains($pepe)
            ->notContains($mario)
            ->notContains($luis);
    }

    /** @test */
    public function filter_users_by_skill_and_team()
    {
        $team = factory(Team::class)->create();
        $php = factory(Skill::class)->create(['name' => 'php']);
        $css = factory(Skill::class)->create(['name' => 'css']);
        $html = factory(Skill::class)->create(['name' => 'html']);

        $backendDev = factory(User::class)->create();
        $backendDev->skills()->attach([$php->id, $css->id]);

        $fullStackDev = factory(User::class)->create([
            'team_id' => $team->id,
        ]);
        $fullStackDev->skills()->attach([$php->id, $css->id]);

        $frontendDev = factory(User::class)->create([
            'team_id' => $team->id,
        ]);

        $designer = factory(User::class)->create([
            'team_id' => $team->id
        ]);
        $designer->skills()->attach([$css->id, $html->id]);

        $response = $this->get("usuarios?skills[0]={$php->id}&skills[1]={$css->id}&team=with_team");

        $response->assertStatus(200);

        $response->assertViewCollection('users')
            ->contains($fullStackDev)
            ->notContains($frontendDev)
            ->notContains($backendDev)
            ->notContains($designer);
    }
}



