<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a granular presenting-issue type alongside the broad case category,
     * so the counselling unit can report on specific problem areas (attendance,
     * attitude, bullying, ...) rather than only the three top-level categories.
     */
    public function up(): void
    {
        Schema::table('counselling_records', function (Blueprint $table) {
            // Nullable so pre-existing records remain valid; reported as "Unspecified".
            $table->string('issue_type', 40)->nullable()->after('category');
            $table->index('issue_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counselling_records', function (Blueprint $table) {
            $table->dropIndex(['issue_type']);
            $table->dropColumn('issue_type');
        });
    }
};
