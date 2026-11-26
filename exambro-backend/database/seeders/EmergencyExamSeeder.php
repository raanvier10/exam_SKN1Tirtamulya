<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmergencyExamSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::where('role', 'admin')
            ->whereNull('department')
            ->value('id') ?? User::where('role', 'admin')->value('id');

        $exam = Exam::updateOrCreate(
            ['title' => 'Ujian Darurat (Semua Siswa)'],
            [
                'type' => 'uas',
                'description' => 'Ujian darurat aktif yang ditujukan untuk seluruh kelas dan akun siswa.',
                'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSf90j8s68rmFR7GsOw2G87MTOP4nyC1z6UYxyNasmgEsv1jkw/viewform',
                'start_at' => now()->subMinutes(10),
                'end_at' => now()->addHours(12),
                'duration' => 120,
                'max_violation' => 3,
                'status' => 'active',
                'created_by' => $creatorId,
            ]
        );

        $allClassIds = StudentClass::pluck('id')->all();
        if (!empty($allClassIds)) {
            $exam->classes()->sync($allClassIds);
        }

        $students = User::where('role', 'siswa')
            ->where('status', 'active')
            ->get(['id']);

        foreach ($students as $student) {
            ExamParticipant::firstOrCreate(
                ['exam_id' => $exam->id, 'user_id' => $student->id],
                ['status' => 'registered']
            );
        }

        AppSetting::setValue('web_exam_enabled', '1');
        AppSetting::webEmergencyPin();
    }
}
