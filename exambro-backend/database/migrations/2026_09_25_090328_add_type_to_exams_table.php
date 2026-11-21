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
        if (!Schema::hasColumn('exams', 'type')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->string('type', 10)->default('uas')->after('title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('exams', 'type')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
