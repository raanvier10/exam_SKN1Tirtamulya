<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'siswa')->with('class')->get();
        return view('admin.students.index', compact('students'));
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
        $classes = StudentClass::all();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, User $student)
    {
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

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diupdate');
    }

    public function togglePkl(User $student)
    {
        $student->update([
            'is_pkl' => !$student->is_pkl,
        ]);

        $statusStr = $student->is_pkl ? 'PKL (Bebas GPS)' : 'Reguler (Wajib GPS)';
        return back()->with('success', "Status siswa {$student->name} diubah menjadi {$statusStr}.");
    }

    public function destroy(User $student)
    {
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        $count = 0;
        foreach ($rows as $row) {
            $name = $row['nama_lengkap'] ?? $row['nama'] ?? $row['name'] ?? null;
            $username = (string)($row['nis_username'] ?? $row['nis'] ?? $row['username'] ?? '');

            if (!empty($username) && !empty($name)) {
                $existingUser = \App\Models\User::where('username', $username)->first();

                // Resolusi Kelas (Bisa nama kelas misal 'XII RPL 1' atau ID kelas angka)
                $classId = $existingUser?->class_id ?? null;
                $classInput = trim((string)($row['nama_kelas'] ?? $row['kelas'] ?? $row['id_kelas'] ?? $row['class_id'] ?? $row['class'] ?? ''));
                if (!empty($classInput)) {
                    if (is_numeric($classInput) && StudentClass::find($classInput)) {
                        $classId = (int)$classInput;
                    } else {
                        $foundClass = StudentClass::whereRaw('LOWER(name) = ?', [strtolower($classInput)])->first();
                        if ($foundClass) {
                            $classId = $foundClass->id;
                        } else {
                            $newClass = StudentClass::create(['name' => $classInput]);
                            $classId = $newClass->id;
                        }
                    }
                }

                // Resolusi Status (aktif / active vs nonaktif / inactive)
                $status = $existingUser?->status ?? 'active';
                if (isset($row['status'])) {
                    $statusVal = strtolower(trim((string)$row['status']));
                    $status = in_array($statusVal, ['inactive', 'nonaktif', '0', 'disabled']) ? 'inactive' : 'active';
                }

                $updateData = [
                    'name' => $name,
                    'role' => 'siswa',
                    'class_id' => $classId,
                    'status' => $status,
                ];

                // Update password jika diisi atau jika user baru (default NIS)
                $passInput = $row['password'] ?? $row['kata_sandi'] ?? null;
                if (!empty($passInput)) {
                    $updateData['password'] = \Illuminate\Support\Facades\Hash::make((string)$passInput);
                } elseif (!$existingUser) {
                    $updateData['password'] = \Illuminate\Support\Facades\Hash::make($username);
                }

                // Update status is_pkl (1 / 0 / ya / tidak / pkl / reguler)
                if (isset($row['is_pkl']) || isset($row['status_pkl']) || isset($row['pkl'])) {
                    $val = strtolower(trim((string)($row['is_pkl'] ?? $row['status_pkl'] ?? $row['pkl'])));
                    $updateData['is_pkl'] = in_array($val, ['1', 'true', 'ya', 'yes', 'pkl', 'aktif']);
                }

                \App\Models\User::updateOrCreate(
                    ['username' => $username],
                    $updateData
                );
                $count++;
            }
        }
        return back()->with('success', "Berhasil memproses & menyinkronkan {$count} data siswa.");
    }
}
