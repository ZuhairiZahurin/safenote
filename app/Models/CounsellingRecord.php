<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellingRecord extends Model
{
    use HasFactory;

    public const CATEGORY_ACADEMIC = 'academic';
    public const CATEGORY_BEHAVIOURAL = 'behavioural';
    public const CATEGORY_EMOTIONAL_WELFARE = 'emotional_welfare';

    /**
     * The three broad case categories from the project scope.
     */
    public const CATEGORIES = [
        self::CATEGORY_ACADEMIC => 'Academic',
        self::CATEGORY_BEHAVIOURAL => 'Behavioural',
        self::CATEGORY_EMOTIONAL_WELFARE => 'Emotional Welfare',
    ];

    /**
     * Granular presenting issues, each belonging to one broad category. This is
     * what the counsellor dashboard reports on.
     */
    public const ISSUE_TYPES = [
        'attendance' => ['label' => 'Attendance / Truancy', 'category' => self::CATEGORY_ACADEMIC],
        'academic_performance' => ['label' => 'Academic Performance', 'category' => self::CATEGORY_ACADEMIC],
        'career_guidance' => ['label' => 'Career Guidance', 'category' => self::CATEGORY_ACADEMIC],
        'attitude' => ['label' => 'Attitude & Conduct', 'category' => self::CATEGORY_BEHAVIOURAL],
        'discipline' => ['label' => 'Disciplinary Issue', 'category' => self::CATEGORY_BEHAVIOURAL],
        'bullying' => ['label' => 'Bullying', 'category' => self::CATEGORY_BEHAVIOURAL],
        'peer_relationship' => ['label' => 'Peer Relationships', 'category' => self::CATEGORY_EMOTIONAL_WELFARE],
        'family' => ['label' => 'Family Matters', 'category' => self::CATEGORY_EMOTIONAL_WELFARE],
        'emotional_distress' => ['label' => 'Emotional Distress', 'category' => self::CATEGORY_EMOTIONAL_WELFARE],
        'other' => ['label' => 'Other', 'category' => self::CATEGORY_EMOTIONAL_WELFARE],
    ];

    protected $fillable = [
        'student_id',
        'counsellor_id',
        'category',
        'issue_type',
        'session_date',
        'content',
    ];

    /**
     * 'content' uses Laravel's built-in `encrypted` cast, which encrypts/decrypts
     * transparently via the Crypt facade (AES-256-CBC, keyed by APP_KEY) so the
     * database only ever stores ciphertext.
     */
    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'content' => 'encrypted',
        ];
    }

    public static function issueTypeLabel(?string $key): string
    {
        return self::ISSUE_TYPES[$key]['label'] ?? 'Unspecified';
    }

    public static function categoryLabel(?string $key): string
    {
        return self::CATEGORIES[$key] ?? 'Unspecified';
    }

    /**
     * Issue types grouped by their parent category, for grouped <select> menus.
     *
     * @return array<string, array<string, string>>
     */
    public static function issueTypesByCategory(): array
    {
        $grouped = [];

        foreach (self::ISSUE_TYPES as $key => $meta) {
            $grouped[self::CATEGORIES[$meta['category']]][$key] = $meta['label'];
        }

        return $grouped;
    }

    public function getIssueTypeLabelAttribute(): string
    {
        return self::issueTypeLabel($this->issue_type);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryLabel($this->category);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }
}
