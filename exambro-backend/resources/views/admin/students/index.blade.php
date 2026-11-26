@extends('layouts.admin')
@section('title', 'Master Siswa')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-[22px]">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Daftar Siswa</h2>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">Kelola akun dan kredensial akses siswa.</p>
    </div>
    <div class="flex items-center gap-2.5 shrink-0">
        <button onclick="importModal.showModal()" class="inline-flex items-center gap-[6px] px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs sm:text-sm font-semibold hover:bg-slate-50 hover:border-[#2457E8] hover:text-[#2457E8] shadow-2xs transition active:scale-[0.98] cursor-pointer whitespace-nowrap">
            <svg class="w-[15px] h-[15px] text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
        <button type="button" onclick="createModal.showModal()" class="inline-flex items-center gap-[6px] px-4 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer whitespace-nowrap">
            <svg class="w-[15px] h-[15px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Siswa</span>
        </button>
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
            
            <div id="dropzone-students-empty" class="mt-1 flex justify-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-7 hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-students').click()">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <div class="flex text-sm leading-6 text-slate-600 justify-center">
                        <span class="font-bold text-indigo-600 hover:text-indigo-500">Pilih file</span>
                        <p class="pl-1">atau tarik file ke sini</p>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">CSV, XLS, XLSX up to 10MB</p>
                </div>
            </div>

            <!-- Preview Card -->
            <div id="dropzone-students-preview" class="hidden mt-1 p-4 rounded-2xl border-2 border-emerald-500/40 bg-emerald-50/40 transition-all">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1.5 14h-7v-2h7v2zm0-4h-7v-2h7v2zm-2-5V3.5L18.5 7H13.5z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h5 id="preview-students-name" class="text-sm font-bold text-slate-900 truncate">file.csv</h5>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">✓ Siap Diupload</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                <span id="preview-students-size" class="font-medium tabular-nums text-slate-600">0 KB</span>
                                <span>•</span>
                                <span id="preview-students-type" class="uppercase font-semibold text-emerald-700">CSV</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="removeStudentsFile()" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors shrink-0" title="Ganti / Batalkan file">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <input id="file-upload-students" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="handleStudentsFile(this)">
        </div>

        <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" onclick="importModal.close()" class="px-4 py-2.5 text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer">Batal</button>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Periksa &amp; Tampilkan Pratinjau</span>
            </button>
        </div>
    </form>
</dialog>

<!-- Modal Tambah Siswa -->
<dialog id="createModal" class="backdrop:bg-slate-900/50 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-auto">
    <div class="bg-[#0D2149] px-6 py-4 text-white flex items-center justify-between gap-3 sticky top-0 z-10">
        <div>
            <h3 class="text-base font-bold tracking-tight">Tambah Siswa Baru</h3>
            <p class="text-xs text-blue-200 mt-0.5">Buat akun siswa baru untuk mengikuti ujian.</p>
        </div>
        <button type="button" onclick="createModal.close()" class="p-1.5 rounded-lg text-blue-200 hover:text-white hover:bg-white/10 transition" aria-label="Tutup">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <form action="{{ route('admin.students.store') }}" method="POST" autocomplete="off" class="bg-white">
        @csrf
        <input type="hidden" name="_form" value="create">

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('_form') === 'create' ? old('name') : '' }}" required autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15" placeholder="Contoh: Budi Santoso">
                    @if(old('_form') === 'create') @error('name') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">NIS / Username</label>
                    <input type="text" name="username" value="{{ old('_form') === 'create' ? old('username') : '' }}" required autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 tabular-nums" placeholder="Contoh: 12345678">
                    @if(old('_form') === 'create') @error('username') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required autocomplete="new-password" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15" placeholder="Minimal 6 karakter">
                    @if(old('_form') === 'create') @error('password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15" placeholder="Ulangi password">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Kelas</label>
                    <select name="student_class_id" required class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('_form') === 'create' && old('student_class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @if(old('_form') === 'create') @error('student_class_id') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                    <select name="status" required class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                        <option value="active" {{ old('_form') === 'create' ? (old('status', 'active') === 'active' ? 'selected' : '') : 'selected' }}>Aktif</option>
                        <option value="inactive" {{ old('_form') === 'create' && old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="p-4 bg-[#F5F8FF] border border-blue-100 rounded-xl">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <span class="block text-sm font-bold text-[#142C5C]">Siswa Sedang PKL / Magang Luar Sekolah</span>
                        <p class="text-xs text-slate-600 mt-0.5">Jika diaktifkan, pengecekan radius lokasi &amp; GPS di HP siswa akan otomatis dilewati saat ujian.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="is_pkl" value="1" {{ old('_form') === 'create' && old('is_pkl') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2457E8]"></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-end gap-2.5">
            <button type="button" onclick="createModal.close()" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs sm:text-sm font-semibold transition">Batal</button>
            <button type="submit" class="px-5 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">Simpan Siswa</button>
        </div>
    </form>
</dialog>
@if(old('_form') === 'create' && $errors->any())
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('createModal')?.showModal());</script>
@endif

<div class="card">
    <div class="card-header bg-[#0D2149] border-[#142C5C] text-white rounded-t-[12px] flex-col items-start gap-0.5">
        <h3 class="card-title text-white">Parameter Filter Siswa</h3>
        <p class="text-xs text-blue-200">Saring data siswa berdasarkan nama, NIS, kelas, status akun, atau mode GPS.</p>
    </div>
    <form method="GET" action="{{ route('admin.students.index') }}">
        <div class="card-body grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[18px]">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cari Nama / NIS</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: Dimas atau NIS..." class="w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kelas</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mode GPS (PKL)</label>
                <select name="is_pkl" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                    <option value="">Semua Mode GPS</option>
                    <option value="0" {{ request('is_pkl') === '0' ? 'selected' : '' }}>Reguler (Wajib GPS)</option>
                    <option value="1" {{ request('is_pkl') === '1' ? 'selected' : '' }}>PKL (Bebas GPS)</option>
                </select>
            </div>
        </div>
        <div class="card-footer bg-slate-50/80 border-slate-200/80 rounded-b-[12px] flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-slate-600">
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ $students->count() }}</span> dari <span class="font-bold text-slate-900 tabular-nums">{{ $students->total() }}</span> data siswa.
            </p>
            <div class="flex items-center gap-2">
                @if(request()->hasAny(['search', 'class_id', 'status', 'is_pkl']))
                    <a href="{{ route('admin.students.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-rose-600 bg-white border border-slate-200 rounded-[8px] transition-colors">
                        Reset
                    </a>
                @endif
                <button type="submit" class="inline-flex items-center gap-[6px] px-4 py-2 bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-xs sm:text-sm font-bold transition shadow-2xs cursor-pointer">
                    <svg class="w-[15px] h-[15px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filter Data</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Bulk Delete Floating Bar -->
<div id="bulkBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white rounded-2xl shadow-2xl px-5 py-3 flex items-center gap-4 transition-all">
    <span class="text-sm font-semibold"><span id="bulkCount">0</span> dipilih</span>
    <button type="button" id="bulkDeleteBtn" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all active:scale-[0.98]">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Hapus Terpilih
    </button>
    <button type="button" id="bulkCancelBtn" class="text-xs text-slate-400 hover:text-white transition-colors">Batal</button>
</div>

<!-- Hidden bulk delete form -->
<form id="bulkDeleteForm" action="{{ route('admin.students.bulk-delete') }}" method="POST" class="hidden">
    @csrf
</form>

<div class="card overflow-hidden">
    <div class="card-header bg-[#0D2149] border-[#142C5C] text-white flex-wrap">
        <h3 class="card-title text-white">Daftar Akun Siswa ({{ $students->total() }})</h3>
        <button type="button" onclick="importModal.showModal()" class="inline-flex items-center gap-[6px] px-3 py-1.5 bg-white/10 hover:bg-white/20 border border-white/15 text-white rounded-[8px] text-xs font-semibold transition cursor-pointer whitespace-nowrap">
            <svg class="w-[14px] h-[14px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
    </div>
    <div class="table-wrap card-body-flush">
        <table class="text-slate-700">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="w-10">
                        <input type="checkbox" id="selectAllStudents" class="rounded text-[#2457E8] focus:ring-[#2457E8]/20 border-slate-300 w-4 h-4 cursor-pointer">
                    </th>
                    <th>Nama</th>
                    <th>Username / NIS</th>
                    <th>Kelas</th>
                    <th>Status Akun</th>
                    <th>Status PKL (GPS)</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $s)
                <tr class="hover:bg-[#F5F8FF] transition-colors duration-150 group">
                    <td class="align-middle">
                        <input type="checkbox" class="bulk-checkbox rounded text-[#2457E8] focus:ring-[#2457E8]/20 border-slate-300 w-4 h-4 cursor-pointer" value="{{ $s->id }}">
                    </td>
                    <td class="text-slate-900 font-bold">{{ $s->name }}</td>
                    <td class="tabular-nums text-slate-700 font-medium">{{ $s->username }}</td>
                    <td class="text-slate-700 font-medium">
                        {{ $s->class ? $s->class->name : '-' }}
                    </td>
                    <td>
                        @if($s->status === 'active')
                            <span class="inline-flex items-center gap-[6px] text-xs font-semibold text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Aktif</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-[6px] text-xs font-medium text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                                <span>Nonaktif</span>
                            </span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.students.toggle-pkl', $s->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-[6px] text-xs font-semibold transition-colors cursor-pointer {{ $s->is_pkl ? 'text-amber-700 hover:text-amber-800 hover:underline' : 'text-[#2457E8] hover:text-blue-800 hover:underline' }}" title="Klik untuk mengubah status PKL">
                                @if($s->is_pkl)
                                    <svg class="w-[14px] h-[14px] text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>PKL (GPS Off)</span>
                                @else
                                    <svg class="w-[14px] h-[14px] text-[#2457E8] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Reguler (GPS On)</span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-2">
                            <a href="{{ route('admin.students.edit', $s->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-2xs transition-all duration-150">Edit</a>
                            <form action="{{ route('admin.students.destroy', $s->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus siswa ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-2xs transition-all duration-150 cursor-pointer">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        Belum ada data siswa yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $students->links() }}
    </div>
    @endif
</div>

<script>
    function handleStudentsFile(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        document.getElementById('preview-students-name').textContent = file.name;

        let sizeStr = '';
        if (file.size < 1024) sizeStr = file.size + ' B';
        else if (file.size < 1024 * 1024) sizeStr = (file.size / 1024).toFixed(1) + ' KB';
        else sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        document.getElementById('preview-students-size').textContent = sizeStr;

        const ext = file.name.split('.').pop().toUpperCase();
        document.getElementById('preview-students-type').textContent = ext + ' SPREADSHEET';

        document.getElementById('dropzone-students-empty').classList.add('hidden');
        document.getElementById('dropzone-students-preview').classList.remove('hidden');
    }

    function removeStudentsFile() {
        const input = document.getElementById('file-upload-students');
        if (input) input.value = '';
        document.getElementById('dropzone-students-empty').classList.remove('hidden');
        document.getElementById('dropzone-students-preview').classList.add('hidden');
    }

    // === Bulk Select & Delete ===
    document.addEventListener('DOMContentLoaded', function() {
        const bulkBar = document.getElementById('bulkBar');
        const bulkCount = document.getElementById('bulkCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkCancelBtn = document.getElementById('bulkCancelBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const selectAllStudents = document.getElementById('selectAllStudents');
        const allCheckboxes = document.querySelectorAll('.bulk-checkbox');

        function updateBulkBar() {
            const checked = document.querySelectorAll('.bulk-checkbox:checked');
            const count = checked.length;
            if (bulkCount) bulkCount.textContent = count;
            if (bulkBar) {
                if (count > 0) {
                    bulkBar.classList.remove('hidden');
                    bulkBar.classList.add('flex');
                } else {
                    bulkBar.classList.add('hidden');
                    bulkBar.classList.remove('flex');
                }
            }
            if (selectAllStudents) {
                selectAllStudents.checked = allCheckboxes.length > 0 && checked.length === allCheckboxes.length;
            }
        }

        allCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

        if (selectAllStudents) {
            selectAllStudents.addEventListener('change', function() {
                allCheckboxes.forEach(cb => {
                    cb.checked = selectAllStudents.checked;
                });
                updateBulkBar();
            });
        }

        if (bulkCancelBtn) {
            bulkCancelBtn.addEventListener('click', function() {
                allCheckboxes.forEach(cb => cb.checked = false);
                if (selectAllStudents) selectAllStudents.checked = false;
                updateBulkBar();
            });
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const checked = document.querySelectorAll('.bulk-checkbox:checked');
                if (checked.length === 0) return;

                if (!confirm(`Yakin ingin menghapus ${checked.length} data siswa yang dipilih?`)) return;

                // Clear old hidden inputs
                bulkDeleteForm.querySelectorAll('input[name="student_ids[]"]').forEach(el => el.remove());

                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'student_ids[]';
                    input.value = cb.value;
                    bulkDeleteForm.appendChild(input);
                });

                bulkDeleteForm.submit();
            });
        }
    });
</script>
@endsection
