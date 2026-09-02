<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('device_id')->nullable();
            $table->string('session_token')->unique();
            $table->dateTime('started_at');
            $table->dateTime('expired_at');
            $table->string('status')->default('ACTIVE'); // WAITING, ACTIVE, PAUSED, COMPLETED, LOCKED, EXPIRED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
