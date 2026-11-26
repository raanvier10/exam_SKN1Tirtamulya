<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebEmergencyExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergency_web_exam_toggle_pin_flow_violation_lock_and_unlock(): void
    {
        $tjktClass = StudentClass::create(['name' => 'XI TJKT 1', 'description' => 'TJKT']);
        $dkvClass = StudentClass::create(['name' => 'XI DKV 1', 'description' => 'DKV']);

        $picTjkt = User::factory()->create(['role' => 'admin', 'department' => 'TJKT', 'status' => 'active']);
        $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $student = User::factory()->create([
            'name' => 'Siswa Darurat',
            'username' => '2026001',
            'password' => Hash::make('rahasia'),
            'role' => 'siswa',
            'class_id' => $tjktClass->id,
            'status' => 'active',
        ]);

        $tjktExam = Exam::create([
            'title' => 'UAS Pemrograman Web',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeDemo/viewform',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addHour(),
            'duration' => 60,
            'max_violation' => 2,
            'status' => 'active',
            'created_by' => $picTjkt->id,
        ]);
        $tjktExam->classes()->sync([$tjktClass->id]);

        $dkvExam = Exam::create([
            'title' => 'UAS Desain DKV',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSeDkv/viewform',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addHour(),
            'duration' => 60,
            'max_violation' => 2,
            'status' => 'active',
            'created_by' => $picTjkt->id,
        ]);
        $dkvExam->classes()->sync([$dkvClass->id]);

        // 1. Default OFF: /darurat menolak akses ujian
        $this->get(route('web-exam.index'))
            ->assertOk()
            ->assertSee('Mode Web Ujian Darurat Tidak Aktif');

        // 2. Guru dilarang menyalakan mode darurat; PIC/Admin diizinkan
        $this->actingAs($guru)
            ->post(route('admin.unlock.web-emergency.toggle'))
            ->assertRedirect(route('admin.exams.index'));
        $this->assertFalse(AppSetting::isWebEmergencyEnabled());

        $this->actingAs($picTjkt)
            ->post(route('admin.unlock.web-emergency.toggle'))
            ->assertSessionHas('success');
        $this->assertTrue(AppSetting::isWebEmergencyEnabled());
        $pin = AppSetting::webEmergencyPin();
        $this->assertSame(6, strlen($pin));

        // 3. Siswa salah PIN ditolak, benar PIN masuk & hanya lihat ujian kelasnya
        $this->post(route('web-exam.login'), [
            'username' => '2026001',
            'password' => 'rahasia',
            'pin' => 'SALAH1',
        ])->assertSessionHas('error');

        $this->post(route('web-exam.login'), [
            'username' => '2026001',
            'password' => 'rahasia',
            'pin' => $pin,
        ])->assertRedirect(route('web-exam.index'));

        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->get(route('web-exam.index'))
            ->assertOk()
            ->assertSee('UAS Pemrograman Web')
            ->assertDontSee('UAS Desain DKV');

        // 4. Mulai ujian via Web Darurat -> sesi memakai prefix WEB_DARURAT_
        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->get(route('web-exam.exam', $tjktExam))
            ->assertOk()
            ->assertSee('WEB DARURAT');

        $session = ExamSession::where('exam_id', $tjktExam->id)->where('user_id', $student->id)->firstOrFail();
        $this->assertStringStartsWith('WEB_DARURAT_', (string) $session->device_id);

        // 5. Pelanggaran 1 -> belum terkunci; Pelanggaran 2 -> terkunci & muncul di Buka Kunci PIC TJKT
        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->postJson(route('web-exam.violation', $tjktExam), ['type' => 'WEB_TAB_SWITCH'])
            ->assertOk()
            ->assertJson(['locked' => false, 'violations' => 1, 'max_violation' => 2]);

        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->postJson(route('web-exam.violation', $tjktExam), ['type' => 'WEB_FULLSCREEN_EXIT'])
            ->assertOk()
            ->assertJson(['locked' => true, 'violations' => 2, 'max_violation' => 2]);

        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->get(route('web-exam.exam', $tjktExam))
            ->assertOk()
            ->assertSee('Ujian Terkunci');

        $this->actingAs($picTjkt)->get(route('admin.unlock.index'))
            ->assertOk()
            ->assertSee('Siswa Darurat')
            ->assertSee('WEB DARURAT');

        // 5b. Siswa terkunci dilarang bypass lewat POST finish (baik Web Darurat maupun API)
        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->post(route('web-exam.finish', $tjktExam))
            ->assertOk()
            ->assertSee('Ujian Terkunci');
        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $tjktExam->id,
            'user_id' => $student->id,
            'status' => 'locked',
        ]);

        // 6. PIC membuka kunci & siswa menyelesaikan ujian
        $this->actingAs($picTjkt)
            ->post(route('admin.exams.students.unlock', [$tjktExam->id, $student->id]))
            ->assertSessionHas('success');

        $this->actingAs($student)
            ->withSession(['web_exam_verified' => true])
            ->post(route('web-exam.finish', $tjktExam))
            ->assertOk()
            ->assertSee('Ujian Berhasil Diselesaikan!');

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $tjktExam->id,
            'user_id' => $student->id,
            'status' => 'finished',
        ]);
    }
}
