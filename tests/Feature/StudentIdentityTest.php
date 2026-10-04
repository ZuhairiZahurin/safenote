<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_second_record_for_the_same_student_reuses_the_student(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        foreach (['First session', 'Second session'] as $note) {
            $this->actingAs($counsellor)->post('/records', [
                'student_name' => 'Nurul Aina binti Rosli',
                'student_class' => '4 Amanah',
                'issue_type' => 'attendance',
                'session_date' => '2026-10-01',
                'content' => $note,
            ])->assertRedirect();
        }

        $this->assertSame(1, Student::count());
        $this->assertSame(2, CounsellingRecord::count());
    }

    public function test_the_same_name_in_a_different_class_is_refused_rather_than_duplicated(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        Student::factory()->create(['name' => 'Nurul Aina binti Rosli', 'class' => '4 Amanah']);

        $this->actingAs($counsellor)->from('/records/create')->post('/records', [
            'student_name' => 'Nurul Aina binti Rosli',
            'student_class' => '5 Cekal',
            'issue_type' => 'attendance',
            'session_date' => '2026-10-01',
            'content' => 'Note',
        ])->assertSessionHasErrors('student_name');

        $this->assertSame(1, Student::count());
        $this->assertSame(0, CounsellingRecord::count());
    }

    public function test_extra_spacing_in_the_name_does_not_create_a_second_student(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        Student::factory()->create(['name' => 'Tan Wei Ling', 'class' => '3 Bestari']);

        $this->actingAs($counsellor)->post('/records', [
            'student_name' => '  Tan   Wei  Ling  ',
            'student_class' => '3 Bestari',
            'issue_type' => 'family',
            'session_date' => '2026-10-01',
            'content' => 'Note',
        ])->assertRedirect();

        $this->assertSame(1, Student::count());
    }

    public function test_a_teacher_referral_is_held_to_the_same_rule(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);
        Student::factory()->create(['name' => 'Lim Jia Hui', 'class' => '5 Cekal']);

        $this->actingAs($teacher)->from('/referrals/create')->post('/referrals', [
            'student_name' => 'Lim Jia Hui',
            'student_class' => '4 Amanah',
            'issue_type' => 'bullying',
            'urgency' => 'high',
            'notes' => 'Observed being excluded at recess.',
        ])->assertSessionHasErrors('student_name');

        $this->assertSame(1, Student::count());
    }
}
