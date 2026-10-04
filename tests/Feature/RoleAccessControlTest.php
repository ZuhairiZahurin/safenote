<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_counsellor_can_access_records_but_not_admin_or_teacher_areas(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $this->actingAs($counsellor)->get('/records')->assertOk();
        $this->actingAs($counsellor)->get('/students')->assertForbidden();
        $this->actingAs($counsellor)->get('/admin/users')->assertForbidden();
    }

    public function test_teacher_can_view_student_profiles_but_not_records_or_admin_areas(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create();

        $this->actingAs($teacher)->get('/students')->assertOk();
        $this->actingAs($teacher)->get('/records')->assertForbidden();
        $this->actingAs($teacher)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_access_admin_areas_but_not_records_or_student_areas(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/audit-log')->assertOk();
        $this->actingAs($admin)->get('/records')->assertForbidden();
        $this->actingAs($admin)->get('/students')->assertForbidden();
    }

    public function test_teacher_cannot_view_confidential_record_content(): void
    {
        // The teacher's student-profile page must never leak counselling
        // session content, even when a record exists for that student.
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create(['class' => '4 Amanah']);

        $secretNote = 'CONFIDENTIAL: disclosed self-harm ideation during session.';
        CounsellingRecord::create([
            'student_id' => $student->id,
            'counsellor_id' => $counsellor->id,
            'category' => CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE,
            'session_date' => now(),
            'content' => $secretNote,
        ]);

        $response = $this->actingAs($teacher)->get("/students/{$student->id}");

        $response->assertOk();
        $response->assertDontSee($secretNote);
    }

    public function test_teacher_only_sees_students_of_their_own_class(): void
    {
        // Listing students outside the teacher's class would reveal who has
        // been in contact with the counselling unit.
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => '4 Amanah']);

        $ownClass = Student::factory()->create(['name' => 'Own Class Pupil', 'class' => '4 Amanah']);
        $otherClass = Student::factory()->create(['name' => 'Other Class Pupil', 'class' => '5 Cekal']);
        CounsellingRecord::factory()->create(['student_id' => $otherClass->id]);

        $this->actingAs($teacher)->get('/students')
            ->assertOk()
            ->assertSee('Own Class Pupil')
            ->assertDontSee('Other Class Pupil');

        $this->actingAs($teacher)->get("/students/{$ownClass->id}")->assertOk();
        $this->actingAs($teacher)->get("/students/{$otherClass->id}")->assertNotFound();
    }

    public function test_teacher_without_a_class_sees_no_student_profiles(): void
    {
        $teacher = User::factory()->role(User::ROLE_TEACHER)->create(['class' => null]);
        $student = Student::factory()->create(['name' => 'Some Pupil', 'class' => '4 Amanah']);

        $this->actingAs($teacher)->get('/students')
            ->assertOk()
            ->assertDontSee('Some Pupil');

        $this->actingAs($teacher)->get("/students/{$student->id}")->assertNotFound();
    }

    public function test_a_class_outside_the_school_list_is_rejected(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->actingAs($admin)->from('/admin/users/create')->post('/admin/users', [
            'name' => 'Cikgu Salmah', 'email' => 'salmah@safenote.test',
            'role' => User::ROLE_TEACHER, 'class' => '6 Cemerlang',   // not a class at this school
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('class');

        $this->assertDatabaseMissing('users', ['email' => 'salmah@safenote.test']);
    }

    public function test_admin_assigns_a_class_only_to_teacher_accounts(): void
    {
        $admin = User::factory()->role(User::ROLE_ADMIN)->create();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Cikgu Aminah', 'email' => 'aminah@safenote.test',
            'role' => User::ROLE_TEACHER, 'class' => '3 Bestari',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertSame('3 Bestari', User::where('email', 'aminah@safenote.test')->value('class'));

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Pn Zaiton', 'email' => 'zaiton@safenote.test',
            'role' => User::ROLE_COUNSELLOR, 'class' => '3 Bestari',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertNull(User::where('email', 'zaiton@safenote.test')->value('class'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/records')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }
}
