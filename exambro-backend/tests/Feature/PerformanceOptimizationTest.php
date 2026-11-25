<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_show_uses_constant_queries_without_n_plus_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $class = StudentClass::create(['name' => 'XII TJKT 1']);

        $exam = Exam::create([
            'title' => 'Ujian Skalabilitas',
            'type' => 'uas',
            'google_form_url' => 'https://docs.google.com/forms/d/e/test/viewform',
            'start_at' => Carbon::now()->subMinutes(15),
            'end_at' => Carbon::now()->addMinutes(45),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);
        $exam->classes()->attach($class->id);

        $seedStudents = function (int $count) use ($exam, $class) {
            for ($i = 0; $i < $count; $i++) {
                $student = User::factory()->create([
                    'role' => 'siswa',
                    'status' => 'active',
                    'class_id' => $class->id,
                ]);
                ExamParticipant::create([
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                    'status' => 'working',
                ]);
                $session = ExamSession::create([
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                    'device_id' => 'dev_' . $student->id,
                    'session_token' => Str::random(24),
                    'started_at' => Carbon::now()->subMinutes(5),
                    'expired_at' => Carbon::now()->addMinutes(55),
                    'unlocked_at' => $i === 0 ? Carbon::now()->subMinutes(2) : null,
                    'status' => 'ACTIVE',
                ]);
                Violation::create([
                    'exam_id' => $exam->id,
                    'user_id' => $student->id,
                    'session_id' => $session->id,
                    'type' => 'APP_SWITCH',
                    'description' => 'Test violation',
                ]);
            }
        };

        // Measure query count with 2 participants
        $seedStudents(2);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.exams.show', $exam))->assertStatus(200);
        $queriesWithTwo = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Add 10 more participants (total 12) and verify query count remains constant (no N+1)
        $seedStudents(10);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.exams.show', $exam))->assertStatus(200);
        $queriesWithTwelve = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($queriesWithTwo, $queriesWithTwelve);
    }

    public function test_exam_index_and_unlocked_reentry_resume_logic_remain_intact(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $student = User::factory()->create([
            'role' => 'siswa',
            'status' => 'active',
            'is_pkl' => true,
            'api_token' => hash('sha256', 'tok_perf'),
        ]);

        $exam = Exam::create([
            'title' => 'Ujian Resumption',
            'type' => 'uts',
            'google_form_url' => 'https://docs.google.com/forms/d/e/test2/viewform',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(50),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
        ]);

        // Index renders cleanly using preloaded has_active_session
        $this->actingAs($admin)->get(route('admin.exams.index'))->assertStatus(200);

        // Unlocked session re-entry still reports accurate violation count
        ExamSession::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'device_id' => 'dev_same',
            'session_token' => Str::random(24),
            'started_at' => Carbon::now()->subMinutes(5),
            'expired_at' => Carbon::now()->addMinutes(55),
            'unlocked_at' => Carbon::now()->subMinute(),
            'status' => 'ACTIVE',
        ]);
        ExamParticipant::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'working',
        ]);

        $resp = $this->withHeader('Authorization', 'Bearer tok_perf')
            ->postJson("/api/exams/{$exam->id}/start", ['device_id' => 'dev_same']);

        $resp->assertStatus(200);
        $this->assertFalse($resp->json('data.resumed_violation'));
        $this->assertSame(0, $resp->json('data.violation_count'));
    }
}
