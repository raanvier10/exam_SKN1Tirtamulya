<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamSession;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Violation;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartmentIsolationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DepartmentPicScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_all_six_department_pic_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['AKUN', 'MPLB', 'DKV', 'TJKT', 'TM', 'TITL'] as $dept) {
            $user = User::where('username', 'pic_' . strtolower($dept))->first();
            $this->assertNotNull($user);
            $this->assertSame('admin', $user->role);
            $this->assertSame($dept, $user->department);
            $this->assertTrue($user->isAdmin());
            $this->assertTrue($user->isDepartmentPic());
            $this->assertTrue(Hash::check("SatryaPIC{$dept}", $user->password));
        }
    }

    public function test_all_thirty_nine_school_classes_map_exclusively_to_their_pic(): void
    {
        $expectedCounts = ['AKUN' => 6, 'MPLB' => 6, 'DKV' => 6, 'TJKT' => 6, 'TM' => 9, 'TITL' => 6];
        $classNames = [];
        foreach (['X', 'XI', 'XII'] as $grade) {
            foreach ($expectedCounts as $dept => $total) {
                $maxRom = $total / 3;
                for ($n = 1; $n <= $maxRom; $n++) {
                    $classNames[] = "{$grade} {$dept} {$n}";
                }
            }
        }

        foreach ($classNames as $name) {
            StudentClass::create(['name' => $name, 'description' => $name]);
        }

        $matchedAll = [];
        foreach ($expectedCounts as $dept => $expected) {
            $pic = User::factory()->create(['role' => 'admin', 'department' => $dept, 'status' => 'active']);
            $ids = $pic->departmentClassIds();
            $this->assertCount($expected, $ids);
            $matchedAll = array_merge($matchedAll, $ids);
        }

        $this->assertCount(39, array_unique($matchedAll));
    }

    public function test_demo_seeder_isolates_tjkt_violator_from_tm_pic(): void
    {
        $this->seed(DepartmentIsolationDemoSeeder::class);

        $picTm = User::where('username', 'pic_tm')->firstOrFail();
        $picTjkt = User::where('username', 'pic_tjkt')->firstOrFail();

        $this->actingAs($picTm)->get(route('admin.unlock.index'))
            ->assertOk()
            ->assertSee('Raka Saputra (Demo TM)')
            ->assertDontSee('Bima Pratama (Demo TJKT)');

        $this->actingAs($picTjkt)->get(route('admin.unlock.index'))
            ->assertOk()
            ->assertSee('Bima Pratama (Demo TJKT)')
            ->assertDontSee('Raka Saputra (Demo TM)');
    }

    public function test_unlock_export_generates_overall_and_per_class_xlsx_sheets(): void
    {
        $this->seed(DepartmentIsolationDemoSeeder::class);

        $superAdmin = User::factory()->create(['role' => 'admin', 'department' => null, 'status' => 'active']);
        $picTjkt = User::where('username', 'pic_tjkt')->firstOrFail();

        // 1. Super Admin -> Sheet 1 Keseluruhan (2 siswa), Sheet 2 XI TJKT 1, Sheet 3 XI TM 1
        $resAdmin = $this->actingAs($superAdmin)->get(route('admin.unlock.export'));
        $resAdmin->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $resAdmin->headers->get('content-type')
        );

        $tmpAdmin = tempnam(sys_get_temp_dir(), 'xlsx_admin_');
        file_put_contents($tmpAdmin, $resAdmin->streamedContent());
        try {
            $wbAdmin = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpAdmin);
            $this->assertSame(['Keseluruhan', 'XI TJKT 1', 'XI TM 1'], $wbAdmin->getSheetNames());
            $this->assertSame('Bima Pratama (Demo TJKT)', $wbAdmin->getSheet(1)->getCell('C2')->getValue());
            $this->assertSame('Raka Saputra (Demo TM)', $wbAdmin->getSheet(2)->getCell('C2')->getValue());
            $this->assertSame(3, $wbAdmin->getSheet(0)->getHighestRow());
        } finally {
            @unlink($tmpAdmin);
        }

        // 2. PIC TJKT -> hanya Sheet Keseluruhan & XI TJKT 1 (tidak ada XI TM 1)
        $resPic = $this->actingAs($picTjkt)->get(route('admin.unlock.export'));
        $resPic->assertOk();

        $tmpPic = tempnam(sys_get_temp_dir(), 'xlsx_pic_');
        file_put_contents($tmpPic, $resPic->streamedContent());
        try {
            $wbPic = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPic);
            $this->assertSame(['Keseluruhan', 'XI TJKT 1'], $wbPic->getSheetNames());
            $this->assertSame(2, $wbPic->getSheet(0)->getHighestRow());
            $this->assertSame('Bima Pratama (Demo TJKT)', $wbPic->getSheet(0)->getCell('C2')->getValue());
        } finally {
            @unlink($tmpPic);
        }
    }

    public function test_department_pic_scopes_unlock_and_exams_even_on_shared_exam(): void
    {
        $tjktClass = StudentClass::create(['name' => 'XI TJKT 1', 'description' => 'TJKT']);
        $dkvClass = StudentClass::create(['name' => 'XI DKV 1', 'description' => 'DKV']);
        $tmClass = StudentClass::create(['name' => 'XI TM 1', 'description' => 'TM']);
        $otherClass = StudentClass::create(['name' => 'XI OTOMOTIF 1', 'description' => 'Jangan cocok ke TM']);

        $superAdmin = User::factory()->create(['role' => 'admin', 'department' => null, 'status' => 'active']);
        $picTjkt = User::factory()->create(['role' => 'admin', 'department' => 'TJKT', 'status' => 'active']);
        $picTm = User::factory()->create(['role' => 'admin', 'department' => 'TM', 'status' => 'active']);

        // Pastikan TM hanya cocok ke XI TM 1, bukan XI OTOMOTIF 1
        $this->assertSame([$tmClass->id], $picTm->departmentClassIds());
        $this->assertNull($superAdmin->departmentClassIds());

        $sTjkt = User::factory()->create(['name' => 'Siswa TJKT', 'username' => 'tjkt01', 'role' => 'siswa', 'class_id' => $tjktClass->id, 'status' => 'active']);
        $sDkv = User::factory()->create(['name' => 'Siswa DKV', 'username' => 'dkv01', 'role' => 'siswa', 'class_id' => $dkvClass->id, 'status' => 'active']);

        $sharedUas = Exam::create([
            'title' => 'UAS Bahasa Indonesia',
            'type' => 'uas',
            'google_form_url' => 'https://forms.gle/shared',
            'start_at' => now()->subMinutes(15),
            'end_at' => now()->addHour(),
            'duration' => 90,
            'max_violation' => 3,
            'status' => 'active',
            'created_by' => $superAdmin->id,
        ]);
        $sharedUas->classes()->sync([$tjktClass->id, $dkvClass->id]);

        $dkvOnlyExam = Exam::create([
            'title' => 'Ujian Produktif DKV',
            'type' => 'uas',
            'google_form_url' => 'https://forms.gle/dkv',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addHour(),
            'duration' => 60,
            'max_violation' => 3,
            'status' => 'active',
            'created_by' => $superAdmin->id,
        ]);
        $dkvOnlyExam->classes()->sync([$dkvClass->id]);

        foreach ([$sTjkt, $sDkv] as $stu) {
            ExamParticipant::create(['exam_id' => $sharedUas->id, 'user_id' => $stu->id, 'status' => 'locked']);
            $sess = ExamSession::create([
                'exam_id' => $sharedUas->id,
                'user_id' => $stu->id,
                'status' => 'LOCKED',
                'session_token' => 'tok_' . $stu->id,
                'started_at' => now()->subMinutes(10),
                'expired_at' => now()->addHour(),
            ]);
            Violation::create([
                'exam_id' => $sharedUas->id,
                'user_id' => $stu->id,
                'session_id' => $sess->id,
                'type' => 'APP_SWITCH',
                'description' => 'Keluar app',
            ]);
        }

        // 1. Buka Kunci center: PIC TJKT hanya melihat Siswa TJKT, tidak melihat Siswa DKV
        $unlockRes = $this->actingAs($picTjkt)->get(route('admin.unlock.index'));
        $unlockRes->assertOk()
            ->assertSee('Siswa TJKT')
            ->assertDontSee('Siswa DKV');

        // 2. PIC TJKT dilarang buka kunci Siswa DKV walaupun dari ujian UAS yang sama
        $this->actingAs($picTjkt)
            ->post(route('admin.exams.students.unlock', [$sharedUas->id, $sDkv->id]))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('exam_sessions', ['user_id' => $sDkv->id, 'status' => 'LOCKED']);

        // 3. PIC TJKT membuka kunci Siswa TJKT -> sukses & tercatat unlocked_at
        $this->actingAs($picTjkt)
            ->post(route('admin.exams.students.unlock', [$sharedUas->id, $sTjkt->id]))
            ->assertSessionHas('success');
        $session = ExamSession::where('user_id', $sTjkt->id)->firstOrFail();
        $firstUnlockTime = $session->unlocked_at;
        $this->assertSame('ACTIVE', $session->status);
        $this->assertNotNull($firstUnlockTime);

        // 4. Double-unlock oleh admin/tab lain tidak error & tidak menimpa unlocked_at
        $this->travel(5)->minutes();
        $this->actingAs($superAdmin)
            ->post(route('admin.exams.students.unlock', [$sharedUas->id, $sTjkt->id]))
            ->assertSessionHas('success');
        $this->assertTrue($session->fresh()->unlocked_at->equalTo($firstUnlockTime));

        // 5. Jadwal Ujian index & show: PIC TJKT lihat shared UAS (hanya siswa/kelas TJKT) & tidak lihat ujian khusus DKV
        $this->actingAs($picTjkt)->get(route('admin.exams.index'))
            ->assertOk()
            ->assertSee('UAS Bahasa Indonesia')
            ->assertDontSee('Ujian Produktif DKV');

        $this->actingAs($picTjkt)->get(route('admin.exams.show', $sharedUas->id))
            ->assertOk()
            ->assertSee('Siswa TJKT')
            ->assertDontSee('Siswa DKV');

        $this->actingAs($picTjkt)->get(route('admin.exams.show', $dkvOnlyExam->id))
            ->assertRedirect(route('admin.exams.index'))
            ->assertSessionHas('error');

        // 6. Dashboard: PIC TJKT hanya melihat ujian & jumlah kelas jurusannya
        $this->actingAs($picTjkt)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('PIC Jurusan TJKT')
            ->assertSee('UAS Bahasa Indonesia')
            ->assertDontSee('Ujian Produktif DKV');

        // 7. Super Admin tetap melihat kedua jurusan
        $this->actingAs($superAdmin)->get(route('admin.unlock.index'))
            ->assertOk()
            ->assertSee('Siswa DKV');
    }
}
