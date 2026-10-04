<?php

namespace Database\Factories;

use App\Models\CounsellingRecord;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'teacher_id' => User::factory()->role(User::ROLE_TEACHER),
            'issue_type' => fake()->randomElement(array_keys(CounsellingRecord::ISSUE_TYPES)),
            'urgency' => fake()->randomElement(array_keys(Referral::URGENCIES)),
            'notes' => fake()->randomElement(self::OBSERVATIONS),
            'status' => Referral::STATUS_PENDING,
        ];
    }

    /**
     * A teacher's own classroom observation, written the way a teacher would
     * write it: what was seen, not what the student disclosed in counselling.
     */
    private const OBSERVATIONS = [
        'Saya perasan pelajar ini semakin kerap menyendiri semasa waktu rehat sejak dua minggu lepas. Kerja rumah juga tidak disiapkan seperti biasa.',
        'Pelajar kelihatan mengantuk dan sukar menumpukan perhatian di dalam kelas pada waktu pagi. Perubahan ini agak ketara berbanding penggal lepas.',
        'Rakan sekelas melaporkan pelajar ini kerap diusik semasa waktu rehat. Saya sendiri pernah melihat pelajar duduk berseorangan di belakang kelas.',
        'Kehadiran pelajar menurun dengan mendadak bulan ini. Setiap kali ditanya, pelajar hanya diam dan mengelak daripada memberi jawapan.',
        'Pelajar menangis selepas waktu kelas tetapi tidak mahu memberitahu puncanya. Saya rasa lebih baik pihak kaunseling yang berbincang dengannya.',
    ];

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
