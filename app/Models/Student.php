<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class',
        'created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function counsellingRecords(): HasMany
    {
        return $this->hasMany(CounsellingRecord::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function scopeReferredBy(Builder $query, User $teacher): Builder
    {
        return $query->whereHas('referrals', fn ($q) => $q->where('teacher_id', $teacher->id));
    }

    /**
     * Students of the teacher's own class. A teacher with no class assigned
     * matches nothing, rather than falling through to every student.
     */
    /**
     * Find or create the student, refusing to do so when someone of the same
     * name is already registered in a different class. Creating a second row
     * would split one child's counselling history in two, and silently reusing
     * the other row could attach notes to the wrong child, so the person
     * entering the record is asked to resolve it instead.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function resolve(string $name, ?string $class, int $createdBy): self
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        $elsewhere = static::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($class, fn ($q) => $q->where('class', '!=', $class))
            ->when(! $class, fn ($q) => $q->whereNotNull('class'))
            ->first();

        if ($elsewhere) {
            throw ValidationException::withMessages([
                'student_name' => "A student named {$elsewhere->name} is already registered in {$elsewhere->class}. "
                    ."Select that class if this is the same student, or adjust the name if this is a different one.",
            ]);
        }

        return static::firstOrCreate(
            ['name' => $name, 'class' => $class],
            ['created_by' => $createdBy]
        );
    }

    public function scopeInClassOf(Builder $query, User $teacher): Builder
    {
        return blank($teacher->class)
            ? $query->whereRaw('1 = 0')
            : $query->where('class', $teacher->class);
    }
}
