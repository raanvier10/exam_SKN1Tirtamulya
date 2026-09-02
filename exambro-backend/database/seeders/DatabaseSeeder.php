<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $class = \App\Models\StudentClass::create([
            'name' => '12 IPA 1',
            'description' => 'Kelas 12 IPA 1'
        ]);

        $user = \App\Models\User::create([
            'name' => 'Dimas',
            'username' => 'dimas123',
            'email' => 'dimas@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'siswa',
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $exam = \App\Models\Exam::create([
            'title' => 'UAS Basis Data',
            'description' => 'Ujian Akhir Semester Genap',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeQ0f_VvG1nF_QW__.../viewform', // Replace with real one if testing
            'start_at' => \Carbon\Carbon::now()->subMinutes(10), // already started
            'end_at' => \Carbon\Carbon::now()->addHours(2),
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active'
        ]);

        \App\Models\ExamParticipant::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'status' => 'registered'
        ]);
    }
}
