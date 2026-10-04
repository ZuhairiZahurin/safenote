<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        User::factory()->role(User::ROLE_ADMIN)->create();   // a second admin exists

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_TEACHER,
        ])->assertForbidden();

        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);
    }

    public function test_the_last_active_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $other = User::factory()->role(User::ROLE_ADMIN)->create(['is_active' => false]);

        $this->actingAs($admin)->put("/admin/users/{$other->id}", [
            'name' => $other->name,
            'email' => $other->email,
            'role' => User::ROLE_COUNSELLOR,
        ])->assertRedirect();   // inactive admin may be demoted; one active admin remains

        $this->assertSame(User::ROLE_COUNSELLOR, $other->fresh()->role);
    }

    public function test_an_admin_can_still_change_another_users_role(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);

        $this->actingAs($admin)->put("/admin/users/{$teacher->id}", [
            'name' => $teacher->name,
            'email' => $teacher->email,
            'role' => User::ROLE_COUNSELLOR,
        ])->assertRedirect();

        $teacher->refresh();
        $this->assertSame(User::ROLE_COUNSELLOR, $teacher->role);
        $this->assertNull($teacher->class, 'a counsellor should not keep a class');
        $this->assertDatabaseHas('audit_logs', ['action' => 'user_updated']);
    }
}
