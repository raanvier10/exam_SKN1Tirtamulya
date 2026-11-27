<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\StudentClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WeeklyExamDemoSeeder extends Seeder
{
    public function run(): void
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $creatorId = User::where('role', 'admin')->value('id');
        $classIds = StudentClass::pluck('id')->all();
        $defaultFormUrl = 'https://docs.google.com/forms/d/e/1FAIpQLSeWeeklyDemoExaSatrya/viewform';

        $schedule = [
            // Senin
            [
                'day_offset' => 0,
                'title' => 'PAS Matematika',
                'type' => 'uas',
                'start' => '07:30',
                'end' => '09:00',
                'duration' => 90,
                'access_code' => 'SEN001',
            ],
            [
                'day_offset' => 0,
                'title' => 'PAS Bahasa Indonesia',
                'type' => 'uas',
                'start' => '09:30',
                'end' => '11:00',
                'duration' => 90,
                'access_code' => 'SEN002',
            ],
            // Selasa
            [
                'day_offset' => 1,
                'title' => 'PAS Bahasa Inggris',
                'type' => 'uas',
                'start' => '07:30',
                'end' => '09:00',
                'duration' => 90,
                'access_code' => 'SEL001',
            ],
            [
                'day_offset' => 1,
                'title' => 'PAS Pendidikan Pancasila',
                'type' => 'uas',
                'start' => '09:30',
                'end' => '10:45',
                'duration' => 75,
                'access_code' => 'SEL002',
            ],
            // Rabu
            [
                'day_offset' => 2,
                'title' => 'PAS Informatika',
                'type' => 'uas',
                'start' => '07:30',
                'end' => '09:00',
                'duration' => 90,
                'access_code' => 'RAB001',
            ],
            [
                'day_offset' => 2,
                'title' => 'PAS Sejarah',
                'type' => 'uas',
                'start' => '09:30',
                'end' => '10:45',
                'duration' => 75,
                'access_code' => 'RAB002',
            ],
            // Kamis
            [
                'day_offset' => 3,
                'title' => 'PAS Produktif Kejuruan',
                'type' => 'uas',
                'start' => '07:30',
                'end' => '09:30',
                'duration' => 120,
                'access_code' => 'KAM001',
            ],
            [
                'day_offset' => 3,
                'title' => 'PAS Pendidikan Agama',
                'type' => 'uas',
                'start' => '10:00',
                'end' => '11:15',
                'duration' => 75,
                'access_code' => 'KAM002',
            ],
            // Jumat (durasi jendela waktu diperpanjang untuk kebutuhan testing)
            [
                'day_offset' => 4,
                'title' => 'PAS Basis Data',
                'type' => 'uas',
                'start' => '07:00',
                'end' => '23:59',
                'duration' => 120,
                'access_code' => 'TEST01',
            ],
            [
                'day_offset' => 4,
                'title' => 'PAS Jaringan Komputer',
                'type' => 'uas',
                'start' => '08:00',
                'end' => '23:59',
                'duration' => 90,
                'access_code' => 'TEST02',
            ],
            [
                'day_offset' => 4,
                'title' => 'PAS Keamanan Jaringan',
                'type' => 'uas',
                'start' => '23:00',
                'end' => '23:59',
                'duration' => 60,
                'access_code' => 'TEST03',
            ],
        ];

        foreach ($schedule as $item) {
            $dayDate = $weekStart->copy()->addDays($item['day_offset']);
            [$startH, $startM] = explode(':', $item['start']);
            [$endH, $endM] = explode(':', $item['end']);

            $exam = Exam::updateOrCreate(
                ['title' => $item['title']],
                [
                    'type' => $item['type'],
                    'description' => 'Data uji jadwal mingguan (Senin - Jumat).',
                    'google_form_url' => $defaultFormUrl,
                    'start_at' => $dayDate->copy()->setTime((int) $startH, (int) $startM, 0),
                    'end_at' => $dayDate->copy()->setTime((int) $endH, (int) $endM, 59),
                    'duration' => $item['duration'],
                    'max_violation' => 3,
                    'access_code' => $item['access_code'],
                    'status' => 'active',
                    'created_by' => $creatorId,
                ]
            );

            if (!empty($classIds)) {
                $exam->classes()->sync($classIds);
            }
        }

        $this->command?->info('WeeklyExamDemoSeeder selesai: 11 ujian (Senin-Jumat) disiapkan. Kode testing Jumat: TEST01, TEST02, TEST03.');
    }
}
