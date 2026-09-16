@extends('layouts.admin')
@section('title', 'Manajemen Ujian')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Jadwal Ujian</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola sesi ujian online dan tautan Google Form.</p>
    </div>
    <div class="grid grid-cols-2 sm:flex items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
        <button onclick="importModal.showModal()" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-indigo-600 shadow-xs transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
        <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Ujian</span>
        </a>
    </div>
</div>

<dialog id="importModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto m-auto">
    <div class="px-5 py-4 border-b border-slate-100 bg-white">
        <h3 class="text-lg font-bold text-slate-900">Import Jadwal Ujian</h3>
        <p class="text-sm text-slate-500 mt-0.5">Pastikan format kolom sesuai dengan template.</p>
    </div>
    
    <form action="{{ route('admin.exams.import') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-white space-y-4">
        @csrf
        
        <div class="flex items-start gap-3 p-4 bg-indigo-50/40 border border-indigo-100 rounded-xl">
            <div class="p-2 bg-white shadow-xs border border-indigo-100 text-indigo-600 rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Format Wajib Sesuai!</h4>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Kolom <code>target_kelas</code> bisa diisi nama kelas (misal: <em>XI TJKT 1, XI TJKT 2</em>) atau kosongkan untuk Semua Kelas. Data yang sudah ada akan otomatis dilewati.</p>
                <a href="{{ route('admin.exams.template') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    Download Template CSV &rarr;
                </a>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File Excel/CSV</label>
            
            <!-- State 1: Dropzone kosong (belum ada file) -->
            <div id="dropzone-empty" class="mt-1 flex justify-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-7 hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-exams').click()">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <div class="flex text-sm leading-6 text-slate-600 justify-center">
                        <span class="font-bold text-indigo-600 hover:text-indigo-500">
                            Pilih file
                        </span>
                        <p class="pl-1">atau tarik file ke sini</p>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">CSV, XLS, XLSX up to 10MB</p>
                </div>
            </div>

            <!-- State 2: Preview Bentuk File Card (saat file sudah dipilih) -->
            <div id="dropzone-file-preview" class="hidden mt-1 p-4 rounded-2xl border-2 border-emerald-500/40 bg-emerald-50/40 transition-all">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Icon Dokumen Excel / Spreadsheet Hijau -->
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1.5 14h-7v-2h7v2zm0-4h-7v-2h7v2zm-2-5V3.5L18.5 7H13.5z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h5 id="preview-file-name" class="text-sm font-bold text-slate-900 truncate">nama_file.csv</h5>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">
                                    ✓ Siap Diupload
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                <span id="preview-file-size" class="font-medium font-mono text-slate-600">0 KB</span>
                                <span>•</span>
                                <span id="preview-file-type" class="uppercase font-semibold text-emerald-700">EXCEL/CSV</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="removeSelectedFile()" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors shrink-0" title="Ganti / Batalkan file">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Hidden native file input -->
            <input id="file-upload-exams" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="handleFileSelected(this)">
        </div>

        <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" onclick="importModal.close()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-xs">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-colors">Upload Data</button>
        </div>
    </form>
</dialog>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs mb-6 p-3.5 sm:p-4">
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 sm:gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                id="searchExam" 
                placeholder="Cari judul ujian..." 
                value="{{ request('search') }}"
                class="w-full pl-10 pr-9 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
            <button id="clearSearch" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1 sm:w-44">
                <select id="filterClass" class="w-full appearance-none pl-3 pr-7 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ strtolower($c->name) }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div class="relative flex-1 sm:w-36">
                <select id="filterStatus" class="w-full appearance-none pl-3 pr-7 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <button id="resetFilters" class="h-9 w-9 shrink-0 flex items-center justify-center text-slate-500 hover:text-indigo-600 bg-slate-50 hover:bg-indigo-50 rounded-xl border border-slate-200/80 hover:border-indigo-200 transition-all active:scale-95" title="Reset Filter">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>
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
<form id="bulkDeleteForm" action="{{ route('admin.exams.bulk-delete') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- Dual Mode: Desktop Table + Mobile Standalone Cards -->

<!-- 1. Desktop Table View (>= md) -->
<div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-4 w-10">
                        <input type="checkbox" id="selectAllDesktop" class="rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer">
                    </th>
                    <th class="px-6 py-4 min-w-[200px]">Judul Ujian</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[170px]">Target Kelas</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[150px]">Waktu</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[90px]">Durasi</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[130px]">Toleransi</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[100px]">Status</th>
                    <th class="px-6 py-4 whitespace-nowrap text-right min-w-[180px]">Aksi</th>
                </tr>
            </thead>
            <tbody id="examTableBody" class="divide-y divide-slate-100">
                @php $displayList = $groupedExams ?? $exams; @endphp
                @forelse($displayList as $grp)
                @php
                    $isGrouped = isset($grp->session_count);
                    $sessionCount = $isGrouped ? $grp->session_count : 1;
                    $isSingle = $sessionCount === 1;
                    $primary = $isGrouped ? $grp->primary : $grp;
                    $classList = $isGrouped ? $grp->classes->pluck('name')->implode(', ') : $grp->classes->pluck('name')->implode(', ');
                    $classCount = $isGrouped ? $grp->classes->count() : $grp->classes->count();
                    $canManage = auth()->user()->isAdmin() || ($primary->created_by === auth()->id());
                    $isOngoing = $isGrouped ? $grp->is_ongoing : $primary->isOngoing();
                    $status = $isOngoing ? 'ongoing' : ($isGrouped ? ($grp->has_active ? 'active' : 'inactive') : $primary->status);
                    $groupId = $isGrouped ? $grp->group_id : ('exam_' . $primary->id);
                @endphp

                @if($isSingle)
                    <!-- Single Exam Row -->
                    <tr class="exam-row hover:bg-indigo-50/20 transition-colors duration-150 group" 
                        data-title="{{ strtolower($primary->title) }}" 
                        data-classes="{{ strtolower($classList ?: 'semua kelas') }}" 
                        data-status="{{ $primary->status }}"
                        data-exam-id="{{ $primary->id }}"
                        data-can-manage="{{ $canManage ? '1' : '0' }}"
                        data-ongoing="{{ $isOngoing ? '1' : '0' }}">
                        <td class="px-4 py-4 align-middle">
                            @if($canManage)
                                @if($isOngoing)
                                    <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed opacity-40" title="Ujian sedang berlangsung">
                                @else
                                    <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer" value="{{ $primary->id }}">
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900 align-middle">
                            <div class="line-clamp-2 max-w-sm font-bold text-slate-900">{{ $primary->title }}</div>
                            <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                                @if($primary->created_by === auth()->id())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Ujian Anda
                                    </span>
                                @elseif($primary->creator && $primary->creator->isAdmin())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                                        Ujian Sekolah (Admin)
                                    </span>
                                @elseif($primary->creator)
                                    <span class="text-slate-400">
                                        Oleh: <span class="text-slate-600 font-medium">{{ $primary->creator->name }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                                        Ujian Sekolah (Admin)
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            @if($classCount === 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    Semua Kelas
                                </span>
                            @elseif($classCount <= 2)
                                <div class="inline-flex items-center gap-1.5 flex-wrap">
                                    @foreach(($isGrouped ? $grp->classes : $primary->classes) as $cls)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $cls->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ ($isGrouped ? $grp->classes : $primary->classes)->first()->name }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-indigo-100 text-slate-700 hover:text-indigo-800 border border-slate-200 transition-colors cursor-help" title="{{ $classList }}">
                                        +{{ $classCount - 1 }} Kelas
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            <div class="text-slate-900 font-semibold text-xs">{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }}</div>
                            <div class="text-slate-400 text-xs mt-0.5 font-mono">
                                {{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB
                            </div>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            <div class="font-bold text-slate-800 text-xs">{{ $primary->duration }} mnt</div>
                            <div class="text-[10px] font-semibold text-indigo-600 mt-0.5">{{ round($primary->duration / 45, 1) }} JP</div>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Max {{ $primary->max_violation }}x Keluar
                            </span>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            @if($isOngoing)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/30 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping mr-0.5"></span> Berlangsung
                                </span>
                            @elseif($primary->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.exams.show', $primary->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white shadow-2xs transition-all duration-150" title="Monitor & Buka Kunci Siswa">
                                    Monitor
                                </a>
                                @if($canManage)
                                    <a href="{{ route('admin.exams.edit', $primary->id) }}" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-2xs transition-all duration-150">Edit</a>
                                    @if($isOngoing)
                                        <button type="button" disabled class="inline-flex items-center justify-center px-2.5 py-1.5 bg-slate-100 border border-slate-200 text-slate-400 rounded-lg text-xs font-medium cursor-not-allowed opacity-50" title="Ujian sedang berlangsung dan tidak dapat dihapus">
                                            Hapus
                                        </button>
                                    @else
                                        <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-2xs transition-all duration-150">Hapus</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @else
                    <!-- Consolidated Multi-Session Master Row -->
                    <tr class="exam-row hover:bg-indigo-50/20 transition-colors duration-150 group border-t-2 border-slate-100" 
                        data-title="{{ strtolower($grp->title) }}" 
                        data-classes="{{ strtolower($classList ?: 'semua kelas') }}" 
                        data-status="{{ $isOngoing ? 'active' : ($grp->has_active ? 'active' : 'inactive') }}"
                        data-group-id="{{ $groupId }}"
                        data-can-manage="{{ $canManage ? '1' : '0' }}"
                        data-ongoing="{{ $isOngoing ? '1' : '0' }}">
                        <td class="px-4 py-4 align-middle">
                            @if($canManage)
                                @if($isOngoing)
                                    <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed opacity-40" title="Sebagian sesi ujian sedang berlangsung">
                                @else
                                    <input type="checkbox" class="group-master-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer" data-target-group="{{ $groupId }}" title="Pilih semua sesi">
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900 align-middle">
                            <div class="line-clamp-2 max-w-sm text-base font-bold text-slate-900">{{ $grp->title }}</div>
                            <div class="mt-1 flex items-center gap-1.5 flex-wrap text-[11px]">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                                    <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $sessionCount }} Sesi Terjadwal
                                </span>
                                @if($grp->created_by === auth()->id())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Ujian Anda
                                    </span>
                                @elseif($grp->creator && $grp->creator->isAdmin())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                                        Ujian Sekolah (Admin)
                                    </span>
                                @elseif($grp->creator)
                                    <span class="text-slate-400">
                                        Oleh: <span class="text-slate-600 font-medium">{{ $grp->creator->name }}</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            @if($classCount === 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    Semua Kelas
                                </span>
                            @elseif($classCount <= 2)
                                <div class="inline-flex items-center gap-1.5 flex-wrap">
                                    @foreach($grp->classes as $cls)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $cls->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $grp->classes->first()->name }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-indigo-100 text-slate-700 hover:text-indigo-800 border border-slate-200 transition-colors cursor-help" title="{{ $classList }}">
                                        +{{ $classCount - 1 }} Kelas
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            <div class="text-slate-900 font-bold text-xs">{{ $sessionCount }} Sesi Kelas</div>
                            <div class="text-slate-400 text-xs mt-0.5 font-mono">
                                {{ \Carbon\Carbon::parse($grp->start_at)->format('d M Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            <div class="font-bold text-slate-800 text-xs">{{ $grp->duration }} mnt</div>
                            <div class="text-[10px] font-semibold text-indigo-600 mt-0.5">{{ round($grp->duration / 45, 1) }} JP</div>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                                Max {{ $grp->max_violation }}x Keluar
                            </span>
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                            @if($isOngoing)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/30 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping mr-0.5"></span> Berlangsung
                                </span>
                            @elseif($grp->has_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 align-middle whitespace-nowrap text-right">
                            <button type="button" onclick="toggleSessionGroup('{{ $groupId }}')" class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-all duration-150 active:scale-95" title="Buka detail seluruh sesi">
                                <span id="btn-text-{{ $groupId }}" data-count="{{ $sessionCount }}">Buka {{ $sessionCount }} Sesi</span>
                                <svg id="btn-icon-{{ $groupId }}" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </td>
                    </tr>

                    <!-- Expandable Drawer for Multi-Session -->
                    <tr id="session-drawer-{{ $groupId }}" class="hidden bg-slate-50/70 border-b-2 border-indigo-100/80 transition-all">
                        <td colspan="8" class="p-3.5 sm:p-5">
                            <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-4 space-y-3">
                                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                        <h5 class="text-xs sm:text-sm font-bold text-slate-900">
                                            Daftar {{ $sessionCount }} Sesi: <span class="text-indigo-600">{{ $grp->title }}</span>
                                        </h5>
                                    </div>
                                    <span class="text-[11px] sm:text-xs text-slate-500 font-medium">Klik <strong class="text-indigo-600 font-bold">"Pantau Siswa"</strong> pada sesi kelas yang sedang berlangsung</span>
                                </div>

                                <div class="divide-y divide-slate-100">
                                    @foreach($grp->sessions as $idx => $s)
                                    @php
                                        $sOngoing = $s->isOngoing();
                                        $sClasses = $s->classes->pluck('name')->implode(', ') ?: 'Semua Kelas';
                                    @endphp
                                    <div class="py-3 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-indigo-50/20 px-2 rounded-xl transition-colors">
                                        <div class="flex items-center gap-3 min-w-0">
                                            @if($canManage)
                                                @if($sOngoing)
                                                    <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed opacity-40" title="Ujian sedang berlangsung">
                                                @else
                                                    <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer session-cb-{{ $groupId }}" value="{{ $s->id }}">
                                                @endif
                                            @endif
                                            <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-bold text-slate-900 text-sm">
                                                        {{ $sClasses }}
                                                    </span>
                                                    @if($sOngoing)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/30 border border-amber-200">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span> Berlangsung
                                                        </span>
                                                    @elseif($s->status === 'active')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                                            Nonaktif
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                                                    <span>📅 {{ \Carbon\Carbon::parse($s->start_at)->format('d M Y') }}</span>
                                                    <span class="font-mono text-slate-700 font-semibold">⏰ {{ \Carbon\Carbon::parse($s->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_at)->format('H:i') }} WIB</span>
                                                    <span class="text-indigo-600 font-semibold font-mono">({{ $s->duration }} Menit / {{ round($s->duration / 45, 1) }} JP)</span>
                                                    <span>Batas: Max {{ $s->max_violation }}x</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                                            <a href="{{ route('admin.exams.show', $s->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-all active:scale-[0.98]">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Pantau Siswa</span>
                                            </a>
                                            @if($canManage)
                                                <a href="{{ route('admin.exams.edit', $s->id) }}" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition-all">Edit</a>
                                                @if($sOngoing)
                                                    <button type="button" disabled class="px-2.5 py-1.5 bg-slate-100 border border-slate-200 text-slate-400 rounded-lg text-xs font-medium cursor-not-allowed opacity-50" title="Ujian sedang berlangsung dan tidak dapat dihapus">Hapus</button>
                                                @else
                                                    <form action="{{ route('admin.exams.destroy', $s->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus sesi ini?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-600 text-slate-700 rounded-lg text-xs font-medium transition-all">Hapus</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
                @empty
                <tr id="emptyStaticRow">
                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                        Belum ada jadwal ujian terdaftar.
                    </td>
                </tr>
                @endforelse
                <tr id="noMatchRow" class="hidden">
                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                        Tidak ada jadwal ujian yang cocok dengan pencarian / filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Mobile Standalone Cards View (< md) -->
<div class="md:hidden space-y-3.5" id="examMobileList">
    @php $displayList = $groupedExams ?? $exams; @endphp
    @forelse($displayList as $grp)
    @php
        $isGrouped = isset($grp->session_count);
        $sessionCount = $isGrouped ? $grp->session_count : 1;
        $isSingle = $sessionCount === 1;
        $primary = $isGrouped ? $grp->primary : $grp;
        $classList = $isGrouped ? $grp->classes->pluck('name')->implode(', ') : $grp->classes->pluck('name')->implode(', ');
        $classCount = $isGrouped ? $grp->classes->count() : $grp->classes->count();
        $canManage = auth()->user()->isAdmin() || ($primary->created_by === auth()->id());
        $isOngoing = $isGrouped ? $grp->is_ongoing : $primary->isOngoing();
        $status = $isOngoing ? 'ongoing' : ($isGrouped ? ($grp->has_active ? 'active' : 'inactive') : $primary->status);
        $groupId = $isGrouped ? $grp->group_id : ('exam_' . $primary->id);
    @endphp

    @if($isSingle)
        <!-- Mobile Single Exam Card -->
        <div class="exam-card bg-white rounded-2xl border-2 border-slate-200 hover:border-slate-300 shadow-xs p-4 sm:p-5 space-y-3 transition-all"
             data-title="{{ strtolower($primary->title) }}" 
             data-classes="{{ strtolower($classList ?: 'semua kelas') }}" 
             data-status="{{ $primary->status }}"
             data-exam-id="{{ $primary->id }}"
             data-can-manage="{{ $canManage ? '1' : '0' }}"
             data-ongoing="{{ $isOngoing ? '1' : '0' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-2.5 min-w-0 flex-1">
                    @if($canManage)
                        @if($isOngoing)
                            <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed mt-1 shrink-0 opacity-40" title="Ujian sedang berlangsung">
                        @else
                            <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-1 shrink-0" value="{{ $primary->id }}">
                        @endif
                    @endif
                    <div class="space-y-1.5 min-w-0">
                        <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $primary->title }}</h4>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $primary->duration }} Menit ({{ round($primary->duration / 45, 1) }} JP)
                            </span>
                            @if($primary->created_by === auth()->id())
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Ujian Anda
                                </span>
                            @elseif($primary->creator && $primary->creator->isAdmin())
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-lg">
                                    Ujian Sekolah (Admin)
                                </span>
                            @elseif($primary->creator)
                                <span class="text-[11px] text-slate-500">
                                    Oleh: <strong class="text-slate-700 font-medium">{{ $primary->creator->name }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="shrink-0 pt-0.5">
                    @if($isOngoing)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/30 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Berlangsung
                        </span>
                    @elseif($primary->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/30 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/30 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs py-2 px-3 bg-slate-50 rounded-xl border border-slate-100">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Waktu Pelaksanaan</span>
                    <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }}</span>
                    <span class="text-slate-500 font-mono block text-[11px]">{{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Batas Toleransi</span>
                    <span class="font-semibold text-rose-600 flex items-center gap-1 mt-0.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Max {{ $primary->max_violation }}x Keluar
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] text-slate-400 font-medium">Target:</span>
                @if($classCount === 0)
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Semua Kelas</span>
                @elseif($classCount <= 3)
                    @foreach($primary->classes as $cls)
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $cls->name }}</span>
                    @endforeach
                @else
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $primary->classes->first()->name }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">+{{ $classCount - 1 }} Kelas lainnya</span>
                @endif
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                <a href="{{ route('admin.exams.show', $primary->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Pantau Live</span>
                </a>
                @if($canManage)
                    <a href="{{ route('admin.exams.edit', $primary->id) }}" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-all active:scale-[0.98]">
                        Edit
                    </a>
                    @if($isOngoing)
                        <button type="button" disabled class="inline-flex items-center justify-center px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-400 rounded-xl text-xs font-semibold cursor-not-allowed opacity-50" title="Ujian sedang berlangsung dan tidak dapat dihapus">
                            Hapus
                        </button>
                    @else
                        <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-flex m-0" onsubmit="return confirm('Hapus ujian ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 text-slate-700 rounded-xl text-xs font-semibold transition-all active:scale-[0.98]">
                                Hapus
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    @else
        <!-- Mobile Multi-Session Consolidated Card -->
        <div class="exam-card bg-white rounded-2xl border-2 border-indigo-200 hover:border-indigo-300 shadow-xs p-4 sm:p-5 space-y-3 transition-all"
             data-title="{{ strtolower($grp->title) }}" 
             data-classes="{{ strtolower($classList ?: 'semua kelas') }}" 
             data-status="{{ $isOngoing ? 'active' : ($grp->has_active ? 'active' : 'inactive') }}"
             data-group-id="{{ $groupId }}"
             data-can-manage="{{ $canManage ? '1' : '0' }}"
             data-ongoing="{{ $isOngoing ? '1' : '0' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1.5 min-w-0 flex-1">
                    <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $grp->title }}</h4>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-lg">
                            <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $sessionCount }} Sesi Terjadwal
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">
                            {{ $grp->duration }} Menit ({{ round($grp->duration / 45, 1) }} JP)
                        </span>
                        @if($grp->created_by === auth()->id())
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Ujian Anda
                            </span>
                        @endif
                    </div>
                </div>
                <div class="shrink-0 pt-0.5">
                    @if($isOngoing)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/30 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Berlangsung
                        </span>
                    @elseif($grp->has_active)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/30 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/30 border border-slate-200">
                            Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap text-xs">
                <span class="text-slate-400 font-medium">Kelas:</span>
                @if($classCount === 0)
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Semua Kelas</span>
                @elseif($classCount <= 3)
                    @foreach($grp->classes as $cls)
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $cls->name }}</span>
                    @endforeach
                @else
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $grp->classes->first()->name }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">+{{ $classCount - 1 }} Kelas lainnya</span>
                @endif
            </div>

            <!-- Toggle Accordion Button -->
            <button type="button" onclick="toggleMobileSessionGroup('{{ $groupId }}')" class="w-full flex items-center justify-between px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 transition-all active:scale-[0.99]">
                <span>Buka {{ $sessionCount }} Sesi Kelas</span>
                <span id="mob-btn-icon-{{ $groupId }}" class="text-base font-bold">▾</span>
            </button>

            <!-- Collapsible Session Sub-Cards -->
            <div id="mob-drawer-{{ $groupId }}" class="hidden space-y-2.5 pt-2 border-t border-slate-100">
                @foreach($grp->sessions as $idx => $s)
                @php
                    $sOngoing = $s->isOngoing();
                    $sClasses = $s->classes->pluck('name')->implode(', ') ?: 'Semua Kelas';
                @endphp
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/90 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <span class="font-bold text-slate-900 text-xs">{{ $sClasses }}</span>
                        </div>
                        @if($sOngoing)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                Berlangsung
                            </span>
                        @elseif($s->status === 'active')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                Aktif
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-200 text-slate-700">
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-600 space-y-0.5 font-mono">
                        <div>📅 {{ \Carbon\Carbon::parse($s->start_at)->format('d M Y') }}</div>
                        <div>⏰ {{ \Carbon\Carbon::parse($s->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_at)->format('H:i') }} WIB ({{ $s->duration }}m)</div>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="{{ route('admin.exams.show', $s->id) }}" class="flex-1 inline-flex items-center justify-center gap-1 py-1.5 px-3 bg-indigo-600 text-white rounded-lg text-xs font-bold shadow-xs">
                            <span>Pantau Live &rarr;</span>
                        </a>
                        @if($canManage)
                            <a href="{{ route('admin.exams.edit', $s->id) }}" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold">
                                Edit
                            </a>
                            @if(!$sOngoing)
                                <form action="{{ route('admin.exams.destroy', $s->id) }}" method="POST" class="inline-flex m-0" onsubmit="return confirm('Hapus sesi ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-white border border-slate-300 text-rose-600 rounded-lg text-xs font-semibold">Hapus</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
    @empty
    <div class="p-8 text-center text-slate-400 text-xs bg-white rounded-2xl border-2 border-slate-200">
        Belum ada jadwal ujian terdaftar.
    </div>
    @endforelse
    <div id="noMatchMobile" class="hidden p-8 text-center text-slate-400 text-xs bg-white rounded-2xl border-2 border-slate-200">
        Tidak ada jadwal ujian yang cocok dengan pencarian / filter.
    </div>
</div>

<script>
    // Accordion Toggle Functions (Global scope for onclick attributes)
    function toggleSessionGroup(groupId) {
        const drawer = document.getElementById('session-drawer-' + groupId);
        const btnText = document.getElementById('btn-text-' + groupId);
        const btnIcon = document.getElementById('btn-icon-' + groupId);
        if (!drawer) return;

        const isHidden = drawer.classList.contains('hidden');
        if (isHidden) {
            drawer.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Tutup Sesi';
            if (btnIcon) btnIcon.classList.add('rotate-180');
        } else {
            drawer.classList.add('hidden');
            if (btnText) {
                const count = btnText.getAttribute('data-count') || '';
                btnText.textContent = 'Buka ' + count + ' Sesi';
            }
            if (btnIcon) btnIcon.classList.remove('rotate-180');
        }
    }

    function toggleMobileSessionGroup(groupId) {
        const drawer = document.getElementById('mob-drawer-' + groupId);
        const icon = document.getElementById('mob-btn-icon-' + groupId);
        if (!drawer) return;
        const isHidden = drawer.classList.contains('hidden');
        if (isHidden) {
            drawer.classList.remove('hidden');
            if (icon) icon.textContent = '▴';
        } else {
            drawer.classList.add('hidden');
            if (icon) icon.textContent = '▾';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchExam');
        const clearBtn = document.getElementById('clearSearch');
        const classFilter = document.getElementById('filterClass');
        const statusFilter = document.getElementById('filterStatus');
        const resetBtn = document.getElementById('resetFilters');
        const rows = document.querySelectorAll('.exam-row');
        const cards = document.querySelectorAll('.exam-card');
        const noMatchRow = document.getElementById('noMatchRow');
        const noMatchMobile = document.getElementById('noMatchMobile');

        function filterExams() {
            const query = searchInput.value.toLowerCase().trim();
            const selectedClass = classFilter.value.toLowerCase().trim();
            const selectedStatus = statusFilter.value.toLowerCase().trim();

            clearBtn.classList.toggle('hidden', query === '');

            let visibleCount = 0;

            function checkItem(el) {
                const title = el.getAttribute('data-title') || '';
                const classes = el.getAttribute('data-classes') || '';
                const status = el.getAttribute('data-status') || '';

                const matchesQuery = query === '' || title.includes(query) || classes.includes(query);
                const matchesClass = selectedClass === '' || classes.includes(selectedClass) || classes.includes('semua kelas');
                const matchesStatus = selectedStatus === '' || status === selectedStatus || (selectedStatus === 'active' && status === 'ongoing');

                if (matchesQuery && matchesClass && matchesStatus) {
                    el.style.display = '';
                    return true;
                } else {
                    el.style.display = 'none';
                    // Hide child drawer if present
                    const groupId = el.getAttribute('data-group-id');
                    if (groupId) {
                        const drawer = document.getElementById('session-drawer-' + groupId);
                        if (drawer) drawer.classList.add('hidden');
                        const mobDrawer = document.getElementById('mob-drawer-' + groupId);
                        if (mobDrawer) mobDrawer.classList.add('hidden');
                    }
                    return false;
                }
            }

            rows.forEach(row => {
                if (checkItem(row)) visibleCount++;
            });

            cards.forEach(card => {
                checkItem(card);
            });

            const hasItems = rows.length > 0 || cards.length > 0;
            if (noMatchRow) {
                noMatchRow.classList.toggle('hidden', !hasItems || visibleCount > 0);
            }
            if (noMatchMobile) {
                noMatchMobile.classList.toggle('hidden', !hasItems || visibleCount > 0);
            }
        }

        searchInput.addEventListener('input', filterExams);
        classFilter.addEventListener('change', filterExams);
        statusFilter.addEventListener('change', filterExams);

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterExams();
            searchInput.focus();
        });

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            classFilter.value = '';
            statusFilter.value = '';
            filterExams();
        });

        filterExams();

        // === Bulk Select & Delete ===
        const bulkBar = document.getElementById('bulkBar');
        const bulkCount = document.getElementById('bulkCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkCancelBtn = document.getElementById('bulkCancelBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const selectAllDesktop = document.getElementById('selectAllDesktop');

        function updateBulkBar() {
            const checked = document.querySelectorAll('.bulk-checkbox:checked');
            const count = checked.length;
            bulkCount.textContent = count;
            if (count > 0) {
                bulkBar.classList.remove('hidden');
                bulkBar.classList.add('flex');
            } else {
                bulkBar.classList.add('hidden');
                bulkBar.classList.remove('flex');
            }
        }

        document.querySelectorAll('.bulk-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });

        // Group master checkboxes
        document.querySelectorAll('.group-master-checkbox').forEach(gcb => {
            gcb.addEventListener('change', function() {
                const targetGroupId = this.getAttribute('data-target-group');
                const sessionCbs = document.querySelectorAll('.session-cb-' + targetGroupId);
                sessionCbs.forEach(scb => {
                    if (!scb.disabled) scb.checked = this.checked;
                });
                updateBulkBar();
            });
        });

        if (selectAllDesktop) {
            selectAllDesktop.addEventListener('change', function() {
                // Toggle all visible bulk checkboxes
                document.querySelectorAll('.bulk-checkbox').forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = selectAllDesktop.checked;
                    }
                });
                document.querySelectorAll('.group-master-checkbox').forEach(gcb => {
                    if (!gcb.disabled) {
                        gcb.checked = selectAllDesktop.checked;
                    }
                });
                updateBulkBar();
            });
        }

        bulkCancelBtn.addEventListener('click', function() {
            document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.group-master-checkbox').forEach(gcb => gcb.checked = false);
            if (selectAllDesktop) selectAllDesktop.checked = false;
            updateBulkBar();
        });

        bulkDeleteBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.bulk-checkbox:checked');
            if (checked.length === 0) return;

            if (!confirm(`Yakin ingin menghapus ${checked.length} jadwal sesi ujian yang dipilih?`)) return;

            bulkDeleteForm.querySelectorAll('input[name="exam_ids[]"]').forEach(el => el.remove());

            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'exam_ids[]';
                input.value = cb.value;
                bulkDeleteForm.appendChild(input);
            });

            bulkDeleteForm.submit();
        });
    });

    // File Upload Preview Card Handlers
    function handleFileSelected(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        const nameEl = document.getElementById('preview-file-name');
        const sizeEl = document.getElementById('preview-file-size');
        const typeEl = document.getElementById('preview-file-type');
        const emptyDropzone = document.getElementById('dropzone-empty');
        const previewDropzone = document.getElementById('dropzone-file-preview');

        if (nameEl) nameEl.textContent = file.name;

        // Hitung ukuran file dalam B, KB, atau MB
        let sizeStr = '';
        if (file.size < 1024) {
            sizeStr = file.size + ' B';
        } else if (file.size < 1024 * 1024) {
            sizeStr = (file.size / 1024).toFixed(1) + ' KB';
        } else {
            sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }
        if (sizeEl) sizeEl.textContent = sizeStr;

        // Ekstensi file
        const ext = file.name.split('.').pop().toUpperCase();
        if (typeEl) typeEl.textContent = ext + ' SPREADSHEET';

        if (emptyDropzone) emptyDropzone.classList.add('hidden');
        if (previewDropzone) previewDropzone.classList.remove('hidden');
    }

    function removeSelectedFile() {
        const input = document.getElementById('file-upload-exams');
        if (input) input.value = '';

        const emptyDropzone = document.getElementById('dropzone-empty');
        const previewDropzone = document.getElementById('dropzone-file-preview');

        if (emptyDropzone) emptyDropzone.classList.remove('hidden');
        if (previewDropzone) previewDropzone.classList.add('hidden');
    }
</script>
@endsection
