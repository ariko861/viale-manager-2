<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Pages\MaisonneesKanbanBoard;
use App\Filament\Resources\MessageResource;
use App\Filament\Resources\OptionResource;
use App\Filament\Resources\ProfileResource;
use App\Filament\Resources\RoomResource;
use App\Filament\Resources\SejourResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\VisitorResource;
use App\Models\User;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Database\Factories\UserFactory;
use Filament\Pages\Dashboard;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_has_no_access_to_admin_panel(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(Dashboard::getUrl())->assertForbidden();
        $this->get(SejourResource::getUrl())->assertForbidden();

        $user->delete();

    }

    public function test_user_with_role_has_access_to_admin_panel_dashboard_only(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => fake()->unique()->word(), 'guard_name' => 'web']);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $this->get(Dashboard::getUrl())->assertSuccessful();
        $this->get(SejourResource::getUrl())->assertForbidden();
        $this->get(VisitorResource::getUrl())->assertForbidden();
        $this->get(MessageResource::getUrl())->assertForbidden();
        $this->get(OptionResource::getUrl())->assertForbidden();
        $this->get(ProfileResource::getUrl())->assertForbidden();
        $this->get(RoomResource::getUrl())->assertForbidden();
        $this->get(UserResource::getUrl())->assertForbidden();
        $this->get(RoleResource::getUrl())->assertForbidden();
        $this->get(MaisonneesKanbanBoard::getUrl())->assertForbidden();

        $user->delete();
        $role->delete();

    }



}
