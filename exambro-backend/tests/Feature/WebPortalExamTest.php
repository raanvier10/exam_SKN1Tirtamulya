<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebPortalExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_login_per_exam_access_code_rotate_single_browser_and_exam_flow(): void
    {
        $class = StudentClass::create(['name' => 'XI PPLG 1', 'description' => 'PPLG']);
        $otherClass = StudentClass::create(['name' => 'XI DKV 1', 'description' => 'DKV']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $student = User::factory()->create([
            'name' => 'Budi Siswa',
            'username' => '2026101',
            'password' => Hash::make('rahasia'),
            'role' => 'siswa',
            'class_id' => $class->id,
            'status' => 'active',
        ]);

        // 1. /darurat sudah dihapus (404), /download/apk redirect ke /portal, dan /portal tanpa PIN global
        $this->get('/darurat')->assertNotFound();
        $this->get('/download/apk')->assertRedirect(route('student.portal'));
        $this->get(route('student.portal'))
            ->assertOk()
            ->assertSee('Portal Ujian Resmi Siswa')
            ->assertSee('Masuk Peserta Ujian')
            ->assertDontSee('Kode PIN Pengawas')
            ->assertDontSee('Darurat');

        // 2. Buat 2 ujian berbeda via controller store -> masing-masing punya access_code unik 6 karakter
        $this->actingAs($admin)->post(route('admin.exams.store'), [
            'title' => 'PAS Basis Data',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSePortalDemo/viewform',
            'duration' => 60,
            'max_violation' => 2,
            'status' => 'active',
            'schedules' => [[
                'date' => now()->format('Y-m-d'),
                'time' => now()->subMinutes(5)->format('H:i'),
                'classes' => [$class->id],
            ]],
        ])->assertRedirect(route('admin.exams.index'));

        $exam = Exam::where('title', 'PAS Basis Data')->firstOrFail();
        $this->assertSame(6, strlen((string) $exam->access_code));

        $otherExam = Exam::create([
            'title' => 'PAS Desain Grafis',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeOtherDemo/viewform',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addHour(),
            'duration' => 60,
            'max_violation' => 2,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        $otherExam->classes()->sync([$otherClass->id]);
        $this->assertSame(6, strlen((string) $otherExam->access_code));

        // 3. Guru non-pembuat dilarang acak kode ujian milik admin; admin diizinkan & tampil di admin.exams.show
        $this->actingAs($guru)
            ->post(route('admin.exams.rotate-access-code', $exam))
            ->assertSessionHas('error');

        $oldCode = $exam->access_code;
        $this->actingAs($admin)
            ->post(route('admin.exams.rotate-access-code', $exam))
            ->assertSessionHas('success');
        $exam->refresh();
        $this->assertSame(6, strlen((string) $exam->access_code));

        $this->actingAs($admin)
            ->get(route('admin.exams.show', $exam))
            ->assertOk()
            ->assertSee('Kode Masuk Ujian (PIN)')
            ->assertSee($exam->access_code);

        // 4. Siswa login di /portal cukup NIS + password dan hanya melihat jadwal kelasnya
        $this->post(route('portal.login'), [
            'username' => '2026101',
            'password' => 'rahasia',
        ])->assertRedirect(route('student.portal'));

        $this->actingAs($student)
            ->withSession(['web_portal_verified' => true])
            ->get(route('student.portal'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Jadwal')
            ->assertSee('Profile')
            ->assertSee('Senin')
            ->assertSee('Jumat')
            ->assertSee('PAS Basis Data')
            ->assertDontSee('PAS Desain Grafis');

        // 5. Siswa bisa ganti password akun dari portal
        $this->actingAs($student)
            ->withSession(['web_portal_verified' => true])
            ->post(route('portal.change-password'), [
                'current_password' => 'rahasia',
                'password' => 'baru1234',
                'password_confirmation' => 'baru1234',
            ])
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertTrue(Hash::check('baru1234', $student->password));

        // 6. Masuk /ujian/{exam} meminta kode masuk ujian; salah ditolak, benar masuk & kunci 1 browser
        $this->actingAs($student)
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERONE1'])
            ->get(route('portal.exam', $exam))
            ->assertOk()
            ->assertSee('Verifikasi Ruang Ujian')
            ->assertSee('Kode Ujian (PIN)');

        $this->actingAs($student)
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERONE1'])
            ->post(route('portal.verify-code', $exam), ['access_code' => 'SALAH9'])
            ->assertSessionHas('error');

        $this->actingAs($student)
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERONE1'])
            ->post(route('portal.verify-code', $exam), ['access_code' => $exam->access_code])
            ->assertRedirect(route('portal.exam', $exam));

        $this->actingAs($student)
            ->withSession([
                'web_portal_verified' => true,
                'web_browser_id' => 'BROWSERONE1',
                "verified_exam_code_{$exam->id}" => $exam->access_code,
            ])
            ->get(route('portal.exam', $exam))
            ->assertOk()
            ->assertSee('UJIAN WEB');

        $session = ExamSession::where('exam_id', $exam->id)->where('user_id', $student->id)->firstOrFail();
        $this->assertStringStartsWith('WEB_PORTAL_BROWSERONE1', (string) $session->device_id);

        // Browser kedua ditolak selama sesi aktif di browser pertama
        $this->flushSession();
        $this->actingAs($student)
            ->withUnencryptedCookie('exasatrya_browser', 'BROWSERTWO2')
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERTWO2'])
            ->get(route('portal.exam', $exam))
            ->assertOk()
            ->assertSee('Sesi Aktif di Perangkat Lain');

        // 7. Pelanggaran -> terkunci -> tampil UJIAN WEB di buka kunci -> buka kunci -> selesai
        $this->flushSession();
        $this->actingAs($student)
            ->withUnencryptedCookie('exasatrya_browser', 'BROWSERONE1')
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERONE1'])
            ->postJson(route('portal.violation', $exam), ['type' => 'WEB_TAB_SWITCH'])
            ->assertOk()
            ->assertJson(['locked' => false, 'violations' => 1]);

        $this->actingAs($student)
            ->withUnencryptedCookie('exasatrya_browser', 'BROWSERONE1')
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERONE1'])
            ->postJson(route('portal.violation', $exam), ['type' => 'WEB_FULLSCREEN_EXIT'])
            ->assertOk()
            ->assertJson(['locked' => true, 'violations' => 2]);

        $this->actingAs($admin)
            ->get(route('admin.unlock.index'))
            ->assertOk()
            ->assertSee('UJIAN WEB')
            ->assertDontSee('Mode Web Ujian Darurat');

        $this->actingAs($admin)
            ->post(route('admin.exams.students.unlock', [$exam->id, $student->id]))
            ->assertSessionHas('success');

        $this->actingAs($student)
            ->withUnencryptedCookie('exasatrya_browser', 'BROWSERONE1')
            ->withSession(['web_portal_verified' => true, 'web_browser_id' => 'BROWSERONE1'])
            ->post(route('portal.finish', $exam))
            ->assertOk()
            ->assertSee('Ujian Berhasil Diselesaikan!');
    }

    public function test_weekly_schedule_shows_monday_to_friday_including_earlier_days_in_week(): void
    {
        $class = StudentClass::create(['name' => 'XII TJKT 1', 'description' => 'TJKT']);
        $student = User::factory()->create([
            'username' => '2026301',
            'password' => Hash::make('pass123'),
            'role' => 'siswa',
            'class_id' => $class->id,
            'status' => 'active',
        ]);

        // Freeze waktu ke hari Rabu jam 10:00 dalam minggu ini
        $wednesday = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(2)->setTime(10, 0);
        $this->travelTo($wednesday);

        $mondayExam = Exam::create([
            'title' => 'Matematika Hari Senin',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeSenin/viewform',
            'start_at' => $wednesday->copy()->subDays(2)->setTime(8, 0),
            'end_at' => $wednesday->copy()->subDays(2)->setTime(9, 30),
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active',
        ]);
        $mondayExam->classes()->sync([$class->id]);

        $fridayExam = Exam::create([
            'title' => 'Bahasa Inggris Hari Jumat',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeJumat/viewform',
            'start_at' => $wednesday->copy()->addDays(2)->setTime(8, 0),
            'end_at' => $wednesday->copy()->addDays(2)->setTime(9, 30),
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active',
        ]);
        $fridayExam->classes()->sync([$class->id]);

        $this->actingAs($student)
            ->withSession(['web_portal_verified' => true])
            ->get(route('student.portal'))
            ->assertOk()
            ->assertSee('Senin')
            ->assertSee('Selasa')
            ->assertSee('Rabu')
            ->assertSee('Kamis')
            ->assertSee('Jumat')
            ->assertSee('Matematika Hari Senin')
            ->assertSee('Waktu Berakhir')
            ->assertSee('Bahasa Inggris Hari Jumat')
            ->assertSee('Belum Dimulai');
    }

    public function test_school_wifi_same_ip_allows_more_than_ten_different_student_logins_per_minute(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $nis = '20262' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            User::factory()->create([
                'username' => $nis,
                'password' => Hash::make('pass123'),
                'role' => 'siswa',
                'status' => 'active',
            ]);

            $response = $this->post(route('portal.login'), [
                'username' => $nis,
                'password' => 'pass123',
            ]);

            $this->assertNotSame(429, $response->getStatusCode(), "Login {$i} should not be rate-limited on shared school IP");
            $response->assertRedirect(route('student.portal'));
        }
    }
}
