<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
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
}
