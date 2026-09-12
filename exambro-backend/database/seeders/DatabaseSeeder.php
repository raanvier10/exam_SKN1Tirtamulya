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
        $class1 = \App\Models\StudentClass::firstOrCreate(
            ['name' => '11 TJKT 1'],
            ['description' => 'Kelas 11 TJKT 1']
        );

        $class2 = \App\Models\StudentClass::firstOrCreate(
            ['name' => '11 TJKT 2'],
            ['description' => 'Kelas 11 TJKT 2']
        );

        $class3 = \App\Models\StudentClass::firstOrCreate(
            ['name' => '12 IPA 1'],
            ['description' => 'Kelas 12 IPA 1']
        );

        // Admin User
        \App\Models\User::updateOrCreate(
            ['username' => 'adminexasatrya@smkn1tirtamulya.online'],
            [
                'name' => 'Administrator',
                'email' => 'adminexasatrya@smkn1tirtamulya.online',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'status' => 'active'
            ]
        );

        // Guru User (Pengawas UAS / Pembuat PTS)
        \App\Models\User::updateOrCreate(
            ['username' => 'guru@smkn1tirtamulya.online'],
            [
                'name' => 'Budi Guru, S.Pd.',
                'email' => 'guru@smkn1tirtamulya.online',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'guru',
                'status' => 'active'
            ]
        );

        // Siswa User
        $user = \App\Models\User::updateOrCreate(
            ['username' => 'dimas123'],
            [
                'name' => 'Dimas',
                'email' => 'dimas@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'siswa',
                'class_id' => $class1->id,
                'status' => 'active'
            ]
        );

        $exam = \App\Models\Exam::firstOrCreate(
            ['title' => 'UAS Basis Data'],
            [
                'description' => 'Ujian Akhir Semester Genap',
                'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeQ0f_VvG1nF_QW__.../viewform',
                'start_at' => \Carbon\Carbon::now()->subMinutes(10),
                'end_at' => \Carbon\Carbon::now()->addHours(2),
                'duration' => 90,
                'max_violation' => 3,
                'status' => 'active'
            ]
        );

        $exam->classes()->syncWithoutDetaching([$class1->id]);

        \App\Models\ExamParticipant::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'registered'
            ]
        );
    }
}
