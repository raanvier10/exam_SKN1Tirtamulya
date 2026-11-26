<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    public function index()
    {
        $classes = StudentClass::all();
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.classes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classes,name',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah terdaftar.',
        ]);

        StudentClass::create($validated);
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(StudentClass $class)
    {
        return view('admin.classes.edit', compact('class'));
    }

    public function update(Request $request, StudentClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classes,name,' . $class->id,
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah terdaftar.',
        ]);

        $class->update($validated);
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(StudentClass $class)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($class) {
            $class->exams()->detach();
            $class->users()->update(['class_id' => null]);
            $class->delete();
        });

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function import(Request $request)
    {
        if ($request->boolean('confirm_import')) {
            $readyRows = \App\Helpers\ImportPreview::pullPayload((string) $request->input('import_token', ''));
            if ($readyRows === null) {
                return redirect()->route('admin.classes.index')->with('error', 'Sesi pratinjau import sudah kedaluwarsa. Silakan unggah ulang file.');
            }
            if (empty($readyRows)) {
                return redirect()->route('admin.classes.index')->with('error', 'Tidak ada data kelas valid untuk disimpan.');
            }

            @set_time_limit(300);
            $count = count($readyRows);

            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($readyRows) {
                    $now = now();
                    $insertBuffer = array_map(fn ($r) => [
                        'name' => $r['name'],
                        'description' => $r['description'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $readyRows);

                    foreach (array_chunk($insertBuffer, 500) as $chunk) {
                        StudentClass::insert($chunk);
                    }
                });
            } catch (\Throwable $e) {
                return redirect()->route('admin.classes.index')->with('error', 'Terjadi kesalahan database saat mengimpor kelas: ' . $e->getMessage());
            }

            return redirect()->route('admin.classes.index')->with('success', "Berhasil mengimpor {$count} data kelas baru.");
        }

        $request->validate(
            ['file' => 'required|file|mimes:csv,txt,xlsx|max:10240'],
            [
                'file.required' => 'Pilih file terlebih dahulu sebelum mengklik upload.',
                'file.mimes' => 'Format file harus berupa CSV (.csv) atau Excel (.xlsx).',
            ]
        );

        @set_time_limit(300);

        try {
            $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca isi file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau tidak memiliki baris data yang terbaca.');
        }

        $existingNames = StudentClass::pluck('name')
            ->mapWithKeys(fn ($n) => [strtolower(trim($n)) => true])
            ->all();

        $previewRows = [];
        $readyPayload = [];
        $seenNames = [];

        foreach ($rows as $idx => $row) {
            $excelRow = $idx + 2;
            $className = trim((string) ($row['nama_kelas'] ?? $row['kelas'] ?? $row['name'] ?? ''));
            $desc = $row['deskripsi'] ?? $row['description'] ?? null;

            $cells = [
                'name' => $className ?: '-',
                'description' => $desc ?: '-',
            ];

            if ($className === '') {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'error', 'note' => 'Nama kelas wajib diisi. (akan dilewati)'];
                continue;
            }

            $lower = strtolower($className);
            if (isset($existingNames[$lower]) || isset($seenNames[$lower])) {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'duplicate', 'note' => "Kelas '{$className}' sudah ada. (akan dilewati)"];
                continue;
            }
            $seenNames[$lower] = true;

            $readyPayload[] = ['name' => $className, 'description' => $desc];
            $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'ready', 'note' => 'Siap ditambahkan ke master kelas.'];
        }

        return \App\Helpers\ImportPreview::storeAndRender([
            'entityLabel' => 'Kelas',
            'unitLabel' => 'kelas',
            'backRoute' => route('admin.classes.index'),
            'actionRoute' => route('admin.classes.import'),
            'columns' => ['name' => 'Nama Kelas', 'description' => 'Deskripsi'],
            'fileName' => $request->file('file')->getClientOriginalName(),
        ], $previewRows, $readyPayload);
    }
}
