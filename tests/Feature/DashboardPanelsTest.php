<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPanelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_counsellor_sees_referrals_waiting_and_recent_sessions(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);
        $student = Student::factory()->create(['name' => 'Nurul Aina binti Rosli']);

        Referral::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        CounsellingRecord::factory()->create([
            'student_id' => $student->id,
            'counsellor_id' => $counsellor->id,
        ]);

        $response = $this->actingAs($counsellor)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Referrals Waiting');
        $response->assertSee('Recent Sessions');
        $response->assertSee('Nurul Aina binti Rosli');
        $response->assertSee('Waiting on You');
    }

    public function test_the_waiting_count_reflects_pending_referrals_only(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        Referral::factory()->count(2)->create([
            'teacher_id' => $teacher->id,
            'status' => Referral::STATUS_PENDING,
        ]);
        Referral::factory()->create([
            'teacher_id' => $teacher->id,
            'status' => Referral::STATUS_CLOSED,
        ]);

        $this->actingAs($counsellor)->get('/dashboard')
            ->assertViewHas('stats', fn ($stats) => $stats['waitingTotal'] === 2);
    }

    public function test_the_admin_sees_the_account_picture_and_recent_activity(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        User::factory()->role(User::ROLE_COUNSELLOR)->create();
        User::factory()->role(User::ROLE_TEACHER)->create();

        AuditLog::record('user_created', 'Created teacher account for cikgu@safenote.test', $admin->id);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Accounts by Role');
        $response->assertSee('Recent Activity');
        $response->assertSee('Created teacher account');
        $response->assertViewHas('adminStats', fn ($s) => $s['users'] === 3 && $s['active'] === 3);
    }

    /**
     * The Admin dashboard reports on accounts, never on case content — the same
     * boundary the rest of the system holds.
     */
    public function test_the_admin_dashboard_shows_no_counselling_content(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        CounsellingRecord::factory()->create([
            'counsellor_id' => $counsellor->id,
            'content' => 'Pelajar menyatakan rasa tertekan di rumah.',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Pelajar menyatakan rasa tertekan');
        $response->assertDontSee('Cases by Presenting Issue');
    }

    public function test_the_teacher_sees_their_own_recent_referrals_with_status_only(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);
        $student = Student::factory()->create(['name' => 'Tan Wei Ling', 'class' => '4 Amanah']);

        Referral::factory()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => Referral::STATUS_IN_REVIEW,
        ]);

        $response = $this->actingAs($teacher)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Your Recent Referrals');
        $response->assertSee('Tan Wei Ling');
        $response->assertSee('Counselling notes are never shown to teachers.');
    }

    public function test_the_month_on_month_change_is_reported(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        CounsellingRecord::factory()->count(3)->create([
            'counsellor_id' => $counsellor->id,
            'session_date' => now()->startOfMonth(),
        ]);
        CounsellingRecord::factory()->create([
            'counsellor_id' => $counsellor->id,
            'session_date' => now()->subMonthNoOverflow()->startOfMonth(),
        ]);

        $this->actingAs($counsellor)->get('/dashboard')
            ->assertViewHas('stats', fn ($stats) => $stats['thisMonth'] === 3
                && $stats['lastMonth'] === 1
                && $stats['monthChange'] === 2);
    }
}
