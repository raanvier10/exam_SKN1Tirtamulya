<?php

namespace Tests\Feature;

use App\Helpers\SimpleSpreadsheetReader;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_datetime_handles_excel_serial_strings_and_invalid_values(): void
    {
        $serial = SimpleSpreadsheetReader::parseDateTime('46252.375');
        $this->assertNotNull($serial);
        $this->assertSame('2026-08-18 09:00:00', $serial->format('Y-m-d H:i:s'));

        $slash = SimpleSpreadsheetReader::parseDateTime('18/08/2026 09:30');
        $this->assertNotNull($slash);
        $this->assertSame('2026-08-18 09:30:00', $slash->format('Y-m-d H:i:s'));

        $this->assertNull(SimpleSpreadsheetReader::parseDateTime('tanggal-ngawur'));
        $this->assertNull(SimpleSpreadsheetReader::parseDateTime(''));
    }

    public function test_bulk_imports_preview_validates_errors_and_confirms_cleanly(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 1. Import classes (preview -> confirm)
        $classCsv = "nama_kelas,deskripsi\nXI TJKT 1,Teknik Jaringan\nXI TJKT 1,Duplikat\nXII IPA 1,IPA\n";
        $classPreview = $this->actingAs($admin)->post(route('admin.classes.import'), [
            'file' => UploadedFile::fake()->createWithContent('classes.csv', $classCsv),
        ]);
        $classPreview->assertOk()->assertViewIs('admin.imports.preview');
        $this->assertSame(2, $classPreview->viewData('counts')['ready']);
        $this->assertSame(1, $classPreview->viewData('counts')['duplicate']);

        $this->actingAs($admin)->post(route('admin.classes.import'), [
            'confirm_import' => '1',
            'import_token' => $classPreview->viewData('token'),
        ])->assertSessionHas('success');
        $this->assertDatabaseCount('classes', 2);

        // 2. Import students (unknown class flagged as error, valid rows saved on confirm)
        $studentCsv = "nama_lengkap,nis_username,password,nama_kelas,status,is_pkl\n"
            . "Dimas Pratama,1001,rahasia123,XI TJKT 1,aktif,0\n"
            . "Siti Aisyah,1002,rahasia123,XII IPA 1,aktif,1\n"
            . "Aditya,1003,rahasia123,X TKJ 1,aktif,0\n"
            . ",1004,rahasia123,XI TJKT 1,aktif,0\n";
        $studentPreview = $this->actingAs($admin)->post(route('admin.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $studentCsv),
        ]);
        $studentPreview->assertOk()
            ->assertViewIs('admin.imports.preview')
            ->assertSee("Kelas 'X TKJ 1' tidak ditemukan dalam master kelas.");
        $this->assertSame(2, $studentPreview->viewData('counts')['ready']);
        $this->assertSame(2, $studentPreview->viewData('counts')['error']);

        $this->actingAs($admin)->post(route('admin.students.import'), [
            'confirm_import' => '1',
            'import_token' => $studentPreview->viewData('token'),
        ])->assertSessionHas('success');

        $dimas = User::where('username', '1001')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $dimas->password));
        $this->assertFalse($dimas->is_pkl);
        $this->assertDatabaseMissing('users', ['username' => '1003']);

        $siti = User::where('username', '1002')->firstOrFail();
        $this->assertTrue($siti->is_pkl);

        // 3. Import teachers (preview -> confirm)
        $teacherCsv = "nama_lengkap,nip_username,email,password,status\n"
            . "Pak Budi,198501,budi@sekolah.test,guru123,aktif\n"
            . "Bu Ani,198502,budi@sekolah.test,guru123,aktif\n";
        $teacherPreview = $this->actingAs($admin)->post(route('admin.teachers.import'), [
            'file' => UploadedFile::fake()->createWithContent('teachers.csv', $teacherCsv),
        ]);
        $teacherPreview->assertOk()->assertViewIs('admin.imports.preview');
        $this->actingAs($admin)->post(route('admin.teachers.import'), [
            'confirm_import' => '1',
            'import_token' => $teacherPreview->viewData('token'),
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['username' => '198501', 'role' => 'guru']);
        $this->assertDatabaseHas('users', ['username' => '198502', 'role' => 'guru', 'email' => null]);

        // 4. Import exams (valid + Excel serial + duplicate + bad date + missing class)
        $examCsv = "judul_ujian,target_kelas,deskripsi,url_google_form,waktu_mulai,durasi_menit,maksimal_pelanggaran,status\n"
            . "UAS Matematika,\"XI TJKT 1\",Tutup buku,https://forms.gle/abc123,2026-08-18 09:00,90,3,active\n"
            . "UAS Matematika,\"XI TJKT 1\",Duplikat,https://forms.gle/abc123,2026-08-18 09:00,90,3,active\n"
            . "UTS Fisika,\"XII IPA 1\",Serial Excel,https://forms.gle/def456,46252.375,60,2,nonaktif\n"
            . "Ujian Error Tanggal,\"XI TJKT 1\",Tanggal salah,https://forms.gle/ghi789,bukan-tanggal,60,3,active\n"
            . "Ujian Kelas Salah,\"X TKJ 9\",Kelas typo,https://forms.gle/jkl012,2026-08-18 09:00,60,3,active\n";

        $examPreview = $this->actingAs($admin)->post(route('admin.exams.import'), [
            'file' => UploadedFile::fake()->createWithContent('exams.csv', $examCsv),
        ]);
        $examPreview->assertOk()->assertViewIs('admin.imports.preview');
        $this->assertSame(2, $examPreview->viewData('counts')['ready']);
        $this->assertSame(1, $examPreview->viewData('counts')['duplicate']);
        $this->assertSame(2, $examPreview->viewData('counts')['error']);

        $this->actingAs($admin)->post(route('admin.exams.import'), [
            'confirm_import' => '1',
            'import_token' => $examPreview->viewData('token'),
        ])->assertSessionHas('success');

        $this->assertSame(2, Exam::count());
        $math = Exam::where('title', 'UAS Matematika')->firstOrFail();
        $fisika = Exam::where('title', 'UTS Fisika')->firstOrFail();
        $this->assertSame('inactive', $fisika->status);
        $this->assertSame(1, ExamParticipant::where('exam_id', $math->id)->count());
        $this->assertTrue(ExamParticipant::where('exam_id', $math->id)->where('user_id', $dimas->id)->exists());

        // 5. Edge cases: GET fallback redirect & corrupt .xlsx rejection
        $this->actingAs($admin)->get(route('admin.students.import'))
            ->assertRedirect(route('admin.students.index'));

        $this->actingAs($admin)->post(route('admin.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan-zip-xlsx'),
        ])->assertSessionHas('error');
    }
}
