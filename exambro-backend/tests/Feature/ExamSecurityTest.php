<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExamSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_form_url_is_hidden_in_today_exams_and_show(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'api_token' => hash('sha256', 'test_token'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian Matematika',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // 1. Check today exams list
        $response = $this->withHeader('Authorization', 'Bearer test_token')
            ->getJson('/api/student/exams/today');

        $response->assertStatus(200);
        $this->assertNull($response->json('data.0.google_form_url'));

        // 2. Check exam detail
        $detailResponse = $this->withHeader('Authorization', 'Bearer test_token')
            ->getJson("/api/student/exams/{$exam->id}");

        $detailResponse->assertStatus(200);
        $this->assertNull($detailResponse->json('data.google_form_url'));
    }

    public function test_regular_student_outside_school_radius_is_rejected(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => false,
            'api_token' => hash('sha256', 'test_token'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian Harian',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => Carbon::now()->subMinutes(5),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // Coordinates far away (e.g. Jakarta Pusat ~60km away)
        $response = $this->withHeader('Authorization', 'Bearer test_token')
            ->postJson("/api/exams/{$exam->id}/start", [
                'device_id' => 'device_123',
                'latitude' => -6.175392,
                'longitude' => 106.827153,
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('di luar area sekolah', $response->json('message'));
    }

    public function test_regular_student_inside_school_radius_can_start_session_and_receives_form_url(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => false,
            'api_token' => hash('sha256', 'test_token'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian Harian',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => Carbon::now()->subMinutes(5),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // Exact school coordinates (-6.3400545, 107.4686982)
        $response = $this->withHeader('Authorization', 'Bearer test_token')
            ->postJson("/api/exams/{$exam->id}/start", [
                'device_id' => 'device_123',
                'latitude' => -6.3400545,
                'longitude' => 107.4686982,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertEquals('https://docs.google.com/forms/d/e/SECRET_URL/viewform', $response->json('data.google_form_url'));
    }

    public function test_pkl_student_can_start_session_without_gps(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
            'api_token' => hash('sha256', 'test_token'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian PKL',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => Carbon::now()->subMinutes(5),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // No latitude / longitude provided
        $response = $this->withHeader('Authorization', 'Bearer test_token')
            ->postJson("/api/exams/{$exam->id}/start", [
                'device_id' => 'device_123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertEquals('https://docs.google.com/forms/d/e/SECRET_URL/viewform', $response->json('data.google_form_url'));
    }

    public function test_session_expired_at_does_not_exceed_exam_end_at(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
            'api_token' => hash('sha256', 'test_token'),
        ]);

        $startAt = Carbon::now()->subMinutes(50);
        $endAt = Carbon::now()->addMinutes(10); // Exam ends in 10 minutes

        $exam = Exam::create([
            'title' => 'Ujian Terlambat',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration' => 60, // 60 minutes nominal
            'max_violation' => 3,
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer test_token')
            ->postJson("/api/exams/{$exam->id}/start", [
                'device_id' => 'device_123',
            ]);

        $response->assertStatus(200);
        $sessionExpiry = Carbon::parse($response->json('data.expired_at'));
        
        // Expiry should not exceed exam end_at
        $this->assertTrue($sessionExpiry->lessThanOrEqualTo($endAt->copy()->addSecond()));
    }

    public function test_accurate_violation_types_can_be_reported_and_render_in_admin_view(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
            'api_token' => hash('sha256', 'test_token'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian Deteksi Pelanggaran',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 10,
            'status' => 'active',
        ]);

        // Start session
        $startResponse = $this->withHeader('Authorization', 'Bearer test_token')
            ->postJson("/api/exams/{$exam->id}/start", ['device_id' => 'device_123']);
        $startResponse->assertStatus(200);
        $sessionId = $startResponse->json('data.id');

        $types = [
            ['type' => 'APP_SWITCH', 'desc' => 'Terdeteksi keluar aplikasi / beralih ke aplikasi lain (5 detik)'],
            ['type' => 'FLOATING_WINDOW', 'desc' => 'Terdeteksi membuka Jendela Mengambang (Floating Window / Smart Sidebar)'],
            ['type' => 'SPLIT_SCREEN', 'desc' => 'Terdeteksi mengaktifkan mode Layar Belah (Split-Screen)'],
            ['type' => 'SCREEN_RECORDING', 'desc' => 'Terdeteksi perekaman layar atau proyeksi layar'],
            ['type' => 'UNAUTHORIZED_URL', 'desc' => 'Mencoba mencari di Google / membuka video di luar form (google.com/search)'],
            ['type' => 'UNAUTHORIZED_COPY', 'desc' => 'Mencoba menyalin (copy) teks soal ujian'],
        ];

        foreach ($types as $t) {
            $resp = $this->withHeader('Authorization', 'Bearer test_token')
                ->postJson('/api/violations', [
                    'exam_id' => $exam->id,
                    'session_id' => $sessionId,
                    'type' => $t['type'],
                    'description' => $t['desc'],
                ]);
            $resp->assertStatus(200);
            $this->assertDatabaseHas('violations', [
                'exam_id' => $exam->id,
                'session_id' => $sessionId,
                'type' => $t['type'],
                'description' => $t['desc'],
            ]);
        }

        // View admin detail page
        $adminView = $this->actingAs($admin)->get(route('admin.exams.show', $exam->id));
        $adminView->assertStatus(200);
        $adminView->assertSee('Keluar Aplikasi (App Switch)');
        $adminView->assertSee('Jendela Mengambang (Floating Window)');
        $adminView->assertSee('Layar Belah (Split Screen)');
        $adminView->assertSee('Perekaman Layar (Screen Recording)');
        $adminView->assertSee('Akses URL Terlarang / Browsing');
        $adminView->assertSee('Salin Soal (Copy Attempt)');

        // Test CSV export
        $csvResp = $this->actingAs($admin)->get(route('admin.exams.export-violations', $exam->id));
        $csvResp->assertStatus(200);
        $csvContent = $csvResp->streamedContent();
        $this->assertStringContainsString('Keluar Aplikasi (App Switch)', $csvContent);
        $this->assertStringContainsString('Jendela Mengambang (Floating Window)', $csvContent);
        $this->assertStringContainsString('Layar Belah (Split Screen)', $csvContent);
        $this->assertStringContainsString('Perekaman Layar (Screen Recording)', $csvContent);
        $this->assertStringContainsString('Akses URL Terlarang / Browsing', $csvContent);
        $this->assertStringContainsString('Salin Soal (Copy Attempt)', $csvContent);
    }

    public function test_restarting_phone_or_app_during_active_exam_records_violation_and_locks_when_exceeded(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
            'api_token' => hash('sha256', 'test_token_reboot'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian Anti Reboot',
            'google_form_url' => 'https://docs.google.com/forms/d/e/SECRET_URL/viewform',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(60),
            'duration' => 60,
            'max_violation' => 2,
            'status' => 'active',
        ]);

        // 1. Initial start (First time opening exam)
        $start1 = $this->withHeader('Authorization', 'Bearer test_token_reboot')
            ->postJson("/api/exams/{$exam->id}/start", ['device_id' => 'device_123']);
        $start1->assertStatus(200);
        $start1->assertJson([
            'success' => true,
            'data' => [
                'violation_count' => 0,
                'resumed_violation' => false,
            ]
        ]);
        $this->assertEquals(0, Violation::where('exam_id', $exam->id)->where('user_id', $student->id)->count());

        // 2. Student turns off phone / kills app, then restarts and opens exam again
        $start2 = $this->withHeader('Authorization', 'Bearer test_token_reboot')
            ->postJson("/api/exams/{$exam->id}/start", ['device_id' => 'device_123']);
        $start2->assertStatus(200);
        $start2->assertJson([
            'success' => true,
            'data' => [
                'violation_count' => 1,
                'resumed_violation' => true,
            ]
        ]);
        $this->assertDatabaseHas('violations', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'type' => 'APP_RESTART_OR_DEVICE_OFF',
        ]);

        // 3. Student turns off phone / restarts second time -> exceeds max_violation (2)
        $start3 = $this->withHeader('Authorization', 'Bearer test_token_reboot')
            ->postJson("/api/exams/{$exam->id}/start", ['device_id' => 'device_123']);
        $start3->assertStatus(403);
        $start3->assertJson([
            'success' => false,
            'locked' => true,
            'violation_count' => 2,
        ]);

        // Verify participant is locked
        $participant = \App\Models\ExamParticipant::where('exam_id', $exam->id)->where('user_id', $student->id)->first();
        $this->assertEquals('locked', $participant->status);
    }
}

