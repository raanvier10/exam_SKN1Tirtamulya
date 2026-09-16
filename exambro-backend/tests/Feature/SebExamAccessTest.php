<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SebExamAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleExam(): Exam
    {
        return Exam::create([
            'title' => 'Ujian Akhir Semester IPA',
            'google_form_url' => 'https://docs.google.com/forms/d/e/sample-form/viewform',
            'start_at' => Carbon::now()->subMinutes(15),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);
    }

    public function test_regular_browser_is_directed_to_seb_landing_page(): void
    {
        $exam = $this->createSampleExam();

        $response = $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1')
            ->get("/seb/exam/{$exam->id}");

        $response->assertStatus(200);
        $response->assertSee('Wajib Menggunakan Safe Exam Browser');
        $response->assertSee('Buka di Safe Exam Browser');
        $response->assertSee('seb://', false);
    }

    public function test_seb_browser_shows_student_login_when_unauthenticated(): void
    {
        $exam = $this->createSampleExam();

        $response = $this->withHeader('User-Agent', 'SafeExamBrowser/3.4.0 (iOS)')
            ->get("/seb/exam/{$exam->id}");

        $response->assertStatus(200);
        $response->assertSee('SEB Secure Gateway');
        $response->assertSee('Nomor Induk Siswa (NIS)');
        $response->assertSee('Masuk');
    }

    public function test_student_can_authenticate_and_enter_seb_exam(): void
    {
        $exam = $this->createSampleExam();
        $student = User::factory()->create([
            'name' => 'Ahmad Siswa',
            'username' => '20241001',
            'password' => Hash::make('rahasia123'),
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
        ]);

        // 1. Submit auth form in SEB
        $authResponse = $this->withHeader('User-Agent', 'SafeExamBrowser/3.4.0 (iOS)')
            ->post("/seb/exam/{$exam->id}/auth", [
                'username' => '20241001',
                'password' => 'rahasia123',
            ]);

        $authResponse->assertRedirect("/seb/exam/{$exam->id}");
        $this->assertAuthenticatedAs($student);

        // 2. Access exam page inside SEB after auth
        $examResponse = $this->actingAs($student)
            ->withHeader('User-Agent', 'SafeExamBrowser/3.4.0 (iOS)')
            ->get("/seb/exam/{$exam->id}");

        $examResponse->assertStatus(200);
        $examResponse->assertSee('SEB KIOSK');
        $examResponse->assertSee('Ahmad Siswa');
        $examResponse->assertSee('embedded=true');

        // 3. Verify session & participant created
        $this->assertDatabaseHas('exam_sessions', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'working',
        ]);
    }

    public function test_student_can_finish_exam_in_seb(): void
    {
        $exam = $this->createSampleExam();
        $student = User::factory()->create([
            'username' => '20241002',
            'password' => Hash::make('rahasia123'),
            'role' => 'siswa',
            'status' => 'active',
        ]);

        // Create active session
        ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'device_id' => 'SEB_TEST',
            'session_token' => 'test_token',
            'started_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addMinutes(60),
            'status' => 'ACTIVE',
        ]);

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'working',
        ]);

        // Finish exam
        $response = $this->actingAs($student)
            ->withHeader('User-Agent', 'SafeExamBrowser/3.4.0 (iOS)')
            ->post("/seb/exam/{$exam->id}/finish");

        $response->assertStatus(200);
        $response->assertSee('Ujian Berhasil Diselesaikan!');

        $this->assertDatabaseHas('exam_sessions', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'COMPLETED',
        ]);

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'finished',
        ]);
    }

    public function test_download_seb_configuration_file(): void
    {
        $exam = $this->createSampleExam();

        $response = $this->get("/seb/exam/{$exam->id}/config");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/seb');
        $response->assertSee('<key>startURL</key>', false);
        $response->assertSee("/seb/exam/{$exam->id}", false);
    }
}
