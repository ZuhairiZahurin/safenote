<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordsListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_head_reports_the_size_of_the_caseload(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        CounsellingRecord::factory()->count(3)->create(['counsellor_id' => $counsellor->id]);

        $response = $this->actingAs($counsellor)->get('/records');

        $response->assertOk();
        $response->assertSee('Student Counselling Records');
        $response->assertViewHas('caseloadTotal', 3);
    }

    public function test_a_filtered_list_says_what_it_is_a_subset_of(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $wanted = Student::factory()->create(['name' => 'Tan Wei Ling']);
        $other = Student::factory()->create(['name' => 'Arjun a/l Ramesh']);

        CounsellingRecord::factory()->create(['counsellor_id' => $counsellor->id, 'student_id' => $wanted->id]);
        CounsellingRecord::factory()->count(4)->create(['counsellor_id' => $counsellor->id, 'student_id' => $other->id]);

        $response = $this->actingAs($counsellor)->get('/records?search=Tan');

        $response->assertOk();
        $response->assertSee('matching');
        $response->assertSee('in the caseload');
        // The whole caseload is still reported, not just the filtered slice.
        $response->assertViewHas('caseloadTotal', 5);
        $response->assertSee('Tan Wei Ling');
        $response->assertDontSee('Arjun a/l Ramesh');
    }

    public function test_an_empty_result_explains_that_the_filter_caused_it(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        CounsellingRecord::factory()->create(['counsellor_id' => $counsellor->id]);

        $this->actingAs($counsellor)->get('/records?search=nobody-by-this-name')
            ->assertOk()
            ->assertSee('Nothing matches those filters.');
    }

    public function test_an_empty_caseload_says_something_different(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();

        $this->actingAs($counsellor)->get('/records')
            ->assertOk()
            ->assertSee('No records yet.');
    }

    public function test_the_record_page_shows_the_note_and_its_handling_warning(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $record = CounsellingRecord::factory()->create([
            'counsellor_id' => $counsellor->id,
            'content' => 'Sesi bersemuka di Bilik Kaunseling.',
        ]);

        $response = $this->actingAs($counsellor)->get("/records/{$record->id}");

        $response->assertOk();
        $response->assertSee('Session Notes');
        $response->assertSee('Sesi bersemuka di Bilik Kaunseling.');
        $response->assertSee('encrypted at rest');
        $response->assertSee('recorded in the audit log');
    }
}
