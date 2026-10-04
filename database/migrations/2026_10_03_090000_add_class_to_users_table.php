<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The class a teacher is responsible for. Teachers see the student profiles
     * of their own class only, so this is what scopes that list.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('class', 50)->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('class');
        });
    }
};
