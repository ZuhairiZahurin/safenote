<?php

namespace Database\Seeders;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use Database\Factories\CounsellingRecordFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with one account per role so the
     * RBAC model (Admin / Counsellor / Teacher) can be demonstrated immediately,
     * plus a sample caseload so the counsellor dashboard shows real statistics.
     */
    public function run(): void
    {
        User::factory()->role(User::ROLE_ADMIN)->create([
            'name' => 'System Administrator',
            'email' => 'admin@safenote.test',
        ]);

        $counsellor = User::factory()->role(User::ROLE_COUNSELLOR)->create([
            'name' => 'Zaiton binti Ahmad',
            'email' => 'counsellor@safenote.test',
        ]);

        $teacher = User::factory()->role(User::ROLE_TEACHER)->create([
            'name' => 'Ahmad bin Ismail',
            'email' => 'teacher@safenote.test',
            'class' => '4 Amanah',
        ]);

        $students = $this->seedCaseload($counsellor);
        $this->seedReferrals($teacher, $counsellor, $students);
    }

    /**
     * A plausible spread of cases across issue types and the last six months.
     */
    private function seedCaseload(User $counsellor): \Illuminate\Support\Collection
    {
        $students = collect([
            ['name' => 'Nurul Aina binti Rosli', 'class' => '4 Amanah'],
            ['name' => 'Muhammad Haziq bin Farid', 'class' => '4 Amanah'],
            ['name' => 'Tan Wei Ling', 'class' => '3 Bestari'],
            ['name' => 'Arjun a/l Ramesh', 'class' => '3 Bestari'],
            ['name' => 'Siti Khadijah binti Omar', 'class' => '5 Cekal'],
            ['name' => 'Lim Jia Hui', 'class' => '5 Cekal'],
            ['name' => 'Danish Iqbal bin Zulkifli', 'class' => '2 Gigih'],
            ['name' => 'Priya a/p Kumaran', 'class' => '2 Gigih'],
        ])->map(fn ($data) => Student::create($data + ['created_by' => $counsellor->id]));

        // Weighted so the dashboard has a clear leading issue rather than a flat chart.
        $distribution = [
            'attendance' => 7,
            'attitude' => 6,
            'academic_performance' => 5,
            'discipline' => 4,
            // One family case is seeded by the closed referral below, so the
            // caseload still totals 33 records with three family cases.
            'family' => 2,
            'emotional_distress' => 3,
            'bullying' => 2,
            'peer_relationship' => 2,
            'career_guidance' => 1,
        ];

        foreach ($distribution as $issueType => $count) {
            CounsellingRecord::factory()
                ->count($count)
                ->issueType($issueType)
                ->create([
                    'counsellor_id' => $counsellor->id,
                    'student_id' => fn () => $students->random()->id,
                ]);
        }

        return $students;
    }

    /**
     * Referrals raised by the teacher, spread across the workflow states so the
     * inbox and the teacher's status tracking both have something to show.
     */
    private function seedReferrals(User $teacher, User $counsellor, \Illuminate\Support\Collection $students): void
    {
        $referrals = [
            ['issue_type' => 'attendance', 'urgency' => 'high', 'status' => Referral::STATUS_PENDING,
                'notes' => 'Tidak hadir selama enam hari persekolahan bulan ini tanpa sebarang surat daripada rumah. Pelajar kelihatan menyendiri apabila ditanya tentang hal ini.'],
            ['issue_type' => 'attitude', 'urgency' => 'medium', 'status' => Referral::STATUS_PENDING,
                'notes' => 'Semakin kerap melawan cakap dan mencabar guru sepanjang tiga minggu lepas. Ini satu perubahan yang ketara berbanding penggal lepas.'],
            ['issue_type' => 'bullying', 'urgency' => 'high', 'status' => Referral::STATUS_IN_REVIEW,
                'notes' => 'Dilaporkan oleh rakan sekelas bahawa pelajar ini dipinggirkan dan diusik semasa waktu rehat.'],
            ['issue_type' => 'academic_performance', 'urgency' => 'low', 'status' => Referral::STATUS_IN_REVIEW,
                'notes' => 'Keputusan menurun dengan mendadak dalam Matematik dan Sains walaupun hadir ke semua kelas.'],
            ['issue_type' => 'family', 'urgency' => 'medium', 'status' => Referral::STATUS_CLOSED,
                'notes' => 'Pelajar ada menyebut tentang masalah di rumah yang menjejaskan keupayaannya menyiapkan kerja sekolah.'],
        ];

        foreach ($referrals as $index => $data) {
            $referral = Referral::create($data + [
                'student_id' => $students[$index % $students->count()]->id,
                'teacher_id' => $teacher->id,
                'reviewed_by' => $data['status'] === Referral::STATUS_PENDING ? null : $counsellor->id,
                'reviewed_at' => $data['status'] === Referral::STATUS_PENDING ? null : now()->subDays($index),
                'created_at' => now()->subDays(10 - $index),
            ]);

            // The closed referral is closed because it was converted, so the demo
            // shows a completed referral workflow: teacher observation to
            // counselling record, with the two linked.
            if ($referral->status === Referral::STATUS_CLOSED) {
                $this->convert($referral, $counsellor);
            }
        }
    }

    /**
     * Mirrors what ReferralInboxController@convert does when a counsellor turns
     * a referral into a counselling record.
     */
    private function convert(Referral $referral, User $counsellor): void
    {
        $record = CounsellingRecord::create([
            'student_id' => $referral->student_id,
            'counsellor_id' => $counsellor->id,
            'category' => CounsellingRecord::ISSUE_TYPES[$referral->issue_type]['category'],
            'issue_type' => $referral->issue_type,
            'session_date' => $referral->created_at->copy()->addDays(2),
            'content' => CounsellingRecordFactory::noteFor($referral->issue_type),
        ]);

        $referral->update(['counselling_record_id' => $record->id]);
    }
}
