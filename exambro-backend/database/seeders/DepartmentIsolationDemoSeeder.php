<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentIsolationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DepartmentPicSeeder::class);

        $tjktClass = StudentClass::firstOrCreate(
            ['name' => 'XI TJKT 1'],
            ['description' => 'Kelas XI TJKT 1']
        );

        $tmClass = StudentClass::firstOrCreate(
            ['name' => 'XI TM 1'],
            ['description' => 'Kelas XI TM 1']
        );

        $superAdminId = User::where('role', 'admin')->whereNull('department')->value('id');

        $demoStudents = [
            User::updateOrCreate(
                ['username' => 'siswa_tjkt_demo'],
                [
                    'name' => 'Bima Pratama (Demo TJKT)',
                    'email' => null,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'class_id' => $tjktClass->id,
                    'status' => 'active',
                ]
            ),
            User::updateOrCreate(
                ['username' => 'siswa_tm_demo'],
                [
                    'name' => 'Raka Saputra (Demo TM)',
                    'email' => null,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'class_id' => $tmClass->id,
                    'status' => 'active',
                ]
            ),
        ];

        $exam = Exam::updateOrCreate(
            ['title' => 'UAS Uji Isolasi TJKT & TM'],
            [
                'type' => 'uas',
                'description' => 'Simulasi ujian bersama untuk mengecek filter pelanggaran & buka kunci per jurusan.',
                'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeDemoIsolation/viewform',
                'start_at' => now()->subMinutes(15),
                'end_at' => now()->addHours(2),
                'duration' => 90,
                'max_violation' => 3,
                'status' => 'active',
                'created_by' => $superAdminId,
            ]
        );

        $exam->classes()->syncWithoutDetaching([$tjktClass->id, $tmClass->id]);

        foreach ($demoStudents as $student) {
            ExamParticipant::updateOrCreate(
                ['exam_id' => $exam->id, 'user_id' => $student->id],
                ['status' => 'locked', 'started_at' => now()->subMinutes(10), 'finished_at' => null]
            );

            $session = ExamSession::updateOrCreate(
                ['session_token' => 'demo_lock_' . $student->username],
                [
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                    'status' => 'LOCKED',
                    'started_at' => now()->subMinutes(10),
                    'expired_at' => now()->addHours(2),
                    'unlocked_at' => null,
                ]
            );

            Violation::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                    'session_id' => $session->id,
                    'type' => 'APP_SWITCH',
                ],
                ['description' => 'Simulasi pelanggaran keluar aplikasi (uji isolasi PIC jurusan)']
            );
        }
    }
}
