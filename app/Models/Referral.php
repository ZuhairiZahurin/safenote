<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_REVIEW => 'In Review',
        self::STATUS_CLOSED => 'Closed',
    ];

    public const URGENCIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ];

    protected $fillable = [
        'student_id',
        'teacher_id',
        'issue_type',
        'urgency',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'counselling_record_id',
    ];

    protected function casts(): array
    {
        return [
            // Same AES-256 protection as counselling record content.
            'notes' => 'encrypted',
            'reviewed_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    public function getUrgencyLabelAttribute(): string
    {
        return self::URGENCIES[$this->urgency] ?? 'Unknown';
    }

    public function getIssueTypeLabelAttribute(): string
    {
        return CounsellingRecord::issueTypeLabel($this->issue_type);
    }

    /**
     * Bootstrap contextual colour for the status badge.
     */
    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_REVIEW => 'info',
            self::STATUS_CLOSED => 'success',
            default => 'secondary',
        };
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function counsellingRecord(): BelongsTo
    {
        return $this->belongsTo(CounsellingRecord::class);
    }
}
