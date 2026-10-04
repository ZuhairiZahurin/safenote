<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_teacher_can_refer_a_student(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        $this->actingAs($teacher)->post('/referrals', [
            'student_name' => 'Nur Aisyah',
            'student_class' => '2 Bestari',
            'issue_type' => 'attendance',
            'urgency' => 'high',
            'notes' => 'Absent for five consecutive days.',
        ])->assertRedirect(route('referrals.index'));

        $this->assertDatabaseHas('referrals', [
            'teacher_id' => $teacher->id,
            'issue_type' => 'attendance',
            'urgency' => 'high',
            'status' => Referral::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'referral_created']);
    }

    public function test_referral_notes_are_encrypted_at_rest(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();
        $plaintext = 'Student disclosed being bullied during recess.';

        $referral = Referral::factory()->create([
            'teacher_id' => $teacher->id,
            'notes' => $plaintext,
        ]);

        $raw = DB::table('referrals')->find($referral->id)->notes;

        $this->assertStringNotContainsString($plaintext, $raw);
        $this->assertSame($plaintext, Crypt::decryptString($raw));
    }

    public function test_a_teacher_only_sees_their_own_referrals(): void
    {
        $mine = User::factory()->role(User::ROLE_TEACHER)->create();
        $other = User::factory()->role(User::ROLE_TEACHER)->create();

        $mineStudent = Student::factory()->create(['name' => 'My Referred Student']);
        $otherStudent = Student::factory()->create(['name' => 'Someone Elses Student']);

        Referral::factory()->create(['teacher_id' => $mine->id, 'student_id' => $mineStudent->id]);
        $theirs = Referral::factory()->create(['teacher_id' => $other->id, 'student_id' => $otherStudent->id]);

        $response = $this->actingAs($mine)->get('/referrals');
        $response->assertOk();
        $response->assertSee('My Referred Student');
        $response->assertDontSee('Someone Elses Student');

        // And cannot open another teacher's referral directly.
        $this->actingAs($mine)->get("/referrals/{$theirs->id}")->assertForbidden();
    }

    public function test_counsellor_can_review_and_convert_a_referral_into_a_record(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $referral = Referral::factory()->create([
            'teacher_id' => $teacher->id,
            'issue_type' => 'bullying',
        ]);

        $this->actingAs($counsellor)
            ->post("/referral-inbox/{$referral->id}/convert", [
                'session_date' => now()->toDateString(),
                'content' => 'Met the student; safety plan agreed.',
            ])->assertRedirect();

        $referral->refresh();

        $this->assertSame(Referral::STATUS_CLOSED, $referral->status);
        $this->assertNotNull($referral->counselling_record_id);

        $record = CounsellingRecord::find($referral->counselling_record_id);
        $this->assertSame('bullying', $record->issue_type);
        // Category is derived from the issue type, keeping the two coherent.
        $this->assertSame(CounsellingRecord::CATEGORY_BEHAVIOURAL, $record->category);
        $this->assertSame($counsellor->id, $record->counsellor_id);
    }

    /**
     * The core confidentiality guarantee of the referral workflow: information
     * flows teacher -> counsellor only. The teacher learns the STATUS of their
     * referral but never the counselling notes written as a result of it.
     */
    public function test_teacher_cannot_see_counselling_notes_arising_from_their_referral(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $referral = Referral::factory()->create(['teacher_id' => $teacher->id]);

        $confidential = 'CONFIDENTIAL counselling note the teacher must never read.';

        $this->actingAs($counsellor)->post("/referral-inbox/{$referral->id}/convert", [
            'session_date' => now()->toDateString(),
            'content' => $confidential,
        ])->assertRedirect();

        $referral->refresh();

        // The teacher sees the status change...
        $response = $this->actingAs($teacher)->get("/referrals/{$referral->id}");
        $response->assertOk();
        $response->assertSee('Closed');

        // ...but never the counselling content.
        $response->assertDontSee($confidential);

        // And is blocked from the counselling record and the inbox outright.
        $this->actingAs($teacher)->get("/records/{$referral->counselling_record_id}")->assertForbidden();
        $this->actingAs($teacher)->get('/referral-inbox')->assertForbidden();
    }

    public function test_counsellor_cannot_submit_referrals_and_teacher_cannot_access_inbox(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->actingAs($counsellor)->get('/referrals')->assertForbidden();
        $this->actingAs($teacher)->get('/referral-inbox')->assertForbidden();
        $this->actingAs($admin)->get('/referrals')->assertForbidden();
        $this->actingAs($admin)->get('/referral-inbox')->assertForbidden();
    }

    public function test_status_updates_are_audit_logged(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $referral = Referral::factory()->create();

        $this->actingAs($counsellor)
            ->patch("/referral-inbox/{$referral->id}/status", ['status' => Referral::STATUS_IN_REVIEW])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'referral_status_changed']);
        $this->assertSame(Referral::STATUS_IN_REVIEW, $referral->fresh()->status);
    }

    public function test_teacher_dashboard_reports_their_referral_statistics(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        Referral::factory()->count(3)->create(['teacher_id' => $teacher->id, 'issue_type' => 'attendance']);
        Referral::factory()->status(Referral::STATUS_CLOSED)->create(['teacher_id' => $teacher->id]);
        Referral::factory()->create(['teacher_id' => User::factory()->role(User::ROLE_TEACHER)]);

        $response = $this->actingAs($teacher)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Referrals Made');

        $stats = $response->viewData('teacherStats');

        $this->assertSame(4, $stats['total'], 'Only the teacher\'s own referrals count.');
        $this->assertSame(3, $stats['pending']);
        $this->assertSame(1, $stats['closed']);
    }
}
