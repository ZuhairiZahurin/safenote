<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_record_writes_an_audit_log_entry(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $this->actingAs($counsellor)->post('/records', [
            'student_name' => 'Nur Aisyah',
            'student_class' => '2 Bestari',
            'issue_type' => 'academic_performance',
            'session_date' => now()->toDateString(),
            'content' => 'Discussed exam preparation strategies.',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $counsellor->id,
            'action' => 'record_created',
        ]);
    }

    public function test_updating_and_deleting_a_record_writes_audit_log_entries(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create();

        $record = \App\Models\CounsellingRecord::create([
            'student_id' => $student->id,
            'counsellor_id' => $counsellor->id,
            'category' => 'academic',
            'session_date' => now(),
            'content' => 'Initial note.',
        ]);

        $this->actingAs($counsellor)->put("/records/{$record->id}", [
            'issue_type' => 'attitude',
            'session_date' => now()->toDateString(),
            'content' => 'Updated note.',
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'record_updated']);

        $this->actingAs($counsellor)->delete("/records/{$record->id}")->assertRedirect();

        $this->assertDatabaseHas('audit_logs', ['action' => 'record_deleted']);
        $this->assertDatabaseMissing('counselling_records', ['id' => $record->id]);
    }

    public function test_a_counsellor_cannot_edit_another_counsellors_record(): void
    {
        $owner = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $intruder = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create();

        $record = \App\Models\CounsellingRecord::create([
            'student_id' => $student->id,
            'counsellor_id' => $owner->id,
            'category' => 'academic',
            'session_date' => now(),
            'content' => 'Owned by the first counsellor.',
        ]);

        $this->actingAs($intruder)->get("/records/{$record->id}")->assertForbidden();
        $this->actingAs($intruder)->put("/records/{$record->id}", [
            'issue_type' => 'academic_performance',
            'session_date' => now()->toDateString(),
            'content' => 'Tampered.',
        ])->assertForbidden();
    }
}
