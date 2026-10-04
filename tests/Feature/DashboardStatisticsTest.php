<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_counsellor_dashboard_reports_their_caseload_by_issue_type(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create();

        CounsellingRecord::factory()->count(3)->issueType('attendance')->create([
            'counsellor_id' => $counsellor->id,
            'student_id' => $student->id,
        ]);
        CounsellingRecord::factory()->count(1)->issueType('attitude')->create([
            'counsellor_id' => $counsellor->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($counsellor)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Cases by Presenting Issue');
        $response->assertSee('Attendance / Truancy');
        $response->assertSee('Attitude &amp; Conduct', false);

        $stats = $response->viewData('stats');

        $this->assertSame(4, $stats['total']);
        $this->assertSame(1, $stats['students']);
        $this->assertSame('Attendance / Truancy', $stats['topIssue']['label']);
        $this->assertSame(3, $stats['topIssue']['count']);
    }

    public function test_dashboard_statistics_exclude_other_counsellors_records(): void
    {
        $mine = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $theirs = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        CounsellingRecord::factory()->count(2)->issueType('bullying')->create(['counsellor_id' => $mine->id]);
        CounsellingRecord::factory()->count(5)->issueType('bullying')->create(['counsellor_id' => $theirs->id]);

        $stats = $this->actingAs($mine)->get('/dashboard')->viewData('stats');

        $this->assertSame(2, $stats['total'], 'A counsellor must only see their own caseload.');
    }

    public function test_category_totals_are_derived_from_issue_types(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        // attendance + academic_performance are both Academic category.
        CounsellingRecord::factory()->issueType('attendance')->create(['counsellor_id' => $counsellor->id]);
        CounsellingRecord::factory()->issueType('academic_performance')->create(['counsellor_id' => $counsellor->id]);
        CounsellingRecord::factory()->issueType('bullying')->create(['counsellor_id' => $counsellor->id]);

        $stats = $this->actingAs($counsellor)->get('/dashboard')->viewData('stats');

        $byCategory = collect($stats['byCategory'])->keyBy('key');

        $this->assertSame(2, $byCategory[CounsellingRecord::CATEGORY_ACADEMIC]['count']);
        $this->assertSame(1, $byCategory[CounsellingRecord::CATEGORY_BEHAVIOURAL]['count']);
        $this->assertSame(0, $byCategory[CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE]['count']);
    }

    public function test_counsellor_with_no_records_sees_an_empty_state_not_statistics(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $response = $this->actingAs($counsellor)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('No counselling records yet.', false);
        $response->assertDontSee('Cases by Presenting Issue');
    }

    public function test_teacher_and_admin_do_not_receive_counsellor_statistics(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->assertNull($this->actingAs($teacher)->get('/dashboard')->viewData('stats'));
        $this->assertNull($this->actingAs($admin)->get('/dashboard')->viewData('stats'));
    }

    public function test_records_can_be_filtered_by_issue_type(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $bully = Student::factory()->create(['name' => 'Bullying Case Student']);
        $attend = Student::factory()->create(['name' => 'Attendance Case Student']);

        CounsellingRecord::factory()->issueType('bullying')->create([
            'counsellor_id' => $counsellor->id,
            'student_id' => $bully->id,
        ]);
        CounsellingRecord::factory()->issueType('attendance')->create([
            'counsellor_id' => $counsellor->id,
            'student_id' => $attend->id,
        ]);

        $response = $this->actingAs($counsellor)->get('/records?issue_type=bullying');

        $response->assertOk();
        $response->assertSee('Bullying Case Student');
        $response->assertDontSee('Attendance Case Student');
    }
}
