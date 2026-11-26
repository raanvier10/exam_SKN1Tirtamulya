@extends('layouts.admin')
@section('title', 'Master Guru')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Daftar Guru</h2>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">Kelola akun guru untuk pembuatan ujian dan pengawasan sesi.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <button onclick="importModal.showModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold hover:bg-slate-50 hover:border-[#2457E8] hover:text-[#2457E8] shadow-2xs transition active:scale-[0.98]">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import Excel
        </button>
        <button type="button" onclick="createModal.showModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Guru
        </button>
    </div>
</div>

<dialog id="importModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-white">
        <h3 class="text-lg font-bold text-slate-900">Import Data Guru</h3>
        <p class="text-sm text-slate-500 mt-0.5">Gunakan format CSV atau Excel yang valid.</p>
    </div>
    
    <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-white space-y-4">
        @csrf
        
        <div class="flex items-start gap-3 p-4 bg-indigo-50/40 border border-indigo-100 rounded-xl">
            <div class="p-2 bg-white shadow-xs border border-indigo-100 text-indigo-600 rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Belum punya template?</h4>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Gunakan format yang sudah disediakan agar tidak gagal.</p>
                <a href="{{ route('admin.teachers.template') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    Download Template CSV &rarr;
                </a>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File Excel/CSV</label>
            
            <div id="dropzone-teachers-empty" class="mt-1 flex justify-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-7 hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-teachers').click()">
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
            <div id="dropzone-teachers-preview" class="hidden mt-1 p-4 rounded-2xl border-2 border-emerald-500/40 bg-emerald-50/40 transition-all">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1.5 14h-7v-2h7v2zm0-4h-7v-2h7v2zm-2-5V3.5L18.5 7H13.5z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h5 id="preview-teachers-name" class="text-sm font-bold text-slate-900 truncate">file.csv</h5>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">✓ Siap Diupload</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                <span id="preview-teachers-size" class="font-medium tabular-nums text-slate-600">0 KB</span>
                                <span>•</span>
                                <span id="preview-teachers-type" class="uppercase font-semibold text-emerald-700">CSV</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="removeTeachersFile()" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors shrink-0" title="Ganti / Batalkan file">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <input id="file-upload-teachers" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="handleTeachersFile(this)">
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

<!-- Modal Tambah Guru -->
<dialog id="createModal" class="backdrop:bg-slate-900/50 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-auto">
    <div class="bg-[#0D2149] px-6 py-4 text-white flex items-center justify-between gap-3 sticky top-0 z-10">
        <div>
            <h3 class="text-base font-bold tracking-tight">Tambah Guru Baru</h3>
            <p class="text-xs text-blue-200 mt-0.5">Buat akun guru baru untuk pengawasan dan pembuatan ujian.</p>
        </div>
        <button type="button" onclick="createModal.close()" class="p-1.5 rounded-lg text-blue-200 hover:text-white hover:bg-white/10 transition" aria-label="Tutup">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <form action="{{ route('admin.teachers.store') }}" method="POST" autocomplete="off" class="bg-white">
        @csrf
        <input type="hidden" name="_form" value="create">

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('_form') === 'create' ? old('name') : '' }}" required autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15" placeholder="Contoh: Budi Santoso, S.Pd.">
                    @if(old('_form') === 'create') @error('name') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Username / NIP</label>
                    <input type="text" name="username" value="{{ old('_form') === 'create' ? old('username') : '' }}" required autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 tabular-nums" placeholder="Contoh: 19850720... atau guru_ipa">
                    @if(old('_form') === 'create') @error('username') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input type="email" name="email" value="{{ old('_form') === 'create' ? old('email') : '' }}" autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15" placeholder="guru@smkn1tirtamulya.online">
                    @if(old('_form') === 'create') @error('email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                    <select name="status" required class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                        <option value="active" {{ old('_form') === 'create' ? (old('status', 'active') === 'active' ? 'selected' : '') : 'selected' }}>Aktif</option>
                        <option value="inactive" {{ old('_form') === 'create' && old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
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
        </div>

        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-end gap-2.5">
            <button type="button" onclick="createModal.close()" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs sm:text-sm font-semibold transition">Batal</button>
            <button type="submit" class="px-5 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">Simpan Akun Guru</button>
        </div>
    </form>
</dialog>
@if(old('_form') === 'create' && $errors->any())
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('createModal')?.showModal());</script>
@endif

<div class="bg-white rounded-2xl border border-slate-200 shadow-2xs mb-6">
    <div class="bg-[#0D2149] px-5 py-3.5 text-white rounded-t-2xl">
        <h3 class="text-sm font-bold tracking-tight">Parameter Filter Guru</h3>
        <p class="text-xs text-blue-200 mt-0.5">Saring data guru berdasarkan nama, NIP, email, atau status akun.</p>
    </div>
    <form method="GET" action="{{ route('admin.teachers.index') }}">
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cari Nama / NIP / Email</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: Budi atau NIP..." class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8]">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="px-5 py-3 bg-slate-50/80 border-t border-slate-200/80 rounded-b-2xl flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-slate-600">
                Menampilkan <span class="font-bold text-slate-900">{{ $teachers->count() }}</span> dari <span class="font-bold text-slate-900">{{ $teachers->total() }}</span> data guru.
            </p>
            <div class="flex items-center gap-2">
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.teachers.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-rose-600 bg-white border border-slate-200 rounded-xl transition-colors">
                        Reset
                    </a>
                @endif
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Data
                </button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
    <div class="bg-[#0D2149] px-5 py-3.5 text-white flex items-center justify-between gap-3">
        <h3 class="text-sm font-bold tracking-tight">Daftar Akun Guru ({{ $teachers->total() }})</h3>
        <button type="button" onclick="importModal.showModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 hover:bg-white/20 border border-white/15 text-white rounded-lg text-xs font-semibold transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import Excel
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-700">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                <tr>
                    <th class="px-6 py-4">Nama Guru</th>
                    <th class="px-6 py-4">Username / NIP</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Ujian Dibuat (Mandiri)</th>
                    <th class="px-6 py-4">Status Akun</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($teachers as $teacher)
                <tr class="hover:bg-indigo-50/20 transition-colors duration-150 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0 border border-indigo-100">
                                {{ strtoupper(substr($teacher->name, 0, 2)) }}
                            </div>
                            <span class="text-slate-900 font-semibold">{{ $teacher->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 tabular-nums text-slate-600 text-xs font-medium">{{ $teacher->username }}</td>
                    <td class="px-6 py-4 text-slate-500 text-xs">
                        {{ $teacher->email ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-slate-700 font-medium" title="Ujian yang dibuat secara mandiri oleh guru ini (di luar Ujian Umum/UAS Sekolah buatan Admin)">
                        {{ $teacher->created_exams_count }} ujian
                    </td>
                    <td class="px-6 py-4">
                        @if($teacher->status === 'active')
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-1.5">
                        <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-2xs transition-all duration-150">Edit</a>
                        <form action="{{ route('admin.teachers.destroy', $teacher->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun guru {{ $teacher->name }}?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-2xs transition-all duration-150">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        Belum ada data guru yang terdaftar atau cocok dengan pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($teachers->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $teachers->links() }}
    </div>
    @endif
</div>

<script>
    function handleTeachersFile(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        document.getElementById('preview-teachers-name').textContent = file.name;

        let sizeStr = '';
        if (file.size < 1024) sizeStr = file.size + ' B';
        else if (file.size < 1024 * 1024) sizeStr = (file.size / 1024).toFixed(1) + ' KB';
        else sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        document.getElementById('preview-teachers-size').textContent = sizeStr;

        const ext = file.name.split('.').pop().toUpperCase();
        document.getElementById('preview-teachers-type').textContent = ext + ' SPREADSHEET';

        document.getElementById('dropzone-teachers-empty').classList.add('hidden');
        document.getElementById('dropzone-teachers-preview').classList.remove('hidden');
    }

    function removeTeachersFile() {
        const input = document.getElementById('file-upload-teachers');
        if (input) input.value = '';
        document.getElementById('dropzone-teachers-empty').classList.remove('hidden');
        document.getElementById('dropzone-teachers-preview').classList.add('hidden');
    }
</script>
@endsection
