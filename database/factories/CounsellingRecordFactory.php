<?php

namespace Database\Factories;

use App\Models\CounsellingRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CounsellingRecord>
 */
class CounsellingRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueType = fake()->randomElement(array_keys(CounsellingRecord::ISSUE_TYPES));

        return [
            'student_id' => Student::factory(),
            'counsellor_id' => User::factory()->role(User::ROLE_COUNSELLOR),
            // Category always matches the issue type's parent so the two stay coherent.
            'category' => CounsellingRecord::ISSUE_TYPES[$issueType]['category'],
            'issue_type' => $issueType,
            'session_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Session notes are written after any states have been applied, so the note
     * always describes the issue type the record actually ends up with. Notes
     * passed in explicitly (as the tests do) are left untouched.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (CounsellingRecord $record) {
            if (blank($record->content)) {
                $record->content = self::noteFor($record->issue_type);
            }
        });
    }

    /**
     * Demonstration session notes in Bahasa Melayu — the language a Malaysian
     * school counsellor writes in — structured the way a counselling record is
     * kept: presenting issue, observation, intervention, follow-up.
     */
    public static function noteFor(?string $issueType): string
    {
        $note = fake()->randomElement(self::NOTES[$issueType] ?? self::NOTES['other']);
        $mode = fake()->randomElement(['Sesi bersemuka', 'Sesi bersemuka', 'Sesi susulan bersemuka']);
        $minutes = fake()->randomElement([25, 30, 35, 40, 45]);

        return "{$mode} di Bilik Kaunseling ({$minutes} minit).\n\n{$note}";
    }

    private const NOTES = [
        'attendance' => [
            "Isu: Tidak hadir ke sekolah selama enam hari pada bulan ini tanpa surat daripada ibu bapa.\n\nPemerhatian: Pelajar kelihatan letih dan kurang bertenaga. Menyatakan sukar bangun pagi kerana membantu menjaga adik sebelum ke sekolah. Tiada tanda tekanan daripada rakan sebaya.\n\nTindakan: Pendekatan mendengar aktif digunakan untuk memahami rutin harian pelajar. Bersama-sama menyusun semula jadual waktu tidur dan persediaan malam. Pelajar bersetuju merekod kehadiran sendiri selama dua minggu.\n\nSusulan: Temu janji semula dalam dua minggu. Guru kelas dimaklumkan supaya memantau kehadiran sahaja, tanpa didedahkan kandungan sesi ini.",
            "Isu: Corak ponteng pada waktu petang, terutamanya pada hari Isnin dan Khamis.\n\nPemerhatian: Pelajar mengakui meninggalkan kelas kerana rasa tertinggal dalam subjek tertentu dan malu bertanya di hadapan rakan. Hubungan dengan keluarga dilaporkan baik.\n\nTindakan: Meneroka punca rasa rendah diri dalam pembelajaran. Pelajar dibimbing menetapkan satu sasaran kecil setiap minggu dan berlatih cara meminta bantuan guru secara peribadi.\n\nSusulan: Pantau kehadiran waktu petang selama tiga minggu. Cadangan kelas bimbingan tambahan akan dibincangkan pada sesi akan datang.",
        ],
        'academic_performance' => [
            "Isu: Kemerosotan markah yang ketara dalam Matematik dan Sains berbanding penggal lepas.\n\nPemerhatian: Pelajar hadir ke semua kelas tetapi mengaku sukar menumpukan perhatian ketika belajar di rumah. Berkongsi bahawa masa belajar terganggu kerana membantu perniagaan kecil keluarga pada waktu malam.\n\nTindakan: Membincangkan teknik pengurusan masa dan kaedah ulang kaji berkala. Pelajar bersetuju mencuba jadual ulang kaji 30 minit setiap hari sebelum waktu membantu keluarga.\n\nSusulan: Semak semula selepas ujian bulanan. Kemajuan dinilai berdasarkan keyakinan pelajar, bukan markah semata-mata.",
            "Isu: Pelajar datang sendiri untuk berbincang tentang tekanan mencapai sasaran keputusan yang ditetapkan keluarga.\n\nPemerhatian: Pelajar menunjukkan motivasi tinggi tetapi meletakkan jangkaan yang tidak realistik terhadap diri sendiri. Kerap mengulang ungkapan 'tidak cukup bagus'.\n\nTindakan: Sesi memberi fokus kepada penstrukturan semula pemikiran negatif dan penetapan matlamat yang boleh dicapai. Pelajar menyenaraikan tiga kekuatan diri.\n\nSusulan: Sesi seterusnya dalam dua minggu untuk menilai perubahan cara pelajar menilai pencapaian sendiri.",
        ],
        'career_guidance' => [
            "Isu: Pelajar keliru memilih aliran dan haluan kerjaya selepas SPM.\n\nPemerhatian: Berminat dalam bidang teknikal tetapi bimbang tidak mendapat sokongan keluarga. Belum mempunyai maklumat mencukupi tentang laluan kemasukan.\n\nTindakan: Meneroka minat, nilai diri dan kekuatan akademik pelajar. Maklumat tentang laluan politeknik, matrikulasi dan kolej vokasional diberikan.\n\nSusulan: Pelajar akan menyenaraikan tiga pilihan utama sebelum sesi seterusnya. Sesi bersama ibu bapa dicadangkan jika pelajar bersetuju.",
        ],
        'attitude' => [
            "Isu: Perubahan tingkah laku di dalam kelas, kerap menjawab guru sejak tiga minggu lepas.\n\nPemerhatian: Pelajar defensif pada awal sesi, kemudian berkongsi rasa marah kerana merasakan dirinya sering disalahkan berbanding rakan lain. Tiada tanda konflik di rumah.\n\nTindakan: Membimbing pelajar mengenal pasti pencetus kemarahan dan cara bertindak balas yang lebih sesuai. Berlatih teknik bertenang sebelum memberi respons.\n\nSusulan: Pemantauan bersama guru kelas selama dua minggu. Pelajar bersetuju berjumpa semula untuk menilai perubahan.",
            "Isu: Rujukan guru berkaitan sikap kurang menghormati semasa aktiviti berkumpulan.\n\nPemerhatian: Pelajar menunjukkan kesedaran terhadap kesan tingkah lakunya apabila dibincangkan secara tenang. Mengakui ingin menarik perhatian rakan sekelas.\n\nTindakan: Membincangkan perbezaan antara keinginan diterima dan cara mendapatkannya. Pelajar menyenaraikan dua tingkah laku yang ingin diubah.\n\nSusulan: Semakan semula dalam tiga minggu bersama maklum balas guru kelas.",
        ],
        'discipline' => [
            "Isu: Rujukan daripada unit disiplin berhubung kes merokok di kawasan sekolah.\n\nPemerhatian: Pelajar mengakui perbuatan dan menyatakan ia berlaku kerana pengaruh rakan di luar sekolah. Menunjukkan kesediaan untuk berubah.\n\nTindakan: Sesi memberi fokus kepada kesan kesihatan dan kemahiran menolak tekanan rakan sebaya. Berlatih ayat penolakan yang sesuai digunakan.\n\nSusulan: Tiga sesi susulan dipersetujui. Perkembangan dimaklumkan kepada unit disiplin dalam bentuk status sahaja.",
            "Isu: Terlibat dalam pergaduhan kecil semasa waktu rehat.\n\nPemerhatian: Pelajar menyesali tindakannya dan menyatakan tidak tahu cara mengawal kemarahan ketika diprovokasi.\n\nTindakan: Membimbing pelajar mengenal pasti tanda awal kemarahan dan langkah bertenang. Perjanjian tingkah laku ditandatangani bersama.\n\nSusulan: Sesi seterusnya minggu hadapan, dengan pemantauan oleh guru bertugas.",
        ],
        'bullying' => [
            "Isu: Pelajar dilaporkan dipinggirkan dan diusik oleh sekumpulan rakan sekelas semasa waktu rehat.\n\nPemerhatian: Pelajar kelihatan cemas dan teragak-agak untuk bercerita pada mulanya. Mengesahkan kejadian berlaku sejak sebulan lalu dan mula menjejaskan keinginan untuk ke sekolah.\n\nTindakan: Keselamatan pelajar diutamakan. Pelajar diyakinkan bahawa perkara ini bukan salahnya. Pelan keselamatan dirangka bersama: tempat selamat pada waktu rehat dan senarai orang dewasa yang boleh dirujuk.\n\nSusulan: Sesi mingguan selama sebulan. Kes dimaklumkan kepada guru disiplin mengikut prosedur sekolah, dengan pengetahuan pelajar.",
            "Isu: Pelajar menerima mesej mengganggu dalam kumpulan media sosial kelas.\n\nPemerhatian: Pelajar menunjukkan tanda tekanan emosi dan gangguan tidur. Bimbang keadaan bertambah buruk sekiranya kejadian dilaporkan.\n\nTindakan: Membimbing pelajar menyimpan bukti tangkap layar dan menetapkan semula tetapan privasi. Membincangkan pilihan tindakan serta sokongan yang ada.\n\nSusulan: Pemantauan rapat dalam tempoh dua minggu. Ibu bapa akan dihubungi dengan persetujuan pelajar.",
        ],
        'peer_relationship' => [
            "Isu: Kesukaran menyesuaikan diri dengan kelas baharu selepas bertukar sekolah.\n\nPemerhatian: Pelajar menyatakan rasa sunyi ketika waktu rehat dan sukar memulakan perbualan. Tiada tanda buli dikenal pasti.\n\nTindakan: Berlatih kemahiran sosial asas seperti memulakan perbualan dan menyertai aktiviti kumpulan. Pelajar dicadangkan menyertai satu kelab.\n\nSusulan: Semakan semula dalam tiga minggu untuk menilai perkembangan hubungan rakan sebaya.",
            "Isu: Konflik dengan rakan rapat menyebabkan pelajar berasa tersisih.\n\nPemerhatian: Pelajar meluahkan rasa kecewa dan keliru, namun menunjukkan kematangan apabila membincangkan sudut pandangan rakannya.\n\nTindakan: Sesi meneroka cara berkomunikasi secara asertif tanpa menyakiti pihak lain. Pelajar bersetuju berbincang semula dengan rakan tersebut.\n\nSusulan: Sesi pendek minggu hadapan untuk menilai hasil perbincangan.",
        ],
        'family' => [
            "Isu: Pelajar berkongsi tentang pertengkaran berterusan di rumah yang menjejaskan tumpuan belajar.\n\nPemerhatian: Pelajar bercakap perlahan dan beberapa kali berhenti. Menyatakan rasa bertanggungjawab menjaga adik-adik. Tiada isu keselamatan segera dikenal pasti.\n\nTindakan: Memberi ruang selamat untuk pelajar meluahkan perasaan. Membincangkan cara menjaga kesejahteraan diri dan mengenal pasti orang dewasa yang boleh dipercayai.\n\nSusulan: Sesi dua minggu sekali. Rujukan kepada agensi luar akan dipertimbangkan sekiranya keadaan tidak bertambah baik.",
            "Isu: Perubahan struktur keluarga menyebabkan pelajar kerap murung di sekolah.\n\nPemerhatian: Pelajar masih menyesuaikan diri dengan keadaan baharu, namun menerima sokongan yang baik daripada neneknya.\n\nTindakan: Sesi memberi fokus kepada penerimaan dan pengurusan emosi. Pelajar dibimbing menulis jurnal perasaan ringkas setiap malam.\n\nSusulan: Sesi susulan dalam dua minggu.",
        ],
        'emotional_distress' => [
            "Isu: Pelajar dirujuk kerana kerap kelihatan murung dan menyendiri sejak dua minggu lepas.\n\nPemerhatian: Pelajar mengakui sukar tidur dan hilang minat terhadap aktiviti yang sebelum ini digemari. Penilaian risiko dijalankan: tiada niat mencederakan diri dinyatakan pada masa ini.\n\nTindakan: Sesi memberi ruang mendengar tanpa menghakimi. Teknik pernafasan dan pengurusan tekanan diperkenalkan. Nombor talian sokongan diberikan kepada pelajar.\n\nSusulan: Sesi mingguan. Ibu bapa akan dihubungi dengan persetujuan pelajar, dan rujukan kepada pakar dipertimbangkan sekiranya keadaan berterusan.",
            "Isu: Pelajar mengalami kebimbangan berlebihan menjelang peperiksaan.\n\nPemerhatian: Melaporkan degupan jantung laju dan sukar tidur sebelum kertas peperiksaan. Tiada punca tekanan lain yang ketara dikenal pasti.\n\nTindakan: Memperkenalkan teknik relaksasi dan penstrukturan jadual ulang kaji yang lebih realistik. Membincangkan jangkaan diri yang terlalu tinggi.\n\nSusulan: Sesi sokongan sebelum peperiksaan akhir penggal dan penilaian semula selepas itu.",
        ],
        'other' => [
            "Isu: Pelajar datang secara sukarela untuk berbincang tentang tekanan umum di sekolah.\n\nPemerhatian: Pelajar berupaya menyatakan perasaan dengan baik. Tiada isu keselamatan dikenal pasti.\n\nTindakan: Sesi menyokong dan meneroka strategi daya tindak yang sedia ada pada pelajar.\n\nSusulan: Pelajar dialu-alukan datang semula pada bila-bila masa.",
        ],
    ];

    public function issueType(string $issueType): static
    {
        return $this->state(fn () => [
            'issue_type' => $issueType,
            'category' => CounsellingRecord::ISSUE_TYPES[$issueType]['category'],
        ]);
    }
}
