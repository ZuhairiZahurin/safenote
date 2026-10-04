<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The default 'array' session driver used in tests does not persist
        // between separate simulated requests, which would make this
        // cross-request timeout check vacuous. 'database' persists against
        // the in-memory SQLite connection RefreshDatabase already migrates.
        config(['session.driver' => 'database']);
    }

    public function test_session_stays_active_within_the_timeout_window(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // First request establishes last_activity_at in the real session.
        $this->get('/dashboard')->assertOk();

        $this->travel(5)->minutes();

        $this->get('/dashboard')->assertOk();
        $this->assertAuthenticated();
    }

    public function test_idle_session_beyond_the_timeout_is_logged_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/dashboard')->assertOk();

        $this->travel(config('session.timeout_minutes') + 1)->minutes();

        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}
