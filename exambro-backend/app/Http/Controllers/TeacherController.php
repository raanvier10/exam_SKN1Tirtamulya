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
}
