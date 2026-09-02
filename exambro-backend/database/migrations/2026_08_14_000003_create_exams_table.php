<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('google_form_url');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('duration'); // in minutes
            $table->string('security_level')->default('BASIC'); // BASIC, STANDARD, STRICT
            $table->integer('max_violation')->default(3);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
