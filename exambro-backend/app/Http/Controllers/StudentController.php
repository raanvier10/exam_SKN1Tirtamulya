<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('users.role', 'siswa')
            ->with('class')
            ->leftJoin('classes', 'users.class_id', '=', 'classes.id')
            ->select('users.*')
            ->orderByRaw('classes.name IS NULL, classes.name ASC')
            ->orderBy('users.name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('users.class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $query->where('users.status', $request->status);
        }

        if ($request->filled('is_pkl')) {
            $query->where('users.is_pkl', $request->boolean('is_pkl'));
        }

        $students = $query->paginate(25)->withQueryString();
        $classes = StudentClass::orderBy('name')->get();
        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = StudentClass::all();
        return view('admin.students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'class_id' => 'nullable|exists:classes,id',
            'student_class_id' => 'nullable|exists:classes,id',
            'status' => 'required|in:active,inactive',
            'is_pkl' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'class_id' => $request->class_id ?? $request->student_class_id,
            'status' => $request->status,
            'is_pkl' => $request->boolean('is_pkl'),
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function edit(User $student)
    {
        if ($student->role !== 'siswa') {
            abort(404);
        }

        $classes = StudentClass::all();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, User $student)
    {
        if ($student->role !== 'siswa') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $student->id,
            'password' => 'nullable|string|min:6',
            'class_id' => 'nullable|exists:classes,id',
            'student_class_id' => 'nullable|exists:classes,id',
            'status' => 'required|in:active,inactive',
            'is_pkl' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'class_id' => $request->class_id ?? $request->student_class_id,
            'status' => $request->status,
            'is_pkl' => $request->boolean('is_pkl'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $student->update($data);

        if ($data['status'] !== 'active') {
            $student->forceFill(['api_token' => null])->save();
        }

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diupdate');
    }

    public function togglePkl(User $student)
    {
        if ($student->role !== 'siswa') {
            abort(404);
        }

        $student->update([
            'is_pkl' => !$student->is_pkl,
        ]);

        $statusStr = $student->is_pkl ? 'PKL (Bebas GPS)' : 'Reguler (Wajib GPS)';
        return back()->with('success', "Status siswa {$student->name} diubah menjadi {$statusStr}.");
    }

    public function destroy(User $student)
    {
        if ($student->role !== 'siswa') {
            abort(404);
        }

        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus');
    }

    public function destroyBulk(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $count = User::whereIn('id', $request->student_ids)
            ->where('role', 'siswa')
            ->delete();

        if ($count > 0) {
            return redirect()->route('admin.students.index')->with('success', "{$count} siswa berhasil dihapus");
        }

        return redirect()->route('admin.students.index')->with('error', 'Tidak ada data siswa yang dapat dihapus');
    }

    public function import(Request $request)
    {
        if ($request->boolean('confirm_import')) {
            $readyRows = \App\Helpers\ImportPreview::pullPayload((string) $request->input('import_token', ''));
            if ($readyRows === null) {
                return redirect()->route('admin.students.index')->with('error', 'Sesi pratinjau import sudah kedaluwarsa. Silakan unggah ulang file.');
            }
            if (empty($readyRows)) {
                return redirect()->route('admin.students.index')->with('error', 'Tidak ada data siswa valid untuk disimpan.');
            }

            @set_time_limit(300);
            $count = 0;

            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($readyRows, &$count) {
                    $now = now();
                    $usernames = array_column($readyRows, 'username');
                    $existingUsers = collect();
                    foreach (array_chunk(array_unique($usernames), 500) as $uChunk) {
                        foreach (\App\Models\User::whereIn('username', $uChunk)->get() as $u) {
                            $existingUsers->put($u->username, $u);
                        }
                    }

                    $hashCache = [];
                    $hashPassword = function (string $plain) use (&$hashCache): string {
                        return $hashCache[$plain] ??= \Illuminate\Support\Facades\Hash::make($plain);
                    };

                    $insertBuffer = [];
                    $flushInserts = function () use (&$insertBuffer) {
                        if (empty($insertBuffer)) {
                            return;
                        }
                        foreach (array_chunk($insertBuffer, 500) as $chunk) {
                            \App\Models\User::insert($chunk);
                        }
                        $insertBuffer = [];
                    };

                    foreach ($readyRows as $item) {
                        $existingUser = $existingUsers->get($item['username']);
                        if ($existingUser) {
                            if ($existingUser->role !== 'siswa') {
                                continue;
                            }
                            $updateData = [
                                'name' => $item['name'],
                                'class_id' => $item['class_id'] ?? $existingUser->class_id,
                                'status' => $item['status'],
                                'is_pkl' => $item['is_pkl'],
                            ];
                            if ($item['password'] !== null) {
                                $updateData['password'] = $hashPassword($item['password']);
                            }
                            $existingUser->update($updateData);
                        } else {
                            $insertBuffer[] = [
                                'name' => $item['name'],
                                'username' => $item['username'],
                                'password' => $hashPassword($item['password'] ?? $item['username']),
                                'role' => 'siswa',
                                'class_id' => $item['class_id'],
                                'status' => $item['status'],
                                'is_pkl' => $item['is_pkl'] ? 1 : 0,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            if (count($insertBuffer) >= 500) {
                                $flushInserts();
                            }
                        }
                        $count++;
                    }

                    $flushInserts();
                });
            } catch (\Throwable $e) {
                return redirect()->route('admin.students.index')->with('error', 'Terjadi kesalahan database saat mengimpor siswa: ' . $e->getMessage());
            }

            return redirect()->route('admin.students.index')->with('success', "Berhasil memproses & menyinkronkan {$count} data siswa.");
        }

        $request->validate(
            ['file' => 'required|file|mimes:csv,txt,xlsx|max:15360'],
            [
                'file.required' => 'Pilih file terlebih dahulu sebelum mengklik upload.',
                'file.mimes' => 'Format file harus berupa CSV (.csv) atau Excel (.xlsx). Jika file berekstensi .xls lama, silakan buka di Excel lalu Save As ke format .xlsx atau .csv.',
                'file.max' => 'Ukuran file maksimal adalah 15 MB.',
            ]
        );

        @set_time_limit(300);

        try {
            $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca isi file: ' . $e->getMessage() . '. Pastikan file tidak terkunci atau rusak.');
        }

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau tidak memiliki baris data yang terbaca.');
        }

        $allClasses = StudentClass::all();
        $classesById = $allClasses->keyBy('id');
        $classesByName = $allClasses->keyBy(fn ($c) => strtolower(trim($c->name)));

        $usernames = [];
        foreach ($rows as $r) {
            $u = trim((string) ($r['nis_username'] ?? $r['nisn'] ?? $r['nis'] ?? $r['no_peserta'] ?? $r['nomor_peserta'] ?? $r['username'] ?? ''));
            if ($u !== '') {
                $usernames[] = $u;
            }
        }

        $existingUsers = collect();
        foreach (array_chunk(array_unique($usernames), 500) as $uChunk) {
            foreach (\App\Models\User::whereIn('username', $uChunk)->get() as $u) {
                $existingUsers->put($u->username, $u);
            }
        }

        $previewRows = [];
        $readyPayload = [];
        $seenUsernames = [];

        foreach ($rows as $idx => $row) {
            $excelRow = $idx + 2;
            $name = trim((string) ($row['nama_lengkap'] ?? $row['nama_siswa'] ?? $row['nama'] ?? $row['name'] ?? ''));
            $username = trim((string) ($row['nis_username'] ?? $row['nisn'] ?? $row['nis'] ?? $row['no_peserta'] ?? $row['nomor_peserta'] ?? $row['username'] ?? ''));
            $classInput = trim((string) ($row['nama_kelas'] ?? $row['kelas'] ?? $row['rombel'] ?? $row['id_kelas'] ?? $row['class_id'] ?? $row['class'] ?? ''));

            $cells = [
                'nis' => $username ?: '-',
                'name' => $name ?: '-',
                'class' => $classInput ?: '-',
            ];

            if ($username === '' || $name === '') {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'error', 'note' => 'Nama lengkap dan NIS/Username wajib diisi. (akan dilewati)'];
                continue;
            }

            if (isset($seenUsernames[$username])) {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'duplicate', 'note' => "NIS '{$username}' duplikat di dalam file. (akan dilewati)"];
                continue;
            }
            $seenUsernames[$username] = true;

            $existingUser = $existingUsers->get($username);
            if ($existingUser && $existingUser->role !== 'siswa') {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'error', 'note' => "Username '{$username}' sudah dipakai akun Admin/Guru. (akan dilewati)"];
                continue;
            }

            $classId = $existingUser?->class_id ?? null;
            if ($classInput !== '') {
                if (is_numeric($classInput) && $classesById->has((int) $classInput)) {
                    $classId = (int) $classInput;
                } else {
                    $foundClass = $classesByName->get(strtolower($classInput));
                    if (!$foundClass) {
                        $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'error', 'note' => "Kelas '{$classInput}' tidak ditemukan dalam master kelas. (akan dilewati)"];
                        continue;
                    }
                    $classId = $foundClass->id;
                }
            }

            $status = $existingUser?->status ?? 'active';
            if (isset($row['status'])) {
                $statusVal = strtolower(trim((string) $row['status']));
                $status = in_array($statusVal, ['inactive', 'nonaktif', '0', 'disabled']) ? 'inactive' : 'active';
            }

            $isPkl = $existingUser?->is_pkl ?? false;
            if (isset($row['is_pkl']) || isset($row['status_pkl']) || isset($row['pkl'])) {
                $val = strtolower(trim((string) ($row['is_pkl'] ?? $row['status_pkl'] ?? $row['pkl'])));
                $isPkl = in_array($val, ['1', 'true', 'ya', 'yes', 'pkl', 'aktif']);
            }

            $passInput = $row['password'] ?? $row['kata_sandi'] ?? null;

            $readyPayload[] = [
                'name' => $name,
                'username' => $username,
                'class_id' => $classId,
                'status' => $status,
                'is_pkl' => $isPkl,
                'password' => !empty($passInput) ? (string) $passInput : null,
            ];

            $previewRows[] = [
                'excel_row' => $excelRow,
                'cells' => $cells,
                'status' => 'ready',
                'note' => $existingUser ? 'Data siswa sudah ada, akan diperbarui.' : 'Siap ditambahkan sebagai siswa baru.',
            ];
        }

        return \App\Helpers\ImportPreview::storeAndRender([
            'entityLabel' => 'Siswa',
            'unitLabel' => 'siswa',
            'backRoute' => route('admin.students.index'),
            'actionRoute' => route('admin.students.import'),
            'columns' => ['nis' => 'NIS / Username', 'name' => 'Nama Lengkap', 'class' => 'Kelas'],
            'fileName' => $request->file('file')->getClientOriginalName(),
        ], $previewRows, $readyPayload);
    }
}
