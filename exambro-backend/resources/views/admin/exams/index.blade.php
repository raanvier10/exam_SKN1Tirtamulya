@extends('layouts.admin')
@section('title', 'Manajemen Ujian')

@section('content')
@php
    $isTeacher = auth()->user()->isTeacher();
    $defaultMode = $isTeacher ? 'uts' : 'uas';
    $initialMode = request('mode', request('category', $defaultMode));
    $isUts = ($initialMode === 'uts');
    $hideActionButtons = $isTeacher && !$isUts;

    $uasOngoing = $uasExams->where('is_ongoing', true)->count();
    $uasActive = $uasExams->where('has_active', true)->count();
    $uasClassesCount = $uasClassGroups->count();

    $utsOngoing = $utsExams->where('is_ongoing', true)->count();
    $utsActive = $utsExams->where('has_active', true)->count();
    $utsTeachersCount = $utsTeacherGroups->count();

    $classesX = $classes->filter(fn($c) => preg_match('/(^|[\s\-_.,])(X|10)($|[\s\-_.,])/i', $c->name))->values();
    $classesXI = $classes->filter(fn($c) => preg_match('/(^|[\s\-_.,])(XI|11)($|[\s\-_.,])/i', $c->name))->values();
    $classesXII = $classes->filter(fn($c) => preg_match('/(^|[\s\-_.,])(XII|12)($|[\s\-_.,])/i', $c->name))->values();
    $classesOther = $classes->reject(fn($c) => preg_match('/(^|[\s\-_.,])(X|XI|XII|10|11|12)($|[\s\-_.,])/i', $c->name))->values();
@endphp

<style>
    /* Smooth Dropdown Flyout Transitions */
    .dropdown-flyout {
        transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.2s;
        transform-origin: top right;
    }
    .dropdown-flyout-left {
        transform-origin: top left;
    }
    .dropdown-flyout-closed {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: scale(0.95) translateY(-6px) !important;
    }
    .dropdown-flyout-open {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        transform: scale(1) translateY(0) !important;
    }

    /* Smooth CSS Grid Accordion for Sub-items */
    .uts-accordion-wrapper {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .uts-accordion-wrapper.is-open {
        grid-template-rows: 1fr;
    }
    .uts-accordion-content {
        overflow: hidden;
        min-height: 0;
    }

    .uts-class-item {
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .uts-class-item:hover {
        padding-left: 0.85rem;
    }
</style>

<!-- 1. HEADER SECTION (Minimalist, Spacious & High-End) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <span id="pageAccentIndicator" class="w-2.5 h-6 {{ $isUts ? 'bg-emerald-600' : 'bg-indigo-600' }} rounded-full inline-block transition-colors"></span>
        <div>
            <h2 id="pageTitleHeading" class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Ujian {{ $isUts ? 'UTS' : 'UAS' }}</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola jadwal ujian, pemantauan ruang, dan penilaian siswa.</p>
        </div>
    </div>
    <div id="headerExamActionButtons" 
         style="{{ $hideActionButtons ? 'display: none !important;' : '' }}"
         class="{{ $hideActionButtons ? '!hidden ' : '' }}grid grid-cols-2 sm:flex items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
        <button type="button" onclick="importModal.showModal()" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-indigo-600 shadow-2xs transition-all duration-150 active:scale-[0.98] w-full sm:w-auto cursor-pointer">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
        <a id="btnTambahUjian" href="{{ route('admin.exams.create') }}?type={{ $isUts ? 'uts' : 'uas' }}" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 {{ $isUts ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/20' }} text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Ujian</span>
        </a>
    </div>
</div>

<!-- Modal Import Excel -->
<dialog id="importModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto m-auto">
    <div class="px-5 py-4 border-b border-slate-100 bg-white">
        <h3 class="text-lg font-bold text-slate-900">Import Jadwal Ujian</h3>
        <p class="text-sm text-slate-500 mt-0.5">Pastikan format kolom sesuai dengan template resmi.</p>
    </div>
    
    <form action="{{ route('admin.exams.import') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-white space-y-4">
        @csrf
        <div class="flex items-start gap-3 p-4 bg-indigo-50/40 border border-indigo-100 rounded-xl">
            <div class="p-2 bg-white shadow-2xs border border-indigo-100 text-indigo-600 rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Format Wajib Sesuai!</h4>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Kolom <code>target_kelas</code> bisa diisi nama kelas atau kosongkan untuk Semua Kelas. Data yang sudah ada otomatis dilewati.</p>
                <a href="{{ route('admin.exams.template') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    Download Template CSV &rarr;
                </a>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File Excel/CSV</label>
            <div id="dropzone-empty" class="mt-1 flex justify-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-7 hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-exams').click()">
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

            <div id="dropzone-file-preview" class="hidden mt-1 p-4 rounded-2xl border-2 border-emerald-500/40 bg-emerald-50/40 transition-all">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
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
                    <button type="button" onclick="removeSelectedFile()" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors shrink-0" title="Ganti file">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <input id="file-upload-exams" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="handleFileSelected(this)">
        </div>

        <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" onclick="importModal.close()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-2xs">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-colors">Upload Data</button>
        </div>
    </form>
</dialog>

<!-- 3. QUICK STATS BAR (Sleek, Utilitarian & Non-Intrusive) -->
<!-- Stats UAS -->
<div id="statsUasBar" class="{{ $isUts ? 'hidden' : '' }} grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Ruang Kelas</div>
            <div class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{{ $uasClassesCount }} <span class="text-xs font-medium text-slate-500">Kelas</span></div>
        </div>
    </div>
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Soal</div>
            <div class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{{ $uasExams->count() }} <span class="text-xs font-medium text-slate-500">Ujian</span></div>
        </div>
    </div>
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sedang Berlangsung</div>
            <div class="text-base sm:text-lg font-bold text-amber-600 tracking-tight">{{ $uasOngoing }} <span class="text-xs font-medium text-amber-600/70">Sesi</span></div>
        </div>
    </div>
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jadwal Aktif</div>
            <div class="text-base sm:text-lg font-bold text-emerald-600 tracking-tight">{{ $uasActive }} <span class="text-xs font-medium text-emerald-600/70">Ujian</span></div>
        </div>
    </div>
</div>

<!-- Stats UTS -->
<div id="statsUtsBar" class="{{ !$isUts ? 'hidden' : '' }} grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Guru Pengampu</div>
            <div class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{{ $utsTeachersCount }} <span class="text-xs font-medium text-slate-500">Orang</span></div>
        </div>
    </div>
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Soal</div>
            <div class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{{ $utsExams->count() }} <span class="text-xs font-medium text-slate-500">Ujian</span></div>
        </div>
    </div>
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sedang Berlangsung</div>
            <div class="text-base sm:text-lg font-bold text-amber-600 tracking-tight">{{ $utsOngoing }} <span class="text-xs font-medium text-amber-600/70">Sesi</span></div>
        </div>
    </div>
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0 font-bold shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jadwal Aktif</div>
            <div class="text-base sm:text-lg font-bold text-emerald-600 tracking-tight">{{ $utsActive }} <span class="text-xs font-medium text-emerald-600/70">Ujian</span></div>
        </div>
    </div>
</div>

<!-- Bulk Delete Floating Bar -->
<div id="bulkBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white rounded-2xl shadow-2xl px-5 py-3 flex items-center gap-4 transition-all">
    <span class="text-sm font-semibold"><span id="bulkCount">0</span> sesi dipilih</span>
    <button type="button" id="bulkDeleteBtn" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all active:scale-[0.98] cursor-pointer">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Hapus Terpilih
    </button>
    <button type="button" id="bulkCancelBtn" class="text-xs text-slate-400 hover:text-white transition-colors cursor-pointer">Batal</button>
</div>

<!-- Hidden bulk delete form -->
<form id="bulkDeleteForm" action="{{ route('admin.exams.bulk-delete') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- ========================================================================= -->
<!-- 4. KONTEN MODE UAS (id="uasContainer")                                      -->
<!-- ========================================================================= -->
<div id="uasContainer" class="{{ $isUts ? 'hidden' : '' }} space-y-4">
    <!-- View Switcher & Toolbar UAS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-3.5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Dual View Segmented Pill -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/70 select-none shrink-0 self-start sm:self-auto">
            <button type="button" 
                    id="btnViewUasExams" 
                    onclick="switchUasSubView('exams')" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs cursor-pointer">
                Daftar Ujian UAS ({{ $uasExams->count() }})
            </button>
            <button type="button" 
                    id="btnViewUasClasses" 
                    onclick="switchUasSubView('classes')" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-500 hover:text-slate-900 cursor-pointer">
                Per Ruang Kelas ({{ $uasClassGroups->count() }})
            </button>
        </div>

        <!-- Toolbar: Search & Filters -->
        <div class="flex items-center gap-2 flex-wrap flex-1 justify-end">
            <!-- Search Input UAS -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="searchUasInput" 
                    placeholder="Cari ruang kelas atau ujian..." 
                    class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                >
            </div>

            <!-- Filter Status UAS -->
            <div class="relative" id="uasStatusFilterDropdown">
                <button type="button"
                        id="uasStatusFilterBtn"
                        onclick="toggleUasStatusFilterMenu()"
                        class="appearance-none min-w-[130px] pl-3 pr-7 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left"
                        aria-expanded="false"
                        title="Filter Status">
                    <span id="uasStatusFilterLabel">Semua Status</span>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg id="uasStatusFilterChevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
                <input type="hidden" id="filterUasStatus" value="">

                <div id="uasStatusFilterMenu" class="dropdown-flyout dropdown-flyout-left dropdown-flyout-closed absolute left-0 sm:left-auto sm:right-0 mt-2 w-48 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 p-1.5 space-y-0.5">
                    <button type="button" onclick="setUasStatusFilter('', 'Semua Status')" class="uas-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Semua Status</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasStatusFilter('ongoing', 'Sedang Berlangsung')" class="uas-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-amber-700 hover:bg-amber-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Sedang Berlangsung</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasStatusFilter('active', 'Aktif')" class="uas-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Aktif</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasStatusFilter('finished', 'Selesai')" class="uas-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Selesai</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasStatusFilter('inactive', 'Nonaktif')" class="uas-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-rose-700 hover:bg-rose-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Nonaktif</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Filter Kelas / Tingkat UAS -->
            <div class="relative" id="uasGradeFilterDropdown">
                <button type="button"
                        id="uasGradeFilterBtn"
                        onclick="toggleUasGradeFilterMenu()"
                        class="appearance-none min-w-[130px] pl-3 pr-7 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left"
                        aria-expanded="false"
                        title="Filter Kelas">
                    <span id="uasGradeFilterLabel">Semua Kelas</span>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg id="uasGradeFilterChevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
                <input type="hidden" id="filterUasGrade" value="">

                <div id="uasGradeFilterMenu" class="dropdown-flyout dropdown-flyout-left dropdown-flyout-closed absolute right-0 mt-2 w-44 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 p-1.5 space-y-0.5">
                    <button type="button" onclick="setUasGradeFilter('', 'Semua Kelas')" class="uas-grade-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Semua Kelas</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasGradeFilter('X', 'Kelas X')" class="uas-grade-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-rose-700 hover:bg-rose-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Kelas X</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasGradeFilter('XI', 'Kelas XI')" class="uas-grade-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-sky-700 hover:bg-sky-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            <span>Kelas XI</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUasGradeFilter('XII', 'Kelas XII')" class="uas-grade-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Kelas XII</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SUB-VIEW 1: DAFTAR UJIAN UAS (Clean, Uncluttered Master View - NO 39 DUPLICATE BOXES!) -->
    <div id="uasViewExamsList" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
        @forelse($uasExams as $item)
        @php
            $primary = $item->primary;
            $canManage = auth()->user()->isAdmin() || ($primary->created_by === auth()->id());
            $isExpired = $item->is_expired ?? $primary->isExpired();
            $isOngoing = $item->is_ongoing ?? $primary->isOngoing();
            $status = $isOngoing ? 'ongoing' : ($isExpired ? 'finished' : ($item->has_active ? 'active' : 'inactive'));
            $classList = $item->classes->pluck('name')->implode(', ');
            $gradesList = implode(',', $item->detected_grades ?? []);
        @endphp
        <div class="uas-master-exam-row p-4 sm:p-5 hover:bg-slate-50/70 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4"
             data-exam-title="{{ strtolower($item->title) }}"
             data-exam-classes="{{ strtolower($classList ?: 'semua kelas') }}"
             data-exam-status="{{ $status }}"
             data-exam-grades="{{ $gradesList }}">
            
            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                @if($canManage)
                    @if($isOngoing)
                        <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed mt-1 shrink-0 opacity-40">
                    @else
                        <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-1 shrink-0" value="{{ $primary->id }}">
                    @endif
                @endif

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h4 class="text-base font-bold text-slate-900 tracking-tight leading-snug">
                            {{ $item->title }}
                        </h4>

                        <!-- Status Badge -->
                        @if($isOngoing)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Sedang Berlangsung
                            </span>
                        @elseif($isExpired)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                Selesai
                            </span>
                        @elseif($item->has_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200/60">
                                Nonaktif
                            </span>
                        @endif

                        <!-- Target Audience Badge -->
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/70">
                            {{ $item->classes->count() > 0 ? ($item->classes->count() >= 39 ? 'Semua Kelas (39 Kelas)' : $item->classes->count() . ' Ruang Kelas') : 'Semua Kelas' }}
                        </span>
                    </div>

                    <!-- Meta info row -->
                    <div class="flex items-center gap-2 sm:gap-3 text-xs text-slate-500 mt-2 flex-wrap">
                        <div class="inline-flex items-center gap-1.5 text-slate-600">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }}</span>
                            <span class="font-mono text-slate-500">({{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB)</span>
                        </div>

                        <span class="text-slate-300">•</span>

                        <div class="inline-flex items-center gap-1.5">
                            <span class="font-semibold text-slate-700">{{ $primary->duration }} Mnt</span>
                            <span class="text-slate-400">({{ round($primary->duration / 35, 1) }} JP)</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                Max {{ $primary->max_violation }}x Keluar
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end gap-2 shrink-0">
                <a href="{{ route('admin.exams.show', $primary->id) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs hover:shadow-md hover:shadow-indigo-500/20 transition-all active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Pantau Siswa</span>
                </a>

                @if($canManage)
                    <a href="{{ route('admin.exams.edit', $primary->id) }}" class="inline-flex items-center justify-center px-3 py-2 bg-white border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 text-slate-700 rounded-xl text-xs font-medium shadow-2xs transition-all">
                        Edit
                    </a>
                    @if(!$isOngoing)
                        <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini? Data sesi ujian akan dihapus.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3 py-2 bg-white border border-slate-200/80 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-700 rounded-xl text-xs font-medium shadow-2xs transition-all cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
        @empty
        <div class="py-12 text-center p-8">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h5 class="text-base font-bold text-slate-800">Belum Ada Jadwal Ujian UAS</h5>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Klik tombol "Tambah Ujian" di atas untuk menambahkan jadwal ujian sekolah (UAS).</p>
        </div>
        @endforelse
    </div>

    <!-- SUB-VIEW 2: RINCIAN PER RUANG KELAS (Compact, Elegant Accordion Directory) -->
    <div id="uasViewClassesList" class="hidden space-y-2.5">
        <div id="uasClassCardList" class="space-y-2.5">
            @forelse($uasClassGroups as $cg)
            @php
                $cGrade = strtoupper(trim($cg->grade ?? ''));
                if ($cGrade === 'X' || preg_match('/\b(X|10)\b/i', $cg->name)) {
                    $badgeText = 'X';
                    $badgeStyle = 'bg-rose-50 text-rose-700 border border-rose-200/80';
                } elseif ($cGrade === 'XI' || preg_match('/\b(XI|11)\b/i', $cg->name)) {
                    $badgeText = 'XI';
                    $badgeStyle = 'bg-sky-50 text-sky-700 border border-sky-200/80';
                } elseif ($cGrade === 'XII' || preg_match('/\b(XII|12)\b/i', $cg->name)) {
                    $badgeText = 'XII';
                    $badgeStyle = 'bg-emerald-50 text-emerald-700 border border-emerald-200/80';
                } else {
                    $badgeText = substr($cg->grade, 0, 3) ?: 'KL';
                    $badgeStyle = 'bg-slate-100 text-slate-700 border border-slate-200';
                }
            @endphp
            <div class="uas-class-card bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:border-slate-300 transition-all overflow-hidden"
                 data-class-id="{{ $cg->id }}"
                 data-class-name="{{ strtolower($cg->name) }}"
                 data-grade="{{ $cg->grade }}">
                
                <!-- Accordion Header: Ruang Kelas -->
                <div class="p-3.5 sm:p-4 flex items-center justify-between gap-3 cursor-pointer select-none hover:bg-slate-50/70 transition-colors"
                     onclick="toggleUasClassAccordion('{{ $cg->id }}')">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-xl {{ $badgeStyle }} flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            {{ $badgeText }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h5 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight truncate">
                                    Ruang Kelas {{ $cg->name }}
                                </h5>
                                @if($cg->ongoing_exams > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $cg->ongoing_exams }} Sedang Berlangsung
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Pengawasan UAS Ruang • <span class="font-semibold text-slate-700">{{ $cg->total_exams }} Jadwal Soal</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-200/70 px-2 py-0.5 rounded-lg">
                            {{ $cg->total_exams }} Ujian
                        </span>
                        <svg id="chevron-uas-class-{{ $cg->id }}" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Accordion Content: Ujian di Kelas ini -->
                <div id="body-uas-class-{{ $cg->id }}" class="hidden border-t border-slate-100 bg-slate-50/50 p-3 sm:p-4 space-y-2.5">
                    @foreach($cg->exams as $item)
                    @php
                        $primary = $item->target_session ?? $item->primary;
                        $canManage = auth()->user()->isAdmin() || ($primary->created_by === auth()->id());
                        $isExpired = $item->is_expired ?? $primary->isExpired();
                        $isOngoing = $item->is_ongoing ?? $primary->isOngoing();
                        $status = $isOngoing ? 'ongoing' : ($isExpired ? 'finished' : ($item->has_active ? 'active' : 'inactive'));
                    @endphp
                    <div class="uas-exam-row bg-white rounded-xl border border-slate-200/80 p-3.5 hover:border-indigo-300 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3"
                         data-exam-title="{{ strtolower($item->title) }}"
                         data-exam-status="{{ $status }}">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            @if($canManage)
                                @if($isOngoing)
                                    <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed mt-0.5 shrink-0 opacity-40">
                                @else
                                    <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-0.5 shrink-0" value="{{ $primary->id }}">
                                @endif
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h6 class="text-sm font-bold text-slate-900 tracking-tight">{{ $item->title }}</h6>
                                    @if($isOngoing)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300">Sedang Berlangsung</span>
                                    @elseif($isExpired)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">Selesai</span>
                                    @elseif($item->has_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-500 mt-1 flex-wrap">
                                    <span>{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }} ({{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB)</span>
                                    <span class="text-slate-300">•</span>
                                    <span>{{ $primary->duration }} Mnt ({{ round($primary->duration / 35, 1) }} JP)</span>
                                    <span class="text-rose-600 font-semibold text-[10px]">Max {{ $primary->max_violation }}x</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 shrink-0">
                            <a href="{{ route('admin.exams.show', array_merge([$primary->id], ($cg->class ? ['class_id' => $cg->id] : []))) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-2xs">
                                Pantau Siswa
                            </a>
                            @if($canManage)
                                <a href="{{ route('admin.exams.edit', $primary->id) }}" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium shadow-2xs">Edit</a>
                                @if(!$isOngoing)
                                    <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-600 text-slate-700 rounded-lg text-xs font-medium shadow-2xs cursor-pointer">Hapus</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="py-12 text-center bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8">
                <h5 class="text-base font-bold text-slate-800">Belum Ada Ruang Kelas UAS</h5>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pesan jika pencarian UAS nihil -->
    <div id="noMatchUasMessage" class="hidden py-12 text-center bg-white rounded-2xl border border-slate-200 p-6">
        <h5 class="text-sm font-bold text-slate-800">Ruang Kelas atau Ujian UAS Tidak Ditemukan</h5>
        <p class="text-xs text-slate-500 mt-1">Sesuaikan kata kunci pencarian, filter kelas, atau filter status.</p>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. KONTEN MODE UTS (id="utsContainer")                                      -->
<!-- ========================================================================= -->
<div id="utsContainer" class="{{ !$isUts ? 'hidden' : '' }} space-y-4">
    <!-- Bar Kontrol UTS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-3.5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Search Input UTS -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                id="searchUtsInput" 
                placeholder="Cari guru pengampu, mapel, atau kelas..." 
                class="w-full pl-10 pr-9 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
            >
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Filter Status UTS -->
            <div class="relative" id="utsStatusFilterDropdown">
                <button type="button" 
                        id="utsStatusFilterBtn"
                        onclick="toggleUtsStatusFilterMenu()"
                        class="appearance-none min-w-[130px] pl-3 pr-8 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left"
                        aria-expanded="false"
                        title="Filter Status">
                    <span id="utsStatusFilterLabel">Semua Status</span>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg id="utsStatusFilterChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
                <input type="hidden" id="filterUtsStatus" value="">

                <div id="utsStatusFilterMenu" class="dropdown-flyout dropdown-flyout-left dropdown-flyout-closed absolute left-0 mt-2 w-48 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 p-1.5 space-y-0.5">
                    <button type="button" onclick="setUtsStatusFilter('', 'Semua Status')" class="uts-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Semua Status</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUtsStatusFilter('ongoing', 'Sedang Berlangsung')" class="uts-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Sedang Berlangsung</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUtsStatusFilter('active', 'Aktif')" class="uts-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-blue-700 hover:bg-blue-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Aktif</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUtsStatusFilter('finished', 'Selesai')" class="uts-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Selesai</span>
                        </div>
                    </button>
                    <button type="button" onclick="setUtsStatusFilter('inactive', 'Nonaktif')" class="uts-status-opt w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-rose-700 hover:bg-rose-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Nonaktif</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Dropdown Filter Kelas UTS -->
            <div class="relative" id="utsClassFilterDropdown">
                <button type="button" 
                        id="utsClassFilterBtn"
                        onclick="toggleUtsClassFilterMenu()" 
                        class="appearance-none min-w-[130px] pl-3 pr-8 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left"
                        aria-expanded="false"
                        title="Pilih Kelas">
                    <span id="utsClassFilterLabel">Pilih Kelas</span>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg id="utsClassFilterChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>

                <div id="utsClassFilterMenu" class="dropdown-flyout dropdown-flyout-closed absolute right-0 mt-2 w-64 sm:w-72 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 p-2.5 space-y-1.5 max-h-[75vh] overflow-y-auto">
                    <button type="button" 
                            onclick="setUtsClassFilter('', '', 'Semua Kelas')" 
                            class="uts-class-opt-btn w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all text-slate-700 hover:bg-slate-50 border border-transparent hover:border-slate-200/80 cursor-pointer">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Semua Kelas</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Reset</span>
                    </button>

                    <div class="border-t border-slate-100 my-1"></div>

                    <!-- Accordion Tingkat Kelas X -->
                    <div class="uts-grade-accordion rounded-xl border border-slate-200/80 overflow-hidden">
                        <button type="button" 
                                onclick="toggleUtsGradeSection('X')" 
                                class="w-full flex items-center justify-between px-3 py-2 bg-slate-50/80 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Kelas X</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span class="text-[10px] font-semibold bg-white px-1.5 py-0.5 rounded-md border border-slate-200/60 text-slate-600">{{ $classesX->count() }} Kelas</span>
                                <svg id="chevron-grade-X" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        <div id="section-grade-X" class="uts-accordion-wrapper">
                            <div class="uts-accordion-content">
                                <div class="p-1.5 bg-white space-y-1 border-t border-slate-100">
                                    <button type="button" 
                                            onclick="setUtsClassFilter('X', '', 'Semua Kelas X')" 
                                            class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-all cursor-pointer">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        <span>Semua Tingkat X</span>
                                    </button>
                                    @foreach($classesX as $cls)
                                        <button type="button" 
                                                onclick="setUtsClassFilter('X', '{{ strtolower($cls->name) }}', '{{ $cls->name }}')" 
                                                class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                            <span>{{ $cls->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Tingkat Kelas XI -->
                    <div class="uts-grade-accordion rounded-xl border border-slate-200/80 overflow-hidden">
                        <button type="button" 
                                onclick="toggleUtsGradeSection('XI')" 
                                class="w-full flex items-center justify-between px-3 py-2 bg-slate-50/80 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                <span>Kelas XI</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span class="text-[10px] font-semibold bg-white px-1.5 py-0.5 rounded-md border border-slate-200/60 text-slate-600">{{ $classesXI->count() }} Kelas</span>
                                <svg id="chevron-grade-XI" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        <div id="section-grade-XI" class="uts-accordion-wrapper">
                            <div class="uts-accordion-content">
                                <div class="p-1.5 bg-white space-y-1 border-t border-slate-100">
                                    <button type="button" 
                                            onclick="setUtsClassFilter('XI', '', 'Semua Kelas XI')" 
                                            class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-sky-700 hover:bg-sky-50 transition-all cursor-pointer">
                                        <svg class="w-3 h-3 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        <span>Semua Tingkat XI</span>
                                    </button>
                                    @foreach($classesXI as $cls)
                                        <button type="button" 
                                                onclick="setUtsClassFilter('XI', '{{ strtolower($cls->name) }}', '{{ $cls->name }}')" 
                                                class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                            <span>{{ $cls->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Tingkat Kelas XII -->
                    <div class="uts-grade-accordion rounded-xl border border-slate-200/80 overflow-hidden">
                        <button type="button" 
                                onclick="toggleUtsGradeSection('XII')" 
                                class="w-full flex items-center justify-between px-3 py-2 bg-slate-50/80 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Kelas XII</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span class="text-[10px] font-semibold bg-white px-1.5 py-0.5 rounded-md border border-slate-200/60 text-slate-600">{{ $classesXII->count() }} Kelas</span>
                                <svg id="chevron-grade-XII" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        <div id="section-grade-XII" class="uts-accordion-wrapper">
                            <div class="uts-accordion-content">
                                <div class="p-1.5 bg-white space-y-1 border-t border-slate-100">
                                    <button type="button" 
                                            onclick="setUtsClassFilter('XII', '', 'Semua Kelas XII')" 
                                            class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-amber-700 hover:bg-amber-50 transition-all cursor-pointer">
                                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        <span>Semua Tingkat XII</span>
                                    </button>
                                    @foreach($classesXII as $cls)
                                        <button type="button" 
                                                onclick="setUtsClassFilter('XII', '{{ strtolower($cls->name) }}', '{{ $cls->name }}')" 
                                                class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                            <span>{{ $cls->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($classesOther->isNotEmpty())
                    <!-- Accordion Kelas Lainnya -->
                    <div class="uts-grade-accordion rounded-xl border border-slate-200/80 overflow-hidden">
                        <button type="button" 
                                onclick="toggleUtsGradeSection('UMUM')" 
                                class="w-full flex items-center justify-between px-3 py-2 bg-slate-50/80 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors cursor-pointer">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                <span>Lainnya / Umum</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span class="text-[10px] font-semibold bg-white px-1.5 py-0.5 rounded-md border border-slate-200/60 text-slate-600">{{ $classesOther->count() }} Kelas</span>
                                <svg id="chevron-grade-UMUM" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        <div id="section-grade-UMUM" class="uts-accordion-wrapper">
                            <div class="uts-accordion-content">
                                <div class="p-1.5 bg-white space-y-1 border-t border-slate-100">
                                    @foreach($classesOther as $cls)
                                        <button type="button" 
                                                onclick="setUtsClassFilter('UMUM', '{{ strtolower($cls->name) }}', '{{ $cls->name }}')" 
                                                class="uts-class-item w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                            <span>{{ $cls->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- DAFTAR GURU PENGAMPU UTS (id="utsTeacherAccordionList") -->
    <div class="space-y-4" id="utsTeacherAccordionList">
        @forelse($utsTeacherGroups as $tIdx => $t)
        @php
            $teacherExams = collect($t->grades)->flatMap(fn($g) => $g->exams)->unique('group_id');
            $totalX = $t->grades['X']->exams->count() ?? 0;
            $totalXI = $t->grades['XI']->exams->count() ?? 0;
            $totalXII = $t->grades['XII']->exams->count() ?? 0;
            $totalUmum = $t->grades['UMUM']->exams->count() ?? 0;
            $defaultActiveGrade = $totalX > 0 ? 'X' : ($totalXI > 0 ? 'XI' : ($totalXII > 0 ? 'XII' : ($totalUmum > 0 ? 'UMUM' : 'X')));
        @endphp

        <!-- Card Guru UTS -->
        <div class="uts-teacher-block bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all duration-200 overflow-hidden"
             data-teacher-name="{{ strtolower($t->name) }}"
             data-teacher-id="{{ $t->id }}">
            
            <!-- Accordion Header: Klik Bar Guru -->
            <div class="p-4 sm:p-5 flex items-center justify-between gap-3 cursor-pointer select-none hover:bg-slate-50/70 transition-colors"
                 onclick="toggleUtsTeacherAccordion('{{ $t->id }}')">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $t->is_current ? 'bg-emerald-600 text-white shadow-xs' : ($t->role === 'admin' ? 'bg-slate-900 text-white' : 'bg-emerald-50 border border-emerald-100 text-emerald-700') }} flex items-center justify-center font-bold text-sm shrink-0">
                        {{ $t->initials }}
                    </div>
                    
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base sm:text-lg font-bold text-slate-900 truncate tracking-tight">
                                {{ $t->name }}
                            </h4>
                            @if($t->is_current)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Akun Anda
                                </span>
                            @elseif($t->role === 'admin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    Administrator
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Guru Pengampu
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5 flex-wrap">
                            <span>{{ $t->email }}</span>
                            <span class="text-slate-300">•</span>
                            <span class="font-semibold text-slate-700">{{ $t->total_exams }} Ujian Diupload</span>
                            @if($t->ongoing_exams > 0)
                                <span class="text-slate-300">•</span>
                                <span class="inline-flex items-center gap-1 font-bold text-amber-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ $t->ongoing_exams }} Sedang Berlangsung
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Bar Pilihan Kelas X, XI, XII & Chevron Toggle -->
                <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                    <div class="inline-flex items-center p-0.5 sm:p-1 bg-slate-100 rounded-xl border border-slate-200/80 gap-0.5 shadow-2xs" onclick="event.stopPropagation()">
                        <!-- Tombol Kelas X -->
                        <button type="button" 
                                onclick="selectUtsTeacherGrade('{{ $t->id }}', 'X')"
                                id="bar-btn-{{ $t->id }}-X"
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'X' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }} cursor-pointer"
                                title="Kelas X ({{ $totalX }} Ujian)">
                            <span>X</span>
                            <span class="ml-0.5 px-1 py-0.2 rounded text-[10px] {{ $defaultActiveGrade === 'X' ? 'bg-white/20 text-white' : ($totalX > 0 ? 'bg-emerald-100 text-emerald-700 font-extrabold' : 'bg-slate-200/60 text-slate-400') }}">
                                {{ $totalX }}
                            </span>
                        </button>

                        <!-- Tombol Kelas XI -->
                        <button type="button" 
                                onclick="selectUtsTeacherGrade('{{ $t->id }}', 'XI')"
                                id="bar-btn-{{ $t->id }}-XI"
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'XI' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }} cursor-pointer"
                                title="Kelas XI ({{ $totalXI }} Ujian)">
                            <span>XI</span>
                            <span class="ml-0.5 px-1 py-0.2 rounded text-[10px] {{ $defaultActiveGrade === 'XI' ? 'bg-white/20 text-white' : ($totalXI > 0 ? 'bg-emerald-100 text-emerald-700 font-extrabold' : 'bg-slate-200/60 text-slate-400') }}">
                                {{ $totalXI }}
                            </span>
                        </button>

                        <!-- Tombol Kelas XII -->
                        <button type="button" 
                                onclick="selectUtsTeacherGrade('{{ $t->id }}', 'XII')"
                                id="bar-btn-{{ $t->id }}-XII"
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'XII' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }} cursor-pointer"
                                title="Kelas XII ({{ $totalXII }} Ujian)">
                            <span>XII</span>
                            <span class="ml-0.5 px-1 py-0.2 rounded text-[10px] {{ $defaultActiveGrade === 'XII' ? 'bg-white/20 text-white' : ($totalXII > 0 ? 'bg-emerald-100 text-emerald-700 font-extrabold' : 'bg-slate-200/60 text-slate-400') }}">
                                {{ $totalXII }}
                            </span>
                        </button>

                        @if($totalUmum > 0)
                        <button type="button" 
                                onclick="selectUtsTeacherGrade('{{ $t->id }}', 'UMUM')"
                                id="bar-btn-{{ $t->id }}-UMUM"
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'UMUM' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }} cursor-pointer">
                            <span>Umum</span>
                            <span class="ml-0.5 px-1 py-0.2 rounded text-[10px] {{ $defaultActiveGrade === 'UMUM' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800 font-extrabold' }}">
                                {{ $totalUmum }}
                            </span>
                        </button>
                        @endif
                    </div>

                    <button type="button" class="w-8 h-8 rounded-xl border border-slate-200/80 bg-white flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors cursor-pointer">
                        <svg id="chevron-uts-{{ $t->id }}" class="w-4 h-4 transition-transform duration-200 transform {{ ($t->is_current || $tIdx === 0) ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body Guru UTS: Grade Panes -->
            <div id="body-uts-{{ $t->id }}" class="{{ ($t->is_current || $tIdx === 0) ? 'block' : 'hidden' }} border-t border-slate-100 bg-slate-50/50 p-3.5 sm:p-4">
                @foreach(['X', 'XI', 'XII', 'UMUM'] as $gradeKey)
                @php
                    $gradeObj = $t->grades[$gradeKey] ?? null;
                    $gradeExams = $gradeObj ? $gradeObj->exams : collect();
                    $isPaneActive = ($gradeKey === $defaultActiveGrade);
                @endphp
                
                <div id="pane-uts-{{ $t->id }}-{{ $gradeKey }}" 
                     class="grade-pane-uts-{{ $t->id }} {{ $isPaneActive ? 'block' : 'hidden' }} space-y-2.5">
                    
                    @if($gradeExams->isEmpty())
                        <div class="py-8 text-center bg-white rounded-2xl border-2 border-dashed border-slate-200 p-6">
                            <h5 class="text-xs sm:text-sm font-bold text-slate-700">Belum Ada Ujian untuk Kelas {{ $gradeKey }}</h5>
                            <p class="text-xs text-slate-400 mt-0.5">Guru ini belum mengunggah jadwal UTS untuk tingkat Kelas {{ $gradeKey }}.</p>
                            @if(($t->is_current ?? false) || auth()->user()->isAdmin())
                                <a href="{{ route('admin.exams.create') }}?type=uts" class="inline-flex items-center gap-1.5 px-3 py-1.5 mt-3 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Buat Ujian Kelas</span>
                                </a>
                            @endif
                        </div>
                    @else
                        @foreach($gradeExams as $grp)
                        @php
                            $primary = $grp->primary ?? $grp;
                            $classList = $grp->classes->pluck('name')->implode(', ');
                            $canManage = auth()->user()->isAdmin() || ($primary->created_by === auth()->id());
                            $isExpired = $grp->is_expired ?? false;
                            $isOngoing = $grp->is_ongoing ?? false;
                            $status = $isOngoing ? 'ongoing' : ($isExpired ? 'finished' : ($grp->has_active ? 'active' : 'inactive'));
                            $gradesList = implode(',', $grp->detected_grades ?? []);
                        @endphp

                        <div class="uts-exam-row bg-white rounded-xl border border-slate-200/80 p-4 hover:border-emerald-300 hover:shadow-2xs transition-all flex flex-col md:flex-row md:items-center justify-between gap-3.5"
                             data-exam-title="{{ strtolower($grp->title) }}"
                             data-exam-classes="{{ strtolower($classList ?: 'semua kelas') }}"
                             data-exam-status="{{ $status }}"
                             data-exam-grades="{{ $gradesList }}">
                            
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                @if($canManage)
                                    @if($isOngoing)
                                        <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed mt-0.5 shrink-0 opacity-40">
                                    @else
                                        <input type="checkbox" class="bulk-checkbox rounded text-emerald-600 focus:ring-emerald-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-0.5 shrink-0" value="{{ $primary->id }}">
                                    @endif
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h5 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-snug">
                                            {{ $grp->title }}
                                        </h5>

                                        @if($isOngoing)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Sedang Berlangsung
                                            </span>
                                        @elseif($isExpired)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">Selesai</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                        @endif

                                        @if($grp->session_count > 1)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                {{ $grp->session_count }} Sesi Terjadwal
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 sm:gap-3 text-xs text-slate-500 mt-2 flex-wrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="font-semibold text-slate-700">Kelas:</span>
                                            <span class="px-2 py-0.5 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px]">
                                                {{ $classList ?: 'Semua Kelas' }}
                                            </span>
                                        </div>
                                        <span class="text-slate-300">•</span>
                                        <div class="inline-flex items-center gap-1.5 text-slate-600">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }}</span>
                                            <span class="font-mono text-slate-500">({{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 shrink-0">
                                @if($grp->session_count > 1)
                                    <button type="button" 
                                            onclick="const d = document.getElementById('drawer-sessions-uts-{{ $grp->group_id }}'); if(d) d.classList.toggle('hidden');" 
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-semibold transition-all shadow-2xs cursor-pointer">
                                        <span>Buka {{ $grp->session_count }} Sesi</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                @endif

                                <a href="{{ route('admin.exams.show', $primary->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs hover:shadow-emerald-500/20 transition-all active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Pantau Siswa</span>
                                </a>

                                @if($canManage)
                                    <a href="{{ route('admin.exams.edit', $primary->id) }}" class="inline-flex items-center justify-center px-3 py-2 bg-white border border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 text-slate-700 rounded-xl text-xs font-medium shadow-2xs transition-all">Edit</a>
                                    @if(!$isOngoing)
                                        <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center px-3 py-2 bg-white border border-slate-200/80 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-700 rounded-xl text-xs font-medium shadow-2xs transition-all cursor-pointer">Hapus</button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            @if($grp->session_count > 1)
                            <div id="drawer-sessions-uts-{{ $grp->group_id }}" class="hidden w-full pt-3 pb-1 border-t border-slate-100 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                    <span>Daftar {{ $grp->session_count }} Sesi Terjadwal:</span>
                                    <span class="text-[11px] text-slate-400">Pilih sesi untuk dipantau</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($grp->sessions as $idx => $s)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                                        <div>
                                            <div class="font-bold text-slate-800">Sesi {{ $idx + 1 }} • {{ \Carbon\Carbon::parse($s->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_at)->format('H:i') }} WIB</div>
                                            <div class="text-slate-500 text-[11px] mt-0.5">Kelas: {{ $s->classes->pluck('name')->implode(', ') ?: 'Semua Kelas' }}</div>
                                        </div>
                                        <a href="{{ route('admin.exams.show', $s->id) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-2xs">
                                            Pantau
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="py-12 text-center bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8">
            <h5 class="text-base font-bold text-slate-800">Belum Ada Jadwal UTS</h5>
            <p class="text-xs text-slate-500 mt-1">Ujian tengah semester yang dibuat oleh guru mata pelajaran akan muncul di sini.</p>
        </div>
        @endforelse
    </div>

    <!-- Pesan jika pencarian UTS nihil -->
    <div id="noMatchUtsMessage" class="hidden py-12 text-center bg-white rounded-2xl border border-slate-200 p-6">
        <h5 class="text-sm font-bold text-slate-800">Guru atau Ujian UTS Tidak Ditemukan</h5>
        <p class="text-xs text-slate-500 mt-1">Sesuaikan kata kunci pencarian atau status.</p>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 6. JAVASCRIPT LOGIC                                                       -->
<!-- ========================================================================= -->
<script>
    let currentUasGrade = '';
    let currentUasSubView = 'exams'; // 'exams' | 'classes'
    let currentCategory = '{{ $initialMode }}';

    // 1. Category Switcher (UAS vs UTS)
    function selectExamCategory(category) {
        currentCategory = category;
        try {
            localStorage.setItem('admin_exam_category', category);
        } catch (_) {}

        const cardUas = document.getElementById('cardCategoryUas');
        const cardUts = document.getElementById('cardCategoryUts');
        const bUas = document.getElementById('badgeCountUas');
        const bUts = document.getElementById('badgeCountUts');

        const uasContainer = document.getElementById('uasContainer');
        const utsContainer = document.getElementById('utsContainer');
        const statsUasBar = document.getElementById('statsUasBar');
        const statsUtsBar = document.getElementById('statsUtsBar');
        const pageTitleHeading = document.getElementById('pageTitleHeading');
        const pageAccentIndicator = document.getElementById('pageAccentIndicator');
        const btnTambahUjian = document.getElementById('btnTambahUjian');

        if (category === 'uas') {
            if (cardUas) {
                cardUas.className = 'sidebar-exam-sublink flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-all duration-150 bg-indigo-50 text-indigo-700 shadow-2xs';
            }
            if (cardUts) {
                cardUts.className = 'sidebar-exam-sublink flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 text-slate-600 hover:text-slate-900 hover:bg-slate-50';
            }
            if (bUas) bUas.className = 'px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/70';
            if (bUts) bUts.className = 'px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200/60 text-slate-500';

            if (uasContainer) uasContainer.classList.remove('hidden');
            if (utsContainer) utsContainer.classList.add('hidden');
            if (statsUasBar) statsUasBar.classList.remove('hidden');
            if (statsUtsBar) statsUtsBar.classList.add('hidden');

            if (pageTitleHeading) pageTitleHeading.textContent = 'Manajemen Ujian UAS';
            if (pageAccentIndicator) {
                pageAccentIndicator.classList.remove('bg-emerald-600');
                pageAccentIndicator.classList.add('bg-indigo-600');
            }
            if (btnTambahUjian) {
                btnTambahUjian.href = '{{ route("admin.exams.create") }}?type=uas';
                btnTambahUjian.className = 'inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-500/20 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 active:scale-[0.98] w-full sm:w-auto';
            }
            runUasFilter();
        } else {
            if (cardUas) {
                cardUas.className = 'sidebar-exam-sublink flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 text-slate-600 hover:text-slate-900 hover:bg-slate-50';
            }
            if (cardUts) {
                cardUts.className = 'sidebar-exam-sublink flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-all duration-150 bg-indigo-50 text-indigo-700 shadow-2xs';
            }
            if (bUas) bUas.className = 'px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200/60 text-slate-500';
            if (bUts) bUts.className = 'px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70';

            if (uasContainer) uasContainer.classList.add('hidden');
            if (utsContainer) utsContainer.classList.remove('hidden');
            if (statsUasBar) statsUasBar.classList.add('hidden');
            if (statsUtsBar) statsUtsBar.classList.remove('hidden');

            if (pageTitleHeading) pageTitleHeading.textContent = 'Manajemen Ujian UTS';
            if (pageAccentIndicator) {
                pageAccentIndicator.classList.remove('bg-indigo-600');
                pageAccentIndicator.classList.add('bg-emerald-600');
            }
            if (btnTambahUjian) {
                btnTambahUjian.href = '{{ route("admin.exams.create") }}?type=uts';
                btnTambahUjian.className = 'inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-500/20 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 active:scale-[0.98] w-full sm:w-auto';
            }
            runUtsFilter();
        }
    }

    // 2. Sub-View Switcher UAS (Exams vs Classes)
    function switchUasSubView(view) {
        currentUasSubView = view;
        const btnExams = document.getElementById('btnViewUasExams');
        const btnClasses = document.getElementById('btnViewUasClasses');
        const viewExams = document.getElementById('uasViewExamsList');
        const viewClasses = document.getElementById('uasViewClassesList');

        if (view === 'exams') {
            if (btnExams) {
                btnExams.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs cursor-pointer';
            }
            if (btnClasses) {
                btnClasses.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-500 hover:text-slate-900 cursor-pointer';
            }
            if (viewExams) viewExams.classList.remove('hidden');
            if (viewClasses) viewClasses.classList.add('hidden');
        } else {
            if (btnExams) {
                btnExams.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-500 hover:text-slate-900 cursor-pointer';
            }
            if (btnClasses) {
                btnClasses.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs cursor-pointer';
            }
            if (viewExams) viewExams.classList.add('hidden');
            if (viewClasses) viewClasses.classList.remove('hidden');
        }
        runUasFilter();
    }

    function toggleUasClassAccordion(classId) {
        const bodyEl = document.getElementById('body-uas-class-' + classId);
        const chevEl = document.getElementById('chevron-uas-class-' + classId);
        if (!bodyEl) return;
        const isHidden = bodyEl.classList.toggle('hidden');
        if (chevEl) chevEl.classList.toggle('rotate-180', !isHidden);
    }

    // Helper Flyout Dropdown
    function setDropdownFlyout(menuId, open) {
        const menu = document.getElementById(menuId);
        if (!menu) return;
        if (open) {
            menu.classList.remove('dropdown-flyout-closed');
            menu.classList.add('dropdown-flyout-open');
        } else {
            menu.classList.remove('dropdown-flyout-open');
            menu.classList.add('dropdown-flyout-closed');
        }
    }

    function isDropdownFlyoutOpen(menuId) {
        const menu = document.getElementById(menuId);
        return menu ? menu.classList.contains('dropdown-flyout-open') : false;
    }

    // 3. Dropdown Filter Status UAS
    function toggleUasStatusFilterMenu(forceClose) {
        const chev = document.getElementById('uasStatusFilterChevron');
        const shouldOpen = forceClose === undefined ? !isDropdownFlyoutOpen('uasStatusFilterMenu') : !forceClose;
        if (shouldOpen) {
            toggleUasGradeFilterMenu(true);
            setDropdownFlyout('uasStatusFilterMenu', true);
            if (chev) chev.classList.add('rotate-180');
        } else {
            setDropdownFlyout('uasStatusFilterMenu', false);
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function setUasStatusFilter(val, label) {
        const input = document.getElementById('filterUasStatus');
        const labelEl = document.getElementById('uasStatusFilterLabel');
        const btnEl = document.getElementById('uasStatusFilterBtn');
        if (input) input.value = val;
        if (labelEl) labelEl.textContent = label || 'Semua Status';
        if (btnEl) {
            if (val) {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-7 py-1.5 bg-indigo-50 text-indigo-700 rounded-xl border border-indigo-200 text-xs font-bold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            } else {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-7 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            }
        }
        toggleUasStatusFilterMenu(true);
        runUasFilter();
    }

    // 4. Dropdown Filter Grade UAS
    function toggleUasGradeFilterMenu(forceClose) {
        const chev = document.getElementById('uasGradeFilterChevron');
        const shouldOpen = forceClose === undefined ? !isDropdownFlyoutOpen('uasGradeFilterMenu') : !forceClose;
        if (shouldOpen) {
            toggleUasStatusFilterMenu(true);
            setDropdownFlyout('uasGradeFilterMenu', true);
            if (chev) chev.classList.add('rotate-180');
        } else {
            setDropdownFlyout('uasGradeFilterMenu', false);
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function setUasGradeFilter(grade, label) {
        currentUasGrade = grade || '';
        const input = document.getElementById('filterUasGrade');
        const labelEl = document.getElementById('uasGradeFilterLabel');
        const btnEl = document.getElementById('uasGradeFilterBtn');
        if (input) input.value = currentUasGrade;
        if (labelEl) labelEl.textContent = label || 'Semua Kelas';
        if (btnEl) {
            if (currentUasGrade) {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-7 py-1.5 bg-indigo-50 text-indigo-700 rounded-xl border border-indigo-200 text-xs font-bold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            } else {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-7 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            }
        }
        toggleUasGradeFilterMenu(true);
        runUasFilter();
    }

    // 5. Runner Filter UAS
    function runUasFilter() {
        const query = (document.getElementById('searchUasInput')?.value || '').toLowerCase().trim();
        const statusVal = (document.getElementById('filterUasStatus')?.value || '').toLowerCase().trim();
        let totalVisible = 0;

        // Filter View 1: Master Exam Rows
        document.querySelectorAll('.uas-master-exam-row').forEach(row => {
            const title = row.getAttribute('data-exam-title') || '';
            const classes = row.getAttribute('data-exam-classes') || '';
            const status = row.getAttribute('data-exam-status') || '';
            const grades = (row.getAttribute('data-exam-grades') || '').split(',');

            const matchesQuery = !query || title.includes(query) || classes.includes(query);
            const matchesStatus = !statusVal || status === statusVal;
            const matchesGrade = !currentUasGrade || grades.includes(currentUasGrade) || grades.includes('UMUM') || classes.includes('semua kelas');

            if (matchesQuery && matchesStatus && matchesGrade) {
                row.classList.remove('hidden');
                totalVisible++;
            } else {
                row.classList.add('hidden');
            }
        });

        // Filter View 2: Class Cards
        document.querySelectorAll('.uas-class-card').forEach(card => {
            const className = card.getAttribute('data-class-name') || '';
            const grade = card.getAttribute('data-grade') || '';
            const matchesGrade = !currentUasGrade || grade === currentUasGrade;

            let visibleExamsInClass = 0;
            card.querySelectorAll('.uas-exam-row').forEach(row => {
                const title = row.getAttribute('data-exam-title') || '';
                const status = row.getAttribute('data-exam-status') || '';
                const matchesQuery = !query || title.includes(query) || className.includes(query);
                const matchesStatus = !statusVal || status === statusVal;

                if (matchesQuery && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleExamsInClass++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (matchesGrade && visibleExamsInClass > 0) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });

        const noMatchUas = document.getElementById('noMatchUasMessage');
        if (noMatchUas) {
            noMatchUas.classList.toggle('hidden', totalVisible > 0);
        }
    }

    // 6. UTS Grade Selection on Teacher Block
    function selectUtsTeacherGrade(teacherId, gradeKey) {
        ['X', 'XI', 'XII', 'UMUM'].forEach(g => {
            const btn = document.getElementById('bar-btn-' + teacherId + '-' + g);
            const pane = document.getElementById('pane-uts-' + teacherId + '-' + g);
            if (btn) {
                if (g === gradeKey) {
                    btn.className = 'grade-choice-btn-' + teacherId + ' px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all bg-emerald-600 text-white shadow-2xs cursor-pointer';
                } else {
                    btn.className = 'grade-choice-btn-' + teacherId + ' px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900 hover:bg-white/80 cursor-pointer';
                }
            }
            if (pane) {
                pane.classList.toggle('hidden', g !== gradeKey);
                pane.classList.toggle('block', g === gradeKey);
            }
        });

        const bodyEl = document.getElementById('body-uts-' + teacherId);
        const chevEl = document.getElementById('chevron-uts-' + teacherId);
        if (bodyEl && bodyEl.classList.contains('hidden')) {
            bodyEl.classList.remove('hidden');
            bodyEl.classList.add('block');
            if (chevEl) chevEl.classList.add('rotate-180');
        }
    }

    function toggleUtsTeacherAccordion(teacherId) {
        const bodyEl = document.getElementById('body-uts-' + teacherId);
        const chevEl = document.getElementById('chevron-uts-' + teacherId);
        if (!bodyEl) return;
        const isHidden = bodyEl.classList.toggle('hidden');
        bodyEl.classList.toggle('block', !isHidden);
        if (chevEl) chevEl.classList.toggle('rotate-180', !isHidden);
    }

    // 7. UTS Filters
    let currentUtsTargetGrade = '';
    let currentUtsTargetClass = '';

    function toggleUtsStatusFilterMenu(forceClose) {
        const chev = document.getElementById('utsStatusFilterChevron');
        const shouldOpen = forceClose === undefined ? !isDropdownFlyoutOpen('utsStatusFilterMenu') : !forceClose;
        if (shouldOpen) {
            toggleUtsClassFilterMenu(true);
            setDropdownFlyout('utsStatusFilterMenu', true);
            if (chev) chev.classList.add('rotate-180');
        } else {
            setDropdownFlyout('utsStatusFilterMenu', false);
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function setUtsStatusFilter(val, label) {
        const input = document.getElementById('filterUtsStatus');
        const labelEl = document.getElementById('utsStatusFilterLabel');
        const btnEl = document.getElementById('utsStatusFilterBtn');
        if (input) input.value = val;
        if (labelEl) labelEl.textContent = label || 'Semua Status';
        if (btnEl) {
            if (val) {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-8 py-2 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200 text-xs font-bold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            } else {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-8 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            }
        }
        toggleUtsStatusFilterMenu(true);
        runUtsFilter();
    }

    function toggleUtsClassFilterMenu(forceClose) {
        const chev = document.getElementById('utsClassFilterChevron');
        const shouldOpen = forceClose === undefined ? !isDropdownFlyoutOpen('utsClassFilterMenu') : !forceClose;
        if (shouldOpen) {
            toggleUtsStatusFilterMenu(true);
            setDropdownFlyout('utsClassFilterMenu', true);
            if (chev) chev.classList.add('rotate-180');
        } else {
            setDropdownFlyout('utsClassFilterMenu', false);
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function toggleUtsGradeSection(grade) {
        const sec = document.getElementById('section-grade-' + grade);
        const chev = document.getElementById('chevron-grade-' + grade);
        if (!sec) return;
        const isOpen = sec.classList.contains('is-open');
        if (isOpen) {
            sec.classList.remove('is-open');
            if (chev) chev.classList.remove('rotate-180');
        } else {
            ['X', 'XI', 'XII', 'UMUM'].forEach(g => {
                if (g !== grade) {
                    const s = document.getElementById('section-grade-' + g);
                    const c = document.getElementById('chevron-grade-' + g);
                    if (s) s.classList.remove('is-open');
                    if (c) c.classList.remove('rotate-180');
                }
            });
            sec.classList.add('is-open');
            if (chev) chev.classList.add('rotate-180');
        }
    }

    function setUtsClassFilter(grade, className, label) {
        currentUtsTargetGrade = grade || '';
        currentUtsTargetClass = (className || '').toLowerCase().trim();

        const labelEl = document.getElementById('utsClassFilterLabel');
        const btnEl = document.getElementById('utsClassFilterBtn');
        if (labelEl) labelEl.textContent = label || 'Pilih Kelas';
        if (btnEl) {
            if (currentUtsTargetGrade || currentUtsTargetClass) {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-8 py-2 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200 text-xs font-bold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            } else {
                btnEl.className = 'appearance-none min-w-[130px] pl-3 pr-8 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-2xs cursor-pointer select-none relative text-left';
            }
        }
        toggleUtsClassFilterMenu(true);
        runUtsFilter();
    }

    function runUtsFilter() {
        const query = (document.getElementById('searchUtsInput')?.value || '').toLowerCase().trim();
        const statusVal = (document.getElementById('filterUtsStatus')?.value || '').toLowerCase().trim();
        let totalVisibleTeachers = 0;

        document.querySelectorAll('.uts-teacher-block').forEach(block => {
            const teacherName = block.getAttribute('data-teacher-name') || '';
            const teacherId = block.getAttribute('data-teacher-id') || '';
            let matchesTeacher = teacherName.includes(query);
            let visibleExamsInTeacher = 0;

            block.querySelectorAll('.uts-exam-row').forEach(row => {
                const title = row.getAttribute('data-exam-title') || '';
                const classes = row.getAttribute('data-exam-classes') || '';
                const status = row.getAttribute('data-exam-status') || '';
                const rowGrades = (row.getAttribute('data-exam-grades') || '').split(',');

                const matchesQuery = !query || title.includes(query) || classes.includes(query) || teacherName.includes(query);
                const matchesStatus = !statusVal || status === statusVal;
                const matchesGrade = !currentUtsTargetGrade || rowGrades.includes(currentUtsTargetGrade);
                const matchesClass = !currentUtsTargetClass || classes.includes(currentUtsTargetClass);

                if (matchesQuery && matchesStatus && matchesGrade && matchesClass) {
                    row.classList.remove('hidden');
                    visibleExamsInTeacher++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if ((matchesTeacher && !currentUtsTargetClass && !currentUtsTargetGrade) || visibleExamsInTeacher > 0) {
                block.classList.remove('hidden');
                totalVisibleTeachers++;
            } else {
                block.classList.add('hidden');
            }
        });

        const noMatchUts = document.getElementById('noMatchUtsMessage');
        if (noMatchUts) {
            noMatchUts.classList.toggle('hidden', totalVisibleTeachers > 0);
        }
    }

    // 8. Event Listeners Initialization
    document.addEventListener('DOMContentLoaded', function() {
        const sUas = document.getElementById('searchUasInput');
        const sUts = document.getElementById('searchUtsInput');
        if (sUas) sUas.addEventListener('input', runUasFilter);
        if (sUts) sUts.addEventListener('input', runUtsFilter);

        // Close dropdowns outside
        document.addEventListener('click', function(e) {
            const dropdowns = [
                { container: 'utsClassFilterDropdown', close: () => toggleUtsClassFilterMenu(true) },
                { container: 'utsStatusFilterDropdown', close: () => toggleUtsStatusFilterMenu(true) },
                { container: 'uasStatusFilterDropdown', close: () => toggleUasStatusFilterMenu(true) },
                { container: 'uasGradeFilterDropdown', close: () => toggleUasGradeFilterMenu(true) }
            ];
            dropdowns.forEach(d => {
                const el = document.getElementById(d.container);
                if (el && !el.contains(e.target)) d.close();
            });
        });

        // Bulk delete handlers
        const bulkBar = document.getElementById('bulkBar');
        const bulkCount = document.getElementById('bulkCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkCancelBtn = document.getElementById('bulkCancelBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function updateBulk() {
            const checked = document.querySelectorAll('.bulk-checkbox:checked');
            if (bulkCount) bulkCount.textContent = checked.length;
            if (bulkBar) {
                bulkBar.classList.toggle('hidden', checked.length === 0);
                bulkBar.classList.toggle('flex', checked.length > 0);
            }
        }

        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.addEventListener('change', updateBulk));
        if (bulkCancelBtn) {
            bulkCancelBtn.addEventListener('click', function() {
                document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
                updateBulk();
            });
        }

        if (bulkDeleteBtn && bulkDeleteForm) {
            bulkDeleteBtn.addEventListener('click', function() {
                const checked = document.querySelectorAll('.bulk-checkbox:checked');
                if (checked.length === 0) return;
                if (!confirm(`Hapus ${checked.length} jadwal ujian yang dipilih?`)) return;

                bulkDeleteForm.querySelectorAll('input[name="exam_ids[]"]').forEach(el => el.remove());
                const seen = new Set();
                checked.forEach(cb => {
                    if (!seen.has(cb.value)) {
                        seen.add(cb.value);
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'exam_ids[]';
                        input.value = cb.value;
                        bulkDeleteForm.appendChild(input);
                    }
                });
                bulkDeleteForm.submit();
            });
        }

        // Restore category selection from query param or localStorage
        const urlParams = new URLSearchParams(window.location.search);
        const modeFromUrl = urlParams.get('mode') || urlParams.get('category');
        const savedMode = modeFromUrl || '{{ $initialMode }}' || 'uas';
        selectExamCategory(savedMode);
    });

    // File Upload Handlers
    function handleFileSelected(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const nameEl = document.getElementById('preview-file-name');
        const sizeEl = document.getElementById('preview-file-size');
        const emptyDropzone = document.getElementById('dropzone-empty');
        const previewDropzone = document.getElementById('dropzone-file-preview');

        if (nameEl) nameEl.textContent = file.name;
        if (sizeEl) {
            let sizeStr = '';
            if (file.size < 1024) sizeStr = file.size + ' B';
            else if (file.size < 1024 * 1024) sizeStr = (file.size / 1024).toFixed(1) + ' KB';
            else sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            sizeEl.textContent = sizeStr;
        }

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
