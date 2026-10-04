<?php

namespace Tests\Feature;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BruteForceLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_locks_after_five_consecutive_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
            $this->assertGuest();
        }

        $user->refresh();
        $this->assertSame(4, $user->failed_login_attempts);
        $this->assertFalse($user->isLocked());

        // 5th failed attempt triggers the lock.
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $user->refresh();
        $this->assertSame(5, $user->failed_login_attempts);
        $this->assertTrue($user->isLocked());

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'account_locked',
        ]);
    }

    public function test_locked_account_cannot_log_in_even_with_correct_password(): void
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addYears(10),
        ]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_unlock_restores_login_access(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $lockedUser = User::factory()->create([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addYears(10),
        ]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$lockedUser->id}/unlock")
            ->assertRedirect();

        $lockedUser->refresh();
        $this->assertFalse($lockedUser->isLocked());
        $this->assertSame(0, $lockedUser->failed_login_attempts);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'account_unlocked',
        ]);

        // The account can now authenticate normally again (as a fresh,
        // unauthenticated client — the admin session above must not leak in).
        $this->post('/logout');
        $this->post('/login', ['email' => $lockedUser->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($lockedUser);
    }

    public function test_successful_login_resets_failed_attempt_counter(): void
    {
        $user = User::factory()->create(['failed_login_attempts' => 3]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'login']);
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_lock_expires_automatically_after_the_lockout_period(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }
        $this->assertTrue($user->refresh()->isLocked());

        $this->travel(LoginRequest::LOCKOUT_MINUTES - 1)->minutes();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertGuest();

        $this->travel(2)->minutes();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_failure_after_the_lock_expires_relocks_immediately(): void
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 5,
            'locked_until' => now()->subMinute(),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $this->assertTrue($user->refresh()->isLocked());
    }

    public function test_login_errors_do_not_reveal_whether_an_account_exists_or_its_state(): void
    {
        $active = User::factory()->create();
        $locked = User::factory()->create(['failed_login_attempts' => 5, 'locked_until' => now()->addMinutes(10)]);
        $inactive = User::factory()->create(['is_active' => false]);

        $attempts = [
            ['email' => 'nobody@safenote.test', 'password' => 'password'],
            ['email' => $active->email, 'password' => 'wrong'],
            ['email' => $locked->email, 'password' => 'password'],
            ['email' => $inactive->email, 'password' => 'password'],
        ];

        foreach ($attempts as $credentials) {
            $this->post('/login', $credentials)
                ->assertSessionHasErrors(['email' => LoginRequest::FAILED_MESSAGE]);
            $this->assertGuest();
        }
    }

    public function test_repeated_failures_from_one_ip_are_throttled_across_accounts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 20; $i++) {
            $this->post('/login', ['email' => "guess{$i}@safenote.test", 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
