@extends('layouts.admin')
@section('title', 'Master Guru')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Guru</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola akun guru untuk pembuatan ujian dan pengawasan sesi.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="importModal.showModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-indigo-600 shadow-xs transition-all duration-150 active:scale-[0.98]">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import Excel
        </button>
        <a href="{{ route('admin.teachers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Guru
        </a>
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
                                <span id="preview-teachers-size" class="font-medium font-mono text-slate-600">0 KB</span>
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
            <button type="button" onclick="importModal.close()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-xs">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-colors">Upload Data</button>
        </div>
    </form>
</dialog>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6 p-4">
    <form method="GET" action="{{ route('admin.teachers.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[240px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, NIP, atau email..." class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-4 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15">
        </div>
        <div class="w-44">
            <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-indigo-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-2xs">
            Filter
        </button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.teachers.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-rose-600 transition-colors">
                Reset
            </a>
        @endif
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/80 border-b border-slate-200/80 text-xs uppercase tracking-wider text-slate-500 font-semibold">
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
                    <td class="px-6 py-4 font-mono text-slate-600 text-xs font-medium">{{ $teacher->username }}</td>
                    <td class="px-6 py-4 text-slate-500 text-xs">
                        {{ $teacher->email ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/60" title="Ujian yang dibuat secara mandiri oleh guru ini (di luar Ujian Umum/UAS Sekolah buatan Admin)">
                            {{ $teacher->created_exams_count }} ujian
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($teacher->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
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
