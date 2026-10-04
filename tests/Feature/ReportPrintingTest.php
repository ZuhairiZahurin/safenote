<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPrintingTest extends TestCase
{
    use RefreshDatabase;

    public function test_counsellor_can_print_their_own_record_and_it_is_audit_logged(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $record = CounsellingRecord::factory()->create([
            'counsellor_id' => $counsellor->id,
            'content' => 'Session notes for the physical file.',
        ]);

        $this->actingAs($counsellor)->get("/records/{$record->id}/print")
            ->assertOk()
            ->assertSee('SULIT / CONFIDENTIAL')
            ->assertSee('Session notes for the physical file.')
            ->assertSee($record->student->name);

        $this->assertDatabaseHas('audit_logs', ['user_id' => $counsellor->id, 'action' => 'record_printed']);
    }

    public function test_counsellor_cannot_print_another_counsellors_record(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $record = CounsellingRecord::factory()->create();

        $this->actingAs($counsellor)->get("/records/{$record->id}/print")->assertForbidden();
        $this->assertDatabaseMissing('audit_logs', ['action' => 'record_printed']);
    }

    public function test_caseload_report_counts_only_own_records_within_the_period(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        CounsellingRecord::factory()->count(3)->issueType('bullying')->create([
            'counsellor_id' => $counsellor->id,
            'session_date' => '2026-03-10',
        ]);
        CounsellingRecord::factory()->issueType('attendance')->create([
            'counsellor_id' => $counsellor->id,
            'session_date' => '2026-03-20',
        ]);
        // Outside the period, and another counsellor's caseload: both excluded.
        CounsellingRecord::factory()->create(['counsellor_id' => $counsellor->id, 'session_date' => '2026-04-01']);
        CounsellingRecord::factory()->count(5)->create(['session_date' => '2026-03-15']);

        $response = $this->actingAs($counsellor)->get('/reports/caseload?from=2026-03-01&to=2026-03-31');

        $response->assertOk()->assertSee('SULIT / CONFIDENTIAL');
        $this->assertSame(4, $response->viewData('total'));
        $this->assertSame(3, $response->viewData('byIssue')->firstWhere('label', 'Bullying')['count']);
        $this->assertSame(1, $response->viewData('byIssue')->firstWhere('label', 'Attendance / Truancy')['count']);

        $this->assertDatabaseHas('audit_logs', ['user_id' => $counsellor->id, 'action' => 'report_generated']);
    }

    public function test_caseload_report_never_includes_notes_and_hides_names_by_default(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create(['name' => 'Nurul Aina binti Rosli']);
        CounsellingRecord::factory()->create([
            'counsellor_id' => $counsellor->id,
            'student_id' => $student->id,
            'session_date' => now(),
            'content' => 'CONFIDENTIAL disclosure made in session.',
        ]);

        $this->actingAs($counsellor)->get('/reports/caseload')
            ->assertOk()
            ->assertDontSee('CONFIDENTIAL disclosure made in session.')
            ->assertDontSee('Nurul Aina binti Rosli')
            ->assertSee("Student #{$student->id}");

        $this->actingAs($counsellor)->get('/reports/caseload?names=1')
            ->assertOk()
            ->assertSee('Nurul Aina binti Rosli')
            ->assertDontSee('CONFIDENTIAL disclosure made in session.');
    }

    public function test_report_rejects_an_end_date_before_the_start_date(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $this->actingAs($counsellor)->get('/reports/caseload?from=2026-03-31&to=2026-03-01')
            ->assertSessionHasErrors('to');
    }

    public function test_teachers_and_admins_cannot_print_records_or_reports(): void
    {
        $record = CounsellingRecord::factory()->create();

        foreach ([User::ROLE_TEACHER, User::ROLE_ADMIN] as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)->get('/reports/caseload')->assertForbidden();
            $this->actingAs($user)->get("/records/{$record->id}/print")->assertForbidden();
        }
    }
}
