<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcedPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function heldUser(): User
    {
        $user = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $user->forceFill(['must_change_password' => true])->save();

        return $user;
    }

    public function test_a_user_holding_an_issued_password_is_sent_to_the_change_screen(): void
    {
        $user = $this->heldUser();

        foreach (['/dashboard', '/records', '/profile', '/reports/caseload'] as $path) {
            $this->actingAs($user)->get($path)->assertRedirect(route('password.change'));
        }
    }

    public function test_the_change_screen_itself_is_reachable(): void
    {
        $this->actingAs($this->heldUser())->get('/password/change')
            ->assertOk()
            ->assertSee('Choose your own password');
    }

    public function test_signing_out_is_still_possible_from_the_change_screen(): void
    {
        $this->actingAs($this->heldUser())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_choosing_a_new_password_releases_the_user(): void
    {
        $user = $this->heldUser();

        $this->actingAs($user)->put('/password/change', [
            'current_password' => 'password',
            'password' => 'MyOwnPassword2026!',
            'password_confirmation' => 'MyOwnPassword2026!',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('MyOwnPassword2026!', $user->password));

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_the_change_is_written_to_the_audit_log(): void
    {
        $user = $this->heldUser();

        $this->actingAs($user)->put('/password/change', [
            'current_password' => 'password',
            'password' => 'MyOwnPassword2026!',
            'password_confirmation' => 'MyOwnPassword2026!',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'password_changed',
            'user_id' => $user->id,
        ]);
    }

    public function test_the_issued_password_cannot_simply_be_kept(): void
    {
        $user = $this->heldUser();

        $this->actingAs($user)->put('/password/change', [
            'current_password' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_the_issued_password_must_be_entered_correctly(): void
    {
        $user = $this->heldUser();

        $this->actingAs($user)->put('/password/change', [
            'current_password' => 'not-the-issued-one',
            'password' => 'MyOwnPassword2026!',
            'password_confirmation' => 'MyOwnPassword2026!',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_a_user_with_nothing_to_change_is_sent_on(): void
    {
        $user = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $this->actingAs($user)->get('/password/change')->assertRedirect(route('dashboard'));
    }

    public function test_an_admin_reset_puts_the_user_under_the_obligation(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        $this->actingAs($admin)->patch("/admin/users/{$teacher->id}/reset-password", [
            'password' => 'TempPass2026!',
            'password_confirmation' => 'TempPass2026!',
        ]);

        $this->assertTrue($teacher->fresh()->must_change_password);
        $this->post('/logout');

        $this->post('/login', ['email' => $teacher->email, 'password' => 'TempPass2026!']);
        $this->get('/dashboard')->assertRedirect(route('password.change'));
    }

    public function test_a_newly_created_account_is_under_the_same_obligation(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Cikgu Baharu',
            'email' => 'baharu@safenote.test',
            'role' => User::ROLE_TEACHER,
            'class' => '4 Amanah',
            'password' => 'Starter2026!',
            'password_confirmation' => 'Starter2026!',
        ])->assertRedirect();

        $this->assertTrue(User::where('email', 'baharu@safenote.test')->first()->must_change_password);
    }

    public function test_changing_the_password_from_the_profile_also_settles_it(): void
    {
        $user = $this->heldUser();

        $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'MyOwnPassword2026!',
            'password_confirmation' => 'MyOwnPassword2026!',
        ]);

        $this->assertFalse($user->fresh()->must_change_password);
    }
}
