<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'guru')
            ->withCount('createdExams')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->paginate(20)->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama lengkap guru wajib diisi.',
            'username.required' => 'Username / NIP wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan oleh akun lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'status.required' => 'Status akun wajib dipilih.',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email ?: null,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'status' => $request->status,
        ]);

        return redirect()->route('admin.teachers.index')->with('success', 'Akun guru berhasil ditambahkan.');
    }

    public function edit(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            abort(404);
        }

        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        if (!$teacher->isTeacher()) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $teacher->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $teacher->id,
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama lengkap guru wajib diisi.',
            'username.required' => 'Username / NIP wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan oleh akun lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'status.required' => 'Status akun wajib dipilih.',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email ?: null,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return redirect()->route('admin.teachers.index')->with('success', 'Data akun guru berhasil diperbarui.');
    }

    public function destroy(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            abort(404);
        }

        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'Akun guru berhasil dihapus.');
    }

    public function import(Request $request)
    {
        if ($request->boolean('confirm_import')) {
            $readyRows = \App\Helpers\ImportPreview::pullPayload((string) $request->input('import_token', ''));
            if ($readyRows === null) {
                return redirect()->route('admin.teachers.index')->with('error', 'Sesi pratinjau import sudah kedaluwarsa. Silakan unggah ulang file.');
            }
            if (empty($readyRows)) {
                return redirect()->route('admin.teachers.index')->with('error', 'Tidak ada data guru valid untuk disimpan.');
            }

            @set_time_limit(300);
            $count = 0;

            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($readyRows, &$count) {
                    $now = now();
                    $usernames = array_column($readyRows, 'username');
                    $existingUsers = collect();
                    foreach (array_chunk(array_unique($usernames), 500) as $uChunk) {
                        foreach (User::whereIn('username', $uChunk)->get() as $u) {
                            $existingUsers->put($u->username, $u);
                        }
                    }

                    $existingEmails = User::whereNotNull('email')->pluck('id', 'email')->all();
                    $hashCache = [];
                    $hashPassword = function (string $plain) use (&$hashCache): string {
                        return $hashCache[$plain] ??= Hash::make($plain);
                    };

                    $insertBuffer = [];
                    $flushInserts = function () use (&$insertBuffer) {
                        if (empty($insertBuffer)) {
                            return;
                        }
                        foreach (array_chunk($insertBuffer, 500) as $chunk) {
                            User::insert($chunk);
                        }
                        $insertBuffer = [];
                    };

                    foreach ($readyRows as $item) {
                        $existingUser = $existingUsers->get($item['username']);
                        $email = $item['email'];
                        if ($email !== null && isset($existingEmails[$email]) && (!$existingUser || $existingEmails[$email] !== $existingUser->id)) {
                            $email = null;
                        }

                        if ($existingUser) {
                            if ($existingUser->role !== 'guru') {
                                continue;
                            }
                            $updateData = ['name' => $item['name'], 'status' => $item['status']];
                            if ($email !== null) {
                                $updateData['email'] = $email;
                                $existingEmails[$email] = $existingUser->id;
                            }
                            if ($item['password'] !== null) {
                                $updateData['password'] = $hashPassword($item['password']);
                            }
                            $existingUser->update($updateData);
                        } else {
                            if ($email !== null) {
                                $existingEmails[$email] = true;
                            }
                            $insertBuffer[] = [
                                'name' => $item['name'],
                                'username' => $item['username'],
                                'email' => $email,
                                'password' => $hashPassword($item['password'] ?? $item['username']),
                                'role' => 'guru',
                                'status' => $item['status'],
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
                return redirect()->route('admin.teachers.index')->with('error', 'Terjadi kesalahan database saat mengimpor guru: ' . $e->getMessage());
            }

            return redirect()->route('admin.teachers.index')->with('success', "Berhasil memproses & menyinkronkan {$count} data guru.");
        }

        $request->validate(
            ['file' => 'required|file|mimes:csv,txt,xlsx|max:15360'],
            [
                'file.required' => 'Pilih file terlebih dahulu sebelum mengklik upload.',
                'file.mimes' => 'Format file harus berupa CSV (.csv) atau Excel (.xlsx).',
                'file.max' => 'Ukuran file maksimal adalah 15 MB.',
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

        $usernames = [];
        foreach ($rows as $r) {
            $u = trim((string) ($r['nip_username'] ?? $r['nip'] ?? $r['username'] ?? ''));
            if ($u !== '') {
                $usernames[] = $u;
            }
        }

        $existingUsers = collect();
        foreach (array_chunk(array_unique($usernames), 500) as $uChunk) {
            foreach (User::whereIn('username', $uChunk)->get() as $u) {
                $existingUsers->put($u->username, $u);
            }
        }
        $existingEmails = User::whereNotNull('email')->pluck('id', 'email')->all();

        $previewRows = [];
        $readyPayload = [];
        $seenUsernames = [];

        foreach ($rows as $idx => $row) {
            $excelRow = $idx + 2;
            $name = trim((string) ($row['nama_lengkap'] ?? $row['nama'] ?? $row['name'] ?? ''));
            $username = trim((string) ($row['nip_username'] ?? $row['nip'] ?? $row['username'] ?? ''));
            $emailInput = trim((string) ($row['email'] ?? ''));

            $cells = [
                'nip' => $username ?: '-',
                'name' => $name ?: '-',
                'email' => $emailInput ?: '-',
            ];

            if ($username === '' || $name === '') {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'error', 'note' => 'Nama lengkap dan NIP/Username wajib diisi. (akan dilewati)'];
                continue;
            }

            if (isset($seenUsernames[$username])) {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'duplicate', 'note' => "NIP/Username '{$username}' duplikat di dalam file. (akan dilewati)"];
                continue;
            }
            $seenUsernames[$username] = true;

            $existingUser = $existingUsers->get($username);
            if ($existingUser && $existingUser->role !== 'guru') {
                $previewRows[] = ['excel_row' => $excelRow, 'cells' => $cells, 'status' => 'error', 'note' => "Username '{$username}' sudah dipakai akun Admin/Siswa. (akan dilewati)"];
                continue;
            }

            $validEmail = ($emailInput !== '' && filter_var($emailInput, FILTER_VALIDATE_EMAIL)) ? strtolower($emailInput) : null;
            $emailNote = '';
            if ($emailInput !== '' && $validEmail === null) {
                $emailNote = ' (format email tidak valid, dikosongkan)';
            } elseif ($validEmail !== null && isset($existingEmails[$validEmail]) && (!$existingUser || $existingEmails[$validEmail] !== $existingUser->id)) {
                $validEmail = null;
                $emailNote = ' (email sudah dipakai akun lain, dikosongkan)';
            } elseif ($validEmail !== null) {
                $existingEmails[$validEmail] = $existingUser?->id ?? true;
            }

            $status = $existingUser?->status ?? 'active';
            if (isset($row['status'])) {
                $statusVal = strtolower(trim((string) $row['status']));
                $status = in_array($statusVal, ['inactive', 'nonaktif', '0', 'disabled']) ? 'inactive' : 'active';
            }

            $passInput = $row['password'] ?? $row['kata_sandi'] ?? null;

            $readyPayload[] = [
                'name' => $name,
                'username' => $username,
                'email' => $validEmail,
                'status' => $status,
                'password' => !empty($passInput) ? (string) $passInput : null,
            ];

            $previewRows[] = [
                'excel_row' => $excelRow,
                'cells' => $cells,
                'status' => 'ready',
                'note' => ($existingUser ? 'Data guru sudah ada, akan diperbarui.' : 'Siap ditambahkan sebagai guru baru.') . $emailNote,
            ];
        }

        return \App\Helpers\ImportPreview::storeAndRender([
            'entityLabel' => 'Guru',
            'unitLabel' => 'guru',
            'backRoute' => route('admin.teachers.index'),
            'actionRoute' => route('admin.teachers.import'),
            'columns' => ['nip' => 'NIP / Username', 'name' => 'Nama Guru', 'email' => 'Email'],
            'fileName' => $request->file('file')->getClientOriginalName(),
        ], $previewRows, $readyPayload);
    }
}
