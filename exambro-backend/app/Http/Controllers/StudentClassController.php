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
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        StudentClass::create($request->all());
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan');
    }

    public function edit(StudentClass $class)
    {
        return view('admin.classes.edit', compact('class'));
    }

    public function update(Request $request, StudentClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class->update($request->all());
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diupdate');
    }

    public function destroy(StudentClass $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $rows = \App\Helpers\SimpleSpreadsheetReader::read($request->file('file'));
        $count = 0;
        foreach ($rows as $row) {
            if (!empty($row['nama_kelas'])) {
                StudentClass::create([
                    'name' => $row['nama_kelas'],
                    'description' => $row['deskripsi'] ?? null,
                ]);
                $count++;
            }
        }
        return back()->with('success', "Berhasil mengimpor {$count} data kelas.");
    }
}
