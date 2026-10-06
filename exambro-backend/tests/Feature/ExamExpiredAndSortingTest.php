<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\StudentClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamExpiredAndSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_exam_is_not_ongoing_even_if_locked_session_exists(): void
    {
        $exam = Exam::create([
            'title' => 'Ujian Selesai',
            'google_form_url' => 'https://forms.gle/test',
            'start_at' => Carbon::now()->subHours(3),
            'end_at' => Carbon::now()->subHours(1),
            'duration' => 120,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        $student = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'device_id' => 'device-123',
            'session_token' => 'token-123',
            'started_at' => Carbon::now()->subHours(2),
            'expired_at' => Carbon::now()->subHours(1),
            'status' => 'LOCKED',
        ]);

        $this->assertTrue($exam->isExpired());
        $this->assertFalse($exam->isOngoing());
    }

    public function test_visiting_exam_index_syncs_expired_working_and_locked_participants(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $exam = Exam::create([
            'title' => 'Ujian Lampau',
            'google_form_url' => 'https://forms.gle/test',
            'start_at' => Carbon::now()->subHours(4),
            'end_at' => Carbon::now()->subHours(2),
            'duration' => 120,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        $student1 = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
        $student2 = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $p1 = ExamParticipant::create([
            'exam_id' => $exam->id,
            'user_id' => $student1->id,
            'status' => 'working',
        ]);

        $p2 = ExamParticipant::create([
            'exam_id' => $exam->id,
            'user_id' => $student2->id,
            'status' => 'locked',
        ]);

        $s1 = ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $student1->id,
            'device_id' => 'd-1',
            'session_token' => 't-1',
            'started_at' => Carbon::now()->subHours(4),
            'expired_at' => Carbon::now()->subHours(2),
            'status' => 'ACTIVE',
        ]);

        $s2 = ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $student2->id,
            'device_id' => 'd-2',
            'session_token' => 't-2',
            'started_at' => Carbon::now()->subHours(4),
            'expired_at' => Carbon::now()->subHours(2),
            'status' => 'LOCKED',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.exams.index'));
        $response->assertStatus(200);
        $response->assertSee('Selesai');

        $this->assertEquals('finished', $p1->fresh()->status);
        $this->assertEquals('finished', $p2->fresh()->status);
        $this->assertEquals('FINISHED', $s1->fresh()->status);
        $this->assertEquals('FINISHED', $s2->fresh()->status);
    }

    public function test_students_index_orders_by_class_name_and_alphabetical_student_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $classB = StudentClass::create(['name' => 'XI TJKT 2']);
        $classA = StudentClass::create(['name' => 'XI TJKT 1']);

        $studentB2 = User::factory()->create(['name' => 'Zaky', 'username' => '1001', 'role' => 'siswa', 'class_id' => $classB->id]);
        $studentB1 = User::factory()->create(['name' => 'Ahmad', 'username' => '1002', 'role' => 'siswa', 'class_id' => $classB->id]);
        $studentA2 = User::factory()->create(['name' => 'Budi', 'username' => '1003', 'role' => 'siswa', 'class_id' => $classA->id]);
        $studentA1 = User::factory()->create(['name' => 'Anisa', 'username' => '1004', 'role' => 'siswa', 'class_id' => $classA->id]);

        $response = $this->actingAs($admin)->get(route('admin.students.index'));
        $response->assertStatus(200);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $students */
        $students = $response->viewData('students');
        $names = $students->pluck('name')->toArray();

        // Expected order: XI TJKT 1 (Anisa, Budi), XI TJKT 2 (Ahmad, Zaky)
        $this->assertEquals(['Anisa', 'Budi', 'Ahmad', 'Zaky'], $names);
    }

    public function test_student_sees_exam_history_up_to_one_week_but_not_older(): void
    {
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'api_token' => hash('sha256', 'token-abc'),
        ]);

        // Ujian 3 hari lalu (masuk riwayat 1 pekan)
        $exam3DaysAgo = Exam::create([
            'title' => 'Ujian 3 Hari Lalu',
            'google_form_url' => 'https://forms.gle/test3',
            'start_at' => Carbon::now()->subDays(3),
            'end_at' => Carbon::now()->subDays(3)->addHours(2),
            'duration' => 120,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // Ujian Hari Ini
        $examToday = Exam::create([
            'title' => 'Ujian Hari Ini',
            'google_form_url' => 'https://forms.gle/today',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(50),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // Ujian 10 hari lalu (harus keluar dari riwayat 1 pekan)
        $exam10DaysAgo = Exam::create([
            'title' => 'Ujian 10 Hari Lalu',
            'google_form_url' => 'https://forms.gle/old',
            'start_at' => Carbon::now()->subDays(10),
            'end_at' => Carbon::now()->subDays(10)->addHours(2),
            'duration' => 120,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer token-abc')
            ->getJson('/api/student/exams/today');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();

        $this->assertContains('Ujian Hari Ini', $titles);
        $this->assertContains('Ujian 3 Hari Lalu', $titles);
        $this->assertNotContains('Ujian 10 Hari Lalu', $titles);

        // Ujian hari ini yang sedang berlangsung harus berada di urutan pertama
        $this->assertEquals('Ujian Hari Ini', $titles[0]);
    }
}
