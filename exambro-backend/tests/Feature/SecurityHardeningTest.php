<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_token_is_hidden_from_login_and_me_responses(): void
    {
        $student = User::factory()->create([
            'username' => '20260001',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'status' => 'active',
        ]);

        $loginResp = $this->postJson('/api/login', [
            'username' => '20260001',
            'password' => 'secret123',
        ]);

        $loginResp->assertStatus(200);
        $this->assertArrayNotHasKey('api_token', $loginResp->json('data.user'));

        $token = $loginResp->json('data.token');
        $meResp = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me');

        $meResp->assertStatus(200);
        $this->assertArrayNotHasKey('api_token', $meResp->json('data'));
    }

    public function test_student_cannot_report_violation_on_another_students_session_or_wrong_exam(): void
    {
        $studentA = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'api_token' => hash('sha256', 'token_a'),
        ]);
        $studentB = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'api_token' => hash('sha256', 'token_b'),
        ]);

        $exam1 = Exam::create([
            'title' => 'Ujian 1',
            'google_form_url' => 'https://docs.google.com/forms/d/e/1/viewform',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        $exam2 = Exam::create([
            'title' => 'Ujian 2',
            'google_form_url' => 'https://docs.google.com/forms/d/e/2/viewform',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        $sessionB = ExamSession::create([
            'exam_id' => $exam1->id,
            'user_id' => $studentB->id,
            'device_id' => 'device_b',
            'session_token' => 'sess_b',
            'started_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addMinutes(60),
            'status' => 'ACTIVE',
        ]);

        // Student A tries to report a violation against Student B's session
        $idorResp = $this->withHeader('Authorization', 'Bearer token_a')
            ->postJson('/api/violations', [
                'exam_id' => $exam1->id,
                'session_id' => $sessionB->id,
                'type' => 'APP_SWITCH',
                'description' => 'IDOR attempt',
            ]);
        $idorResp->assertStatus(403);

        // Student B tries to report with mismatched exam_id
        $mismatchResp = $this->withHeader('Authorization', 'Bearer token_b')
            ->postJson('/api/violations', [
                'exam_id' => $exam2->id,
                'session_id' => $sessionB->id,
                'type' => 'APP_SWITCH',
                'description' => 'Exam mismatch attempt',
            ]);
        $mismatchResp->assertStatus(403);

        $this->assertDatabaseCount('violations', 0);
    }

    public function test_inactive_exam_cannot_be_accessed_via_api_or_seb(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
            'api_token' => hash('sha256', 'token_s'),
        ]);

        $draftExam = Exam::create([
            'title' => 'Ujian Draft Rahasia',
            'google_form_url' => 'https://docs.google.com/forms/d/e/draft/viewform',
            'start_at' => Carbon::now()->subMinutes(5),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'inactive',
        ]);

        $this->withHeader('Authorization', 'Bearer token_s')
            ->getJson("/api/student/exams/{$draftExam->id}")
            ->assertStatus(404);

        $this->withHeader('Authorization', 'Bearer token_s')
            ->postJson("/api/exams/{$draftExam->id}/start", ['device_id' => 'dev1'])
            ->assertStatus(404);

        $this->get("/seb/exam/{$draftExam->id}/config")->assertStatus(404);

        $sebResp = $this->withHeader('User-Agent', 'SafeExamBrowser/3.4.0 (iOS)')
            ->get("/seb/exam/{$draftExam->id}");
        $sebResp->assertStatus(200);
        $sebResp->assertSee('Ujian Tidak Aktif');
    }

    public function test_teacher_cannot_export_edit_update_or_delete_other_teachers_or_school_exams(): void
    {
        $teacherA = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $teacherB = User::factory()->create(['role' => 'guru', 'status' => 'active']);

        $teacherBExam = Exam::create([
            'title' => 'PTS Privat Guru B',
            'type' => 'uts',
            'google_form_url' => 'https://docs.google.com/forms/d/e/b/viewform',
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHour(),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
            'created_by' => $teacherB->id,
        ]);

        $schoolExamNullCreator = Exam::create([
            'title' => 'UAS Sekolah Umum',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/uas/viewform',
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHour(),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
            'created_by' => null,
        ]);

        // Teacher A cannot export Teacher B's violations
        $this->actingAs($teacherA)
            ->get(route('admin.exams.export-violations', $teacherBExam))
            ->assertRedirect(route('admin.exams.index'))
            ->assertSessionHas('error');

        // Teacher A cannot edit or delete school exam with created_by = null
        $this->actingAs($teacherA)
            ->get(route('admin.exams.edit', $schoolExamNullCreator))
            ->assertRedirect(route('admin.exams.index'))
            ->assertSessionHas('error');

        $this->actingAs($teacherA)
            ->delete(route('admin.exams.destroy', $schoolExamNullCreator))
            ->assertRedirect(route('admin.exams.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('exams', ['id' => $schoolExamNullCreator->id]);
    }

    public function test_inactive_teacher_cannot_login_to_admin_portal(): void
    {
        User::factory()->create([
            'username' => 'inactive_guru',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'status' => 'inactive',
        ]);

        $resp = $this->post(route('admin.login.post'), [
            'username' => 'inactive_guru',
            'password' => 'password123',
        ]);

        $resp->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_student_controller_blocks_access_to_non_student_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $teacher = User::factory()->create(['role' => 'guru', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.students.edit', $teacher->id))
            ->assertStatus(404);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $admin->id))
            ->assertStatus(404);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_csv_imports_prevent_role_overwrite_and_javascript_url_injection(): void
    {
        $admin = User::factory()->create([
            'username' => 'superadmin',
            'password' => Hash::make('keep_secret'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $studentCsv = UploadedFile::fake()->createWithContent(
            'students.csv',
            "nama_lengkap,nis_username,password,nama_kelas,status,is_pkl\nHijack Admin,superadmin,hacked123,XII RPL 1,active,0\n"
        );

        $this->actingAs($admin)->post(route('admin.students.import'), [
            'file' => $studentCsv,
        ]);

        $admin->refresh();
        $this->assertEquals('admin', $admin->role);
        $this->assertTrue(Hash::check('keep_secret', $admin->password));

        $examCsv = UploadedFile::fake()->createWithContent(
            'exams.csv',
            "judul_ujian,url_google_form,waktu_mulai,durasi_menit,maksimal_pelanggaran,status\nXSS Exam,javascript:alert(1),2026-10-01 08:00,60,3,active\n"
        );

        $this->actingAs($admin)->post(route('admin.exams.import'), [
            'file' => $examCsv,
        ]);

        $this->assertDatabaseMissing('exams', ['title' => 'XSS Exam']);
    }

    public function test_security_headers_are_present(): void
    {
        $resp = $this->get('/portal');
        $resp->assertStatus(200);
        $resp->assertHeader('X-Content-Type-Options', 'nosniff');
        $resp->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $resp->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}

