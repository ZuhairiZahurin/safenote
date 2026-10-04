<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counselling_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('counsellor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('category', ['academic', 'behavioural', 'emotional_welfare'])->default('academic');
            $table->date('session_date');
            // AES-256 encrypted (Laravel Crypt / encrypted cast) session notes.
            $table->text('content');
            $table->timestamps();

            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselling_records');
    }
};
