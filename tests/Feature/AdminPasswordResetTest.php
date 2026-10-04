<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function issue(User $admin, User $target, string $password = 'TempPass2026!'): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($admin)->patch("/admin/users/{$target->id}/reset-password", [
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    }

    public function test_an_admin_can_issue_a_new_password(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);

        $this->issue($admin, $teacher)->assertRedirect();
        $this->post('/logout');

        $this->post('/login', ['email' => $teacher->email, 'password' => 'TempPass2026!'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($teacher->fresh());
    }

    public function test_the_previous_password_stops_working(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        $this->issue($admin, $teacher);
        $this->post('/logout');

        $this->post('/login', ['email' => $teacher->email, 'password' => 'password'])
            ->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_issuing_a_password_also_unlocks_the_account(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $locked = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $locked->forceFill(['failed_login_attempts' => 5, 'locked_until' => now()->addMinutes(15)])->save();

        $this->issue($admin, $locked);

        $locked->refresh();
        $this->assertSame(0, $locked->failed_login_attempts);
        $this->assertNull($locked->locked_until);
    }

    public function test_any_session_still_signed_in_as_that_user_is_ended(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        foreach (['stale-session-a', 'stale-session-b'] as $id) {
            DB::table('sessions')->insert([
                'id' => $id,
                'user_id' => $teacher->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => 'x',
                'last_activity' => now()->timestamp,
            ]);
        }

        $this->issue($admin, $teacher);

        $this->assertSame(0, DB::table('sessions')->where('user_id', $teacher->id)->count());
    }

    public function test_the_reset_is_written_to_the_audit_log(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        $this->issue($admin, $teacher);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'password_reset_by_admin',
            'user_id' => $admin->id,
        ]);
    }

    public function test_an_admin_cannot_reset_their_own_password_here(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->issue($admin, $admin)->assertForbidden();
        $this->post('/logout');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    public function test_a_short_password_is_refused(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        $this->issue($admin, $teacher, 'abc')->assertSessionHasErrors('password');
    }

    public function test_only_an_admin_may_issue_passwords(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        foreach ([$teacher, $counsellor] as $actor) {
            $this->actingAs($actor)->patch("/admin/users/{$teacher->id}/reset-password", [
                'password' => 'TempPass2026!',
                'password_confirmation' => 'TempPass2026!',
            ])->assertForbidden();
        }
        $this->post('/logout');

        $this->post('/login', ['email' => $teacher->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }
}
