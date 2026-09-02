@extends('layouts.admin')
@section('title', 'Master Siswa')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Siswa</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola akun dan kredensial akses siswa.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="importModal.showModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-indigo-600 shadow-xs transition-all duration-150 active:scale-[0.98]">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import Excel
        </button>
        <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Siswa
        </a>
    </div>
</div>

<dialog id="importModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-white">
        <h3 class="text-lg font-bold text-slate-900">Import Data Siswa</h3>
        <p class="text-sm text-slate-500 mt-0.5">Gunakan format CSV atau Excel yang valid.</p>
    </div>
    
    <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-white space-y-4">
        @csrf
        
        <div class="flex items-start gap-3 p-4 bg-indigo-50/40 border border-indigo-100 rounded-xl">
            <div class="p-2 bg-white shadow-xs border border-indigo-100 text-indigo-600 rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Belum punya template?</h4>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Gunakan format yang sudah disediakan agar tidak gagal.</p>
                <a href="{{ route('admin.students.template') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    Download Template CSV &rarr;
                </a>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File Excel/CSV</label>
            <div class="mt-1 flex justify-center rounded-xl border-2 border-dashed border-slate-200 px-6 py-7 hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-students').click()">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <div class="mt-3 flex text-sm leading-6 text-slate-600 justify-center">
                        <span class="relative font-semibold text-indigo-600 hover:text-indigo-500">
                            Click to upload
                            <input id="file-upload-students" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="document.getElementById('file-name-students').textContent = this.files[0].name">
                        </span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs leading-5 text-slate-400 mt-0.5" id="file-name-students">CSV, XLS, XLSX up to 10MB</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" onclick="importModal.close()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-xs">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-colors">Upload Data</button>
        </div>
    </form>
</dialog>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/80 border-b border-slate-200/80 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Username / NIS</th>
                    <th class="px-6 py-4">Kelas</th>
                    <th class="px-6 py-4">Status Akun</th>
                    <th class="px-6 py-4">Status PKL (GPS)</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $s)
                <tr class="hover:bg-indigo-50/20 transition-colors duration-150 group">
                    <td class="px-6 py-4 text-slate-900 font-semibold">{{ $s->name }}</td>
                    <td class="px-6 py-4 font-mono text-slate-500 text-xs">{{ $s->username }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/60">
                            {{ $s->class ? $s->class->name : '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($s->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.students.toggle-pkl', $s->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer {{ $s->is_pkl ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-500/30 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 ring-1 ring-blue-500/30 hover:bg-blue-100' }}" title="Klik untuk mengubah status PKL">
                                @if($s->is_pkl)
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>PKL (GPS Off)</span>
                                @else
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Reguler (GPS On)</span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right space-x-1.5">
                        <a href="{{ route('admin.students.edit', $s->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-2xs transition-all duration-150">Edit</a>
                        <form action="{{ route('admin.students.destroy', $s->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus siswa ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-2xs transition-all duration-150">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        Belum ada data siswa yang terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
