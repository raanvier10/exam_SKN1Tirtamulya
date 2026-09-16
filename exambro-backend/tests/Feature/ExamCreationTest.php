<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_exam_with_default_and_custom_slot_duration(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $classA = StudentClass::create(['name' => 'X RPL 1']);
        $classB = StudentClass::create(['name' => 'X RPL 2']);

        $payload = [
            'title' => 'Penilaian Tengah Semester',
            'description' => 'Ujian simulasi',
            'google_form_url' => 'https://docs.google.com/forms/d/e/sample/viewform',
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active',
            'schedules' => [
                [
                    'date' => '2026-09-20',
                    'time' => '08:00',
                    'duration' => null, // empty / default
                    'classes' => [$classA->id],
                ],
                [
                    'date' => '2026-09-21',
                    'time' => '08:00',
                    'duration' => 60, // custom duration
                    'classes' => [$classB->id],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.exams.store'), $payload);

        $response->assertRedirect(route('admin.exams.index'));
        $response->assertSessionHas('success');

        // Verify slot 1: default duration 90m
        $examSlot1 = Exam::where('title', 'Penilaian Tengah Semester')
            ->whereDate('start_at', '2026-09-20')
            ->first();

        $this->assertNotNull($examSlot1);
        $this->assertEquals(90, $examSlot1->duration);
        $this->assertEquals('2026-09-20 09:30:00', $examSlot1->end_at->format('Y-m-d H:i:s'));

        // Verify slot 2: custom duration 60m
        $examSlot2 = Exam::where('title', 'Penilaian Tengah Semester')
            ->whereDate('start_at', '2026-09-21')
            ->first();

        $this->assertNotNull($examSlot2);
        $this->assertEquals(60, $examSlot2->duration);
        $this->assertEquals('2026-09-21 09:00:00', $examSlot2->end_at->format('Y-m-d H:i:s'));
    }

    public function test_can_create_exam_when_slot_duration_is_empty_string(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $classA = StudentClass::create(['name' => 'X RPL 3']);

        $payload = [
            'title' => 'Ujian String Kosong',
            'google_form_url' => 'https://docs.google.com/forms/d/e/sample/viewform',
            'duration' => 45,
            'max_violation' => 3,
            'status' => 'active',
            'schedules' => [
                [
                    'date' => '2026-09-22',
                    'time' => '10:00',
                    'duration' => '', // HTML input sends empty string
                    'classes' => [$classA->id],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.exams.store'), $payload);
        $response->assertRedirect(route('admin.exams.index'));

        $exam = Exam::where('title', 'Ujian String Kosong')->first();
        $this->assertNotNull($exam);
        $this->assertEquals(45, $exam->duration);
        $this->assertEquals('2026-09-22 10:45:00', $exam->end_at->format('Y-m-d H:i:s'));
    }

    public function test_can_create_exams_with_different_jp_durations_per_slot(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $class1 = StudentClass::create(['name' => 'X AKUN 1']);
        $class2 = StudentClass::create(['name' => 'X AKUN 2']);

        $payload = [
            'title' => 'PTS Akuntansi Berbeda JP',
            'google_form_url' => 'https://docs.google.com/forms/d/e/sample/viewform',
            'duration' => 90, // Default 2 JP
            'max_violation' => 3,
            'status' => 'active',
            'schedules' => [
                [
                    'date' => '2026-09-25',
                    'time' => '07:30',
                    'duration' => 45, // 1 JP
                    'classes' => [$class1->id],
                ],
                [
                    'date' => '2026-09-25',
                    'time' => '09:00',
                    'duration' => 135, // 3 JP
                    'classes' => [$class2->id],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.exams.store'), $payload);
        $response->assertRedirect(route('admin.exams.index'));

        $slot1 = Exam::where('title', 'PTS Akuntansi Berbeda JP')->where('duration', 45)->first();
        $this->assertNotNull($slot1);
        $this->assertEquals('2026-09-25 08:15:00', $slot1->end_at->format('Y-m-d H:i:s'));

        $slot2 = Exam::where('title', 'PTS Akuntansi Berbeda JP')->where('duration', 135)->first();
        $this->assertNotNull($slot2);
        $this->assertEquals('2026-09-25 11:15:00', $slot2->end_at->format('Y-m-d H:i:s'));
    }

    public function test_exams_index_groups_multi_session_exams_into_single_card(): void
    {
        $teacher = User::factory()->create(['role' => 'guru', 'status' => 'active']);
        $class1 = StudentClass::create(['name' => 'XII RPL 1']);
        $class2 = StudentClass::create(['name' => 'XII RPL 2']);

        // 2 Sesi untuk ujian yang sama
        $exam1 = Exam::create([
            'title' => 'Ujian Akhir Semester Fisika',
            'google_form_url' => 'https://forms.gle/fisika-uas-2026',
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active',
            'start_at' => now()->addHours(1),
            'end_at' => now()->addHours(2)->addMinutes(30),
            'created_by' => $teacher->id,
        ]);
        $exam1->classes()->attach($class1->id);

        $exam2 = Exam::create([
            'title' => 'Ujian Akhir Semester Fisika',
            'google_form_url' => 'https://forms.gle/fisika-uas-2026',
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active',
            'start_at' => now()->addHours(3),
            'end_at' => now()->addHours(4)->addMinutes(30),
            'created_by' => $teacher->id,
        ]);
        $exam2->classes()->attach($class2->id);

        $response = $this->actingAs($teacher)->get(route('admin.exams.index'));
        $response->assertStatus(200);

        // Pastikan groupedExams ada 1 grup berisi 2 sesi
        $grouped = $response->viewData('groupedExams');
        $this->assertCount(1, $grouped);
        $this->assertEquals(2, $grouped->first()->session_count);
        $this->assertEquals('Ujian Akhir Semester Fisika', $grouped->first()->title);

        // Pastikan UI menampilkan badge 2 Sesi Terjadwal
        $response->assertSee('2 Sesi Terjadwal');
        $response->assertSee('Buka 2 Sesi');
    }
}
