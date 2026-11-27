@extends('layouts.admin')
@section('title', 'Master Kelas')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-[22px]">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Daftar Kelas</h2>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">Kelola rombel dan tingkatan kelas siswa.</p>
    </div>
    <div class="flex items-center gap-2.5 shrink-0">
        <button onclick="importModal.showModal()" class="inline-flex items-center gap-[6px] px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs sm:text-sm font-semibold hover:bg-slate-50 hover:border-[#142C5C] hover:text-[#142C5C] shadow-2xs transition active:scale-[0.98] cursor-pointer whitespace-nowrap">
            <svg class="w-[15px] h-[15px] text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
        <button type="button" onclick="createModal.showModal()" class="inline-flex items-center gap-[6px] px-4 py-2.5 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer whitespace-nowrap">
            <svg class="w-[15px] h-[15px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Kelas</span>
        </button>
    </div>
</div>

<dialog id="importModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-[16px] border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-white">
        <h3 class="text-lg font-bold text-slate-900">Import Data Kelas</h3>
        <p class="text-sm text-slate-500 mt-0.5">Gunakan format CSV atau Excel yang valid.</p>
    </div>
    
    <form action="{{ route('admin.classes.import') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-white space-y-4">
        @csrf
        
        <div class="flex items-start gap-3 p-4 bg-slate-50/80 border border-slate-200 rounded-[8px]">
            <div class="p-2 bg-white shadow-xs border border-slate-200 text-[#142C5C] rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Belum punya template?</h4>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Gunakan format yang sudah disediakan agar tidak gagal.</p>
                <a href="{{ route('admin.classes.template') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#142C5C] hover:text-[#142C5C] hover:underline">
                    Download Template CSV &rarr;
                </a>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File Excel/CSV</label>
            <div class="mt-1 flex justify-center rounded-[8px] border-2 border-dashed border-slate-200 px-6 py-7 hover:border-[#142C5C]/50 hover:bg-slate-50/70 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-classes').click()">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-400 group-hover:text-[#142C5C] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <div class="mt-3 flex text-sm leading-6 text-slate-600 justify-center">
                        <span class="relative font-semibold text-[#142C5C] hover:text-[#142C5C]">
                            Click to upload
                            <input id="file-upload-classes" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="document.getElementById('file-name-classes').textContent = this.files[0].name">
                        </span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs leading-5 text-slate-400 mt-0.5" id="file-name-classes">CSV, XLS, XLSX up to 10MB</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" onclick="importModal.close()" class="px-4 py-2.5 text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-[8px] transition-colors cursor-pointer">Batal</button>
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Periksa &amp; Tampilkan Pratinjau</span>
            </button>
        </div>
    </form>
</dialog>

<!-- Modal Tambah Kelas -->
<dialog id="createModal" class="backdrop:bg-slate-900/50 backdrop:backdrop-blur-xs p-0 rounded-[16px] border border-slate-200 shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto m-auto">
    <div class="bg-[#142C5C] px-6 py-4 text-white flex items-center justify-between gap-3 sticky top-0 z-10">
        <div>
            <h3 class="text-base font-bold tracking-tight">Tambah Kelas Baru</h3>
            <p class="text-xs text-blue-200 mt-0.5">Buat kelas baru untuk pengelompokan siswa.</p>
        </div>
        <button type="button" onclick="createModal.close()" class="p-1.5 rounded-lg text-blue-200 hover:text-white hover:bg-white/10 transition" aria-label="Tutup">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <form action="{{ route('admin.classes.store') }}" method="POST" autocomplete="off" class="bg-white">
        @csrf
        <input type="hidden" name="_form" value="create">

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Kelas</label>
                <input type="text" name="name" value="{{ old('_form') === 'create' ? old('name') : '' }}" required class="w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10" placeholder="Contoh: XII TKJ 1">
                @if(old('_form') === 'create') @error('name') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror @endif
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" class="w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10" placeholder="Contoh: Kelas jurusan TKJ angkatan 2024">{{ old('_form') === 'create' ? old('description') : '' }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-end gap-2.5">
            <button type="button" onclick="createModal.close()" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-[8px] text-xs sm:text-sm font-semibold transition">Batal</button>
            <button type="submit" class="px-5 py-2.5 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">Simpan Kelas</button>
        </div>
    </form>
</dialog>
@if(old('_form') === 'create' && $errors->any())
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('createModal')?.showModal());</script>
@endif

<div class="card overflow-hidden">
    <div class="card-header bg-[#142C5C] border-[#0D2149] text-white">
        <h3 class="card-title text-white">Daftar Ruang Kelas ({{ $classes->count() }})</h3>
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
                    <th>Nama Kelas</th>
                    <th>Deskripsi</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($classes as $c)
                <tr class="hover:bg-slate-50/80 transition-colors duration-150 group">
                    <td class="text-slate-900 font-bold">{{ $c->name }}</td>
                    <td class="text-slate-600">{{ $c->description ?? '-' }}</td>
                    <td class="text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-2">
                            <a href="{{ route('admin.classes.edit', $c->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-2xs transition-all duration-150">Edit</a>
                            <form action="{{ route('admin.classes.destroy', $c->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus kelas ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-2xs transition-all duration-150 cursor-pointer">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-12 text-center text-slate-400">
                        Belum ada data kelas yang terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
