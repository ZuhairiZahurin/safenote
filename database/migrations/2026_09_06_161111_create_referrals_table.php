<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Teacher-to-counsellor student referrals.
     *
     * Information flows one way: a teacher records their own observation here,
     * but never gains access to the counselling notes that result from it. This
     * keeps the referral workflow inside the confidentiality boundary required
     * by Akta Kaunselor 1998.
     */
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('issue_type', 40);
            $table->enum('urgency', ['low', 'medium', 'high'])->default('medium');
            // The teacher's observation is sensitive student data, so it is
            // AES-256 encrypted at rest exactly like counselling record content.
            $table->text('notes');
            $table->enum('status', ['pending', 'in_review', 'closed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('counselling_record_id')->nullable()
                ->constrained('counselling_records')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('urgency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
