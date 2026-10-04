<?php

namespace Tests\Feature;

use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecordEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_content_is_stored_encrypted_at_rest(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create();

        $plaintext = 'Student disclosed a family conflict during today\'s session.';

        $record = CounsellingRecord::create([
            'student_id' => $student->id,
            'counsellor_id' => $counsellor->id,
            'category' => CounsellingRecord::CATEGORY_EMOTIONAL_WELFARE,
            'session_date' => now(),
            'content' => $plaintext,
        ]);

        $rawColumnValue = DB::table('counselling_records')->find($record->id)->content;

        // The raw DB column must never contain the plaintext...
        $this->assertStringNotContainsString($plaintext, $rawColumnValue);

        // ...but must be a valid Laravel Crypt payload that decrypts back to it.
        $this->assertSame($plaintext, Crypt::decryptString($rawColumnValue));
    }

    public function test_record_content_is_transparently_decrypted_via_the_model(): void
    {
        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create();
        $student = Student::factory()->create();

        $plaintext = 'Follow-up required next week regarding attendance.';

        $record = CounsellingRecord::create([
            'student_id' => $student->id,
            'counsellor_id' => $counsellor->id,
            'category' => CounsellingRecord::CATEGORY_ACADEMIC,
            'session_date' => now(),
            'content' => $plaintext,
        ]);

        $fresh = CounsellingRecord::find($record->id);

        $this->assertSame($plaintext, $fresh->content);
    }
}
