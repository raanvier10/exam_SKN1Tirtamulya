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
        $request->validate(['file' => 'required|file']);
        $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        $count = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                $className = trim((string)($row['nama_kelas'] ?? $row['kelas'] ?? $row['name'] ?? ''));
                if (!empty($className)) {
                    StudentClass::firstOrCreate(
                        ['name' => $className],
                        ['description' => $row['deskripsi'] ?? $row['description'] ?? null]
                    );
                    $count++;
                }
            }
        });

        return back()->with('success', "Berhasil mengimpor {$count} data kelas.");
    }
}
