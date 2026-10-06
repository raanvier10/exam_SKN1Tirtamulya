@extends('layouts.admin')
@section('title', 'Manajemen Ujian')

@section('content')
<!-- Header Page -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Ujian</h2>
        </div>

    </div>
    <div class="grid grid-cols-2 sm:flex items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
        <button onclick="importModal.showModal()" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-indigo-600 shadow-xs transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
        <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
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
            <button type="button" onclick="importModal.close()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-xs">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-colors">Upload Data</button>
        </div>
    </form>
</dialog>

<!-- ========================================================================= -->
<!-- 1. DUA PILIHAN UTAMA: UAS (KATEGORI KELAS) VS UTS (GURU PENGAMPU)          -->
<!-- ========================================================================= -->
<div class="relative flex flex-col items-center justify-center my-6 sm:my-8">
    <div class="relative flex items-center justify-center w-full max-w-2xl px-4">
        <!-- Decorative Background Accent Line -->
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200/80"></div>
        </div>

        <!-- Floating Segmented Control with Ambient Glow -->
        <div class="relative z-10 group">
            <!-- Ambient Glow behind pill -->
            <div class="absolute -inset-1 bg-gradient-to-r from-sky-400/20 via-indigo-400/20 to-sky-400/20 rounded-full blur-md opacity-60 group-hover:opacity-100 transition duration-500"></div>

            <!-- Outer Pill Capsule -->
            <div class="relative inline-flex items-center p-1.5 bg-[#f0f2f5] rounded-full border border-slate-200/80 shadow-[0_2px_12px_-2px_rgba(15,23,42,0.06)] ring-4 ring-white/90 select-none" role="tablist">
                <!-- Tab Pilihan 1: Mode UAS -->
                <button type="button"
                        onclick="selectExamCategory('uas')" 
                        onkeydown="if(event.key==='Enter'||event.key===' '){selectExamCategory('uas');event.preventDefault();}"
                        role="tab"
                        aria-selected="true"
                        id="cardCategoryUas"
                        class="relative inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-2 sm:py-2.5 rounded-full text-sm sm:text-[15px] font-bold text-[#0099ff] bg-white shadow-[0_2px_10px_rgba(0,153,255,0.16)] transition-all duration-200 cursor-pointer select-none">
                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.825-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                    </svg>
                    <span>UAS</span>
                    <span id="badgeCountUas" class="px-2 py-0.5 text-[11px] font-extrabold rounded-full bg-sky-50 text-[#0099ff] border border-sky-200/60 shadow-2xs transition-colors">
                        {{ $uasExams->count() }}
                    </span>
                </button>

                <!-- Tab Pilihan 2: Mode UTS -->
                <button type="button"
                        onclick="selectExamCategory('uts')" 
                        onkeydown="if(event.key==='Enter'||event.key===' '){selectExamCategory('uts');event.preventDefault();}"
                        role="tab"
                        aria-selected="false"
                        id="cardCategoryUts"
                        class="relative inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-2 sm:py-2.5 rounded-full text-sm sm:text-[15px] font-medium text-slate-500 hover:text-slate-800 bg-transparent hover:bg-slate-200/40 transition-all duration-200 cursor-pointer select-none">
                    <svg class="w-4 h-4 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>UTS</span>
                    <span id="badgeCountUts" class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-200/70 text-slate-600 transition-colors">
                        {{ $utsExams->count() }}
                    </span>
                </button>
            </div>
        </div>
    </div>
    <!-- Hidden indicator elements for legacy references -->
    <div id="checkUas" class="hidden"></div>
    <div id="checkUts" class="hidden"></div>
</div>


<!-- ========================================================================= -->
<!-- 2. QUICK STATS BAR (DINAMIS SESUAI MODE UAS VS UTS)                       -->
<!-- ========================================================================= -->
@php
    $uasOngoing = $uasExams->where('is_ongoing', true)->count();
    $uasActive = $uasExams->where('has_active', true)->count();
    $uasClassesCount = $uasClassGroups->count();

    $utsOngoing = $utsExams->where('is_ongoing', true)->count();
    $utsActive = $utsExams->where('has_active', true)->count();
    $utsTeachersCount = $utsTeacherGroups->count();
@endphp

<!-- Stats UAS -->
<div id="statsUasBar" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-5">
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Ruang Kelas </div>
            <div class="text-base font-bold text-slate-900 tracking-tight">{{ $uasClassesCount }} Kelas</div>
        </div>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Total Soal</div>
            <div class="text-base font-bold text-slate-900 tracking-tight">{{ $uasExams->count() }} Ujian</div>
        </div>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Sedang Berlangsung</div>
            <div class="text-base font-bold text-amber-600 tracking-tight">{{ $uasOngoing }} Sesi</div>
        </div>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Jadwal Aktif</div>
            <div class="text-base font-bold text-emerald-600 tracking-tight">{{ $uasActive }} Ujian</div>
        </div>
    </div>
</div>

<!-- Stats UTS -->
<div id="statsUtsBar" class="hidden grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mb-5">
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Guru Pengampu</div>
            <div class="text-base font-bold text-slate-900 tracking-tight">{{ $utsTeachersCount }} Orang</div>
        </div>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Total Soal</div>
            <div class="text-base font-bold text-slate-900 tracking-tight">{{ $utsExams->count() }} Ujian</div>
        </div>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Sedang Berlangsung</div>
            <div class="text-base font-bold text-amber-600 tracking-tight">{{ $utsOngoing }} Sesi</div>
        </div>
    </div>
    <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="text-[11px] font-medium text-slate-500">Jadwal Aktif</div>
            <div class="text-base font-bold text-emerald-600 tracking-tight">{{ $utsActive }} Ujian</div>
        </div>
    </div>
</div>

<!-- Bulk Delete Floating Bar -->
<div id="bulkBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white rounded-2xl shadow-2xl px-5 py-3 flex items-center gap-4 transition-all">
    <span class="text-sm font-semibold"><span id="bulkCount">0</span> sesi dipilih</span>
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

<!-- ========================================================================= -->
<!-- 3. KONTEN MODE UAS: DIUTAMAKAN KATEGORI RUANG KELAS                       -->
<!-- ========================================================================= -->
<div id="uasContainer" class="space-y-4">
    <!-- Bar Filter Khusus UAS: Filter Jenjang & Pills Kelas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-3.5 space-y-3">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <!-- Search Input UAS -->
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="searchUasInput" 
                    placeholder="Cari ruang kelas" 
                    class="w-full pl-10 pr-9 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                >
            </div>

            <!-- Filter Status & Filter Jenjang -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Status -->
                <div class="relative">
                    <select id="filterUasStatus" class="appearance-none pl-3 pr-8 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Semua Status</option>
                        <option value="ongoing">Sedang Berlangsung</option>
                        <option value="active">Aktif</option>
                        <option value="finished">Selesai</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>

                <!-- Filter Kelas / Tingkat (Dropdown seperti Semua Status) -->
                <div class="relative">
                    <select id="filterUasGrade" onchange="filterUasGrade(this.value)" class="appearance-none pl-3 pr-8 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Semua Kelas</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DAFTAR BLOK KELAS UAS (KATEGORI KELAS) -->
    <div class="space-y-4" id="uasClassCardList">
        @forelse($uasClassGroups as $cg)
        <div class="uas-class-card bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all duration-200 overflow-hidden"
             data-class-id="{{ $cg->id }}"
             data-class-name="{{ strtolower($cg->name) }}"
             data-grade="{{ $cg->grade }}">
            
            <!-- Header Blok Kelas -->
            <div class="p-3.5 sm:p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-xs shrink-0">
                        {{ substr($cg->grade, 0, 3) ?: 'KL' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                Ruang Kelas {{ $cg->name }}
                            </h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Tingkat {{ $cg->grade }}
                            </span>
                            @if($cg->ongoing_exams > 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ $cg->ongoing_exams }} Sedang Berlangsung
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Pengawasan UAS Ruang • <span class="font-semibold text-slate-700">{{ $cg->total_exams }} Jadwal Soal</span>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-slate-400 hidden sm:block">
                    Pilih soal di bawah dan klik <strong>Pantau Siswa</strong>
                </div>
            </div>

            <!-- List Ujian untuk Kelas ini -->
            <div class="p-3.5 sm:p-4 space-y-2.5">
                @foreach($cg->exams as $item)
                @php
                    $primary = $item->target_session ?? $item->primary;
                    $canManage = auth()->user()->isAdmin() || ($primary->created_by === auth()->id());
                    $isExpired = $item->is_expired ?? $primary->isExpired();
                    $isOngoing = $item->is_ongoing ?? $primary->isOngoing();
                    $status = $isOngoing ? 'ongoing' : ($isExpired ? 'finished' : ($item->has_active ? 'active' : 'inactive'));
                @endphp

                <div class="uas-exam-row bg-white rounded-xl border border-slate-200/90 p-3.5 sm:p-4 hover:border-indigo-300 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3"
                     data-exam-title="{{ strtolower($item->title) }}"
                     data-exam-status="{{ $status }}">
                    
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        @if($canManage)
                            @if($isOngoing)
                                <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed mt-1 shrink-0 opacity-40">
                            @else
                                <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-1 shrink-0" value="{{ $primary->id }}">
                            @endif
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h5 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-snug">
                                    {{ $item->title }}
                                </h5>

                                <!-- Status Badge -->
                                @if($isOngoing)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                        Sedang Berlangsung
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        Selesai
                                    </span>
                                @elseif($item->has_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif

                                @if($item->session_count > 1)
                                    <span class="px-2 py-0.2 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $item->session_count }} Sesi Terjadwal
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 sm:gap-3 text-xs text-slate-500 mt-1.5 flex-wrap">
                                <!-- Jadwal Tanggal & Jam -->
                                <div class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }}</span>
                                    <span class="font-mono text-slate-600 font-medium">({{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB)</span>
                                </div>

                                <span class="text-slate-300">•</span>

                                <!-- Durasi & Pelanggaran -->
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="font-bold text-slate-700">{{ $primary->duration }} Mnt</span>
                                    <span class="text-slate-400">({{ round($primary->duration / 35, 1) }} JP)</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                        Max {{ $primary->max_violation }}x Keluar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Pengawas -->
                    <div class="flex items-center justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 shrink-0">
                        @if($item->session_count > 1)
                            <button type="button" 
                                    onclick="const d = document.getElementById('drawer-sessions-{{ $item->group_id }}'); if(d) d.classList.toggle('hidden');" 
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                <span>Buka {{ $item->session_count }} Sesi</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @endif

                        <!-- Tombol Utama: Pantau Siswa -->
                        <a href="{{ route('admin.exams.show', $primary->id) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-indigo-500/20 transition-all active:scale-[0.98]"
                           title="Masuk Ruang Pengawasan & Buka Kunci Siswa">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Pantau Siswa</span>
                        </a>

                        @if($canManage)
                            <a href="{{ route('admin.exams.edit', $primary->id) }}" class="inline-flex items-center justify-center px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition-all">
                                Edit
                            </a>
                            @if(!$isOngoing)
                                <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini? Data sesi ujian akan dihapus.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center px-3 py-2 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition-all">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>

                    @if($item->session_count > 1)
                    <!-- Drawer Sesi UAS -->
                    <div id="drawer-sessions-{{ $item->group_id }}" class="hidden w-full pt-3 pb-1 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span>Daftar {{ $item->session_count }} Sesi Terjadwal:</span>
                            <span class="text-[11px] text-slate-400">Pilih sesi untuk dipantau secara terpisah</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($item->sessions as $idx => $s)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                                <div>
                                    <div class="font-bold text-slate-800">Sesi {{ $idx + 1 }} • {{ \Carbon\Carbon::parse($s->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_at)->format('H:i') }} WIB</div>
                                    <div class="text-slate-500 text-[11px] mt-0.5">Kelas: {{ $s->classes->pluck('name')->implode(', ') ?: 'Semua Kelas' }}</div>
                                </div>
                                <a href="{{ route('admin.exams.show', $s->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shadow-2xs">
                                    Pantau
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="py-12 text-center bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h5 class="text-base font-bold text-slate-800">Belum Ada Jadwal Ujian UAS</h5>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Klik tombol "Tambah Ujian" di atas untuk menambahkan jadwal ujian sekolah (UAS).</p>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                + Buat Jadwal UAS Baru
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pesan jika pencarian UAS nihil -->
    <div id="noMatchUasMessage" class="hidden py-12 text-center bg-white rounded-2xl border border-slate-200 p-6">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <h5 class="text-sm font-bold text-slate-800">Ruang Kelas atau Ujian UAS Tidak Ditemukan</h5>
        <p class="text-xs text-slate-500 mt-1">Sesuaikan kata kunci pencarian, filter kelas, atau filter status.</p>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. KONTEN MODE UTS: DIUTAMAKAN NAMA GURU PENGAMPU                         -->
<!-- ========================================================================= -->
<div id="utsContainer" class="hidden space-y-4">
    <!-- Bar Kontrol UTS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-3.5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Search Input UTS -->
        <div class="relative w-full md:w-96">
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

        <div class="flex items-center gap-2.5">
            <!-- Filter Status UTS -->
            <div class="relative">
                <select id="filterUtsStatus" class="appearance-none pl-3 pr-8 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <option value="">Semua Status</option>
                    <option value="ongoing">⚡ Sedang Berlangsung</option>
                    <option value="active">✓ Aktif</option>
                    <option value="finished">Selesai</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>

            <!-- Tombol Expand / Collapse All Teachers -->
            <button id="expandAllUtsTeachers" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl border border-slate-200/80 text-xs font-semibold transition-all shadow-xs" title="Buka / Tutup Semua Guru">
                <span id="expandAllUtsText">Buka Semua Guru</span>
            </button>
        </div>
    </div>

    <!-- DAFTAR GURU PENGAMPU UTS -->
    <div class="space-y-4" id="utsTeacherAccordionList">
        @forelse($utsTeacherGroups as $tIdx => $t)
        @php
            $totalX = $t->grades['X']->exams->count();
            $totalXI = $t->grades['XI']->exams->count();
            $totalXII = $t->grades['XII']->exams->count();
            $totalUmum = $t->grades['UMUM']->exams->count();
            
            $defaultActiveGrade = 'X';
            if ($totalX > 0) $defaultActiveGrade = 'X';
            elseif ($totalXI > 0) $defaultActiveGrade = 'XI';
            elseif ($totalXII > 0) $defaultActiveGrade = 'XII';
            elseif ($totalUmum > 0) $defaultActiveGrade = 'UMUM';
        @endphp

        <!-- Card Guru UTS -->
        <div class="uts-teacher-block bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all duration-200 overflow-hidden"
             data-teacher-name="{{ strtolower($t->name) }}"
             data-teacher-id="{{ $t->id }}">
            
            <!-- Accordion Header: Klik Nama Guru -->
            <div class="p-3.5 sm:p-4 flex items-center justify-between gap-3 cursor-pointer select-none hover:bg-slate-50/80 transition-colors"
                 onclick="toggleUtsTeacherAccordion('{{ $t->id }}')">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $t->is_current ? 'bg-emerald-600 text-white shadow-xs' : ($t->role === 'admin' ? 'bg-slate-900 text-white' : 'bg-emerald-50 border border-emerald-100 text-emerald-700') }} flex items-center justify-center font-bold text-sm shrink-0">
                        {{ $t->initials }}
                    </div>
                    
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base sm:text-lg font-bold text-slate-900 truncate tracking-tight">
                                {{ $t->name }}
                            </h4>
                            @if($t->is_current)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Akun Anda
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
                            <span>•</span>
                            <span class="font-semibold text-slate-700">{{ $t->total_exams }} Ujian Diupload</span>
                            @if($t->ongoing_exams > 0)
                                <span>•</span>
                                <span class="inline-flex items-center gap-1 font-bold text-amber-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    {{ $t->ongoing_exams }} Sedang Berlangsung
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Bar Pilihan Kelas -->
                <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                    <div class="inline-flex items-center p-0.5 sm:p-1 bg-slate-100/90 rounded-xl border border-slate-200/80 gap-0.5 shadow-2xs" onclick="event.stopPropagation()">
                        <!-- Tombol Kelas X -->
                        <button type="button" 
                                onclick="selectUtsTeacherGrade('{{ $t->id }}', 'X')"
                                id="bar-btn-{{ $t->id }}-X"
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'X' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}"
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
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'XI' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}"
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
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'XII' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}"
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
                                class="grade-choice-btn-{{ $t->id }} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $defaultActiveGrade === 'UMUM' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            <span>Umum</span>
                            <span class="ml-0.5 px-1 py-0.2 rounded text-[10px] {{ $defaultActiveGrade === 'UMUM' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800 font-extrabold' }}">
                                {{ $totalUmum }}
                            </span>
                        </button>
                        @endif
                    </div>

                    <button type="button" class="w-8 h-8 rounded-xl border border-slate-200/80 bg-white flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                        <svg id="chevron-uts-{{ $t->id }}" class="w-4 h-4 transition-transform duration-200 transform {{ ($t->is_current || $tIdx === 0) ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body Guru UTS -->
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
                        @endphp

                        <div class="uts-exam-row bg-white rounded-xl border border-slate-200/90 p-3.5 sm:p-4 hover:border-emerald-300 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3"
                             data-exam-title="{{ strtolower($grp->title) }}"
                             data-exam-classes="{{ strtolower($classList ?: 'semua kelas') }}"
                             data-exam-status="{{ $status }}">
                            
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                @if($canManage)
                                    @if($isOngoing)
                                        <input type="checkbox" disabled class="rounded text-slate-300 border-slate-200 w-4 h-4 cursor-not-allowed mt-1 shrink-0 opacity-40">
                                    @else
                                        <input type="checkbox" class="bulk-checkbox rounded text-emerald-600 focus:ring-emerald-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-1 shrink-0" value="{{ $primary->id }}">
                                    @endif
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h5 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-snug">
                                            {{ $grp->title }}
                                        </h5>

                                        @if($isOngoing)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span> Sedang Berlangsung
                                            </span>
                                        @elseif($isExpired)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Selesai</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 sm:gap-3 text-xs text-slate-500 mt-1.5 flex-wrap">
                                        <div class="inline-flex items-center gap-1">
                                            <span class="font-semibold text-slate-700">Kelas:</span>
                                            <span class="px-2 py-0.5 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px]">
                                                {{ $classList ?: 'Semua Kelas' }}
                                            </span>
                                        </div>
                                        <span class="text-slate-300">•</span>
                                        <div class="inline-flex items-center gap-1">
                                            <span>{{ \Carbon\Carbon::parse($primary->start_at)->format('d M Y') }}</span>
                                            <span class="font-mono text-slate-600">({{ \Carbon\Carbon::parse($primary->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($primary->end_at)->format('H:i') }} WIB)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 shrink-0">
                                @if($grp->session_count > 1)
                                    <button type="button" 
                                            onclick="const d = document.getElementById('drawer-sessions-uts-{{ $grp->group_id }}'); if(d) d.classList.toggle('hidden');" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                        <span>Buka {{ $grp->session_count }} Sesi</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                @endif

                                <a href="{{ route('admin.exams.show', $primary->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Pantau Siswa</span>
                                </a>

                                @if($canManage)
                                    <a href="{{ route('admin.exams.edit', $primary->id) }}" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition-all">Edit</a>
                                    @if(!$isOngoing)
                                        <form action="{{ route('admin.exams.destroy', $primary->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-600 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition-all">Hapus</button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            @if($grp->session_count > 1)
                            <!-- Drawer Sesi UTS -->
                            <div id="drawer-sessions-uts-{{ $grp->group_id }}" class="hidden w-full pt-3 pb-1 border-t border-slate-100 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                    <span>Daftar {{ $grp->session_count }} Sesi Terjadwal:</span>
                                    <span class="text-[11px] text-slate-400">Pilih sesi untuk dipantau secara terpisah</span>
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
<!-- 5. JAVASCRIPT LOGIC (UAS KELAS VS UTS GURU, FILTER, ACCORDION, STORAGE)    -->
<!-- ========================================================================= -->
<script>
    let currentUasGrade = '';
    let currentUasClassId = '';

    // 1. Pemilihan Mode Utama (UAS vs UTS)
    function selectExamCategory(category) {
        try {
            localStorage.setItem('admin_exam_category', category);
        } catch (_) {}

        const cardUas = document.getElementById('cardCategoryUas');
        const cardUts = document.getElementById('cardCategoryUts');
        const bUas = document.getElementById('badgeCountUas');
        const bUts = document.getElementById('badgeCountUts');
        const checkUas = document.getElementById('checkUas');
        const checkUts = document.getElementById('checkUts');

        const uasContainer = document.getElementById('uasContainer');
        const utsContainer = document.getElementById('utsContainer');
        const statsUasBar = document.getElementById('statsUasBar');
        const statsUtsBar = document.getElementById('statsUtsBar');

        const activeClasses = 'relative inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-2 sm:py-2.5 rounded-full text-sm sm:text-[15px] font-bold text-[#0099ff] bg-white shadow-[0_2px_10px_rgba(0,153,255,0.16)] transition-all duration-200 cursor-pointer select-none';
        const inactiveClasses = 'relative inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-2 sm:py-2.5 rounded-full text-sm sm:text-[15px] font-medium text-slate-500 hover:text-slate-800 bg-transparent hover:bg-slate-200/40 transition-all duration-200 cursor-pointer select-none';

        const activeBadgeClasses = 'px-2 py-0.5 text-[11px] font-extrabold rounded-full bg-sky-50 text-[#0099ff] border border-sky-200/60 shadow-2xs transition-colors';
        const inactiveBadgeClasses = 'px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-200/70 text-slate-600 transition-colors';

        if (category === 'uas') {
            if (cardUas) {
                cardUas.className = activeClasses;
                cardUas.setAttribute('aria-selected', 'true');
            }
            if (cardUts) {
                cardUts.className = inactiveClasses;
                cardUts.setAttribute('aria-selected', 'false');
            }
            if (bUas) bUas.className = activeBadgeClasses;
            if (bUts) bUts.className = inactiveBadgeClasses;

            // Tampilkan Konten UAS, Sembunyikan UTS
            if (uasContainer) uasContainer.classList.remove('hidden');
            if (utsContainer) utsContainer.classList.add('hidden');
            if (statsUasBar) statsUasBar.classList.remove('hidden');
            if (statsUtsBar) statsUtsBar.classList.add('hidden');

            runUasFilter();
        } else {
            if (cardUts) {
                cardUts.className = activeClasses;
                cardUts.setAttribute('aria-selected', 'true');
            }
            if (cardUas) {
                cardUas.className = inactiveClasses;
                cardUas.setAttribute('aria-selected', 'false');
            }
            if (bUts) bUts.className = activeBadgeClasses;
            if (bUas) bUas.className = inactiveBadgeClasses;

            // Tampilkan Konten UTS, Sembunyikan UAS
            if (uasContainer) uasContainer.classList.add('hidden');
            if (utsContainer) utsContainer.classList.remove('hidden');
            if (statsUasBar) statsUasBar.classList.add('hidden');
            if (statsUtsBar) statsUtsBar.classList.remove('hidden');

            runUtsFilter();
        }
    }

    // 2. Filter Tingkat di Mode UAS (Dropdown: Semua Kelas, Kelas X, Kelas XI, Kelas XII)
    function filterUasGrade(grade) {
        currentUasGrade = grade || '';

        const selectEl = document.getElementById('filterUasGrade');
        if (selectEl && selectEl.value !== currentUasGrade) {
            selectEl.value = currentUasGrade;
        }

        runUasFilter();
    }

    // 3. Filter Pill Ruang Kelas Tertentu di Mode UAS
    function filterUasSpecificClass(classId) {
        currentUasClassId = classId || '';

        document.querySelectorAll('.uas-class-pill').forEach(btn => {
            const bId = btn.getAttribute('data-class-id');
            if (bId === classId) {
                btn.className = 'uas-class-pill px-3 py-1 rounded-lg font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0 transition-all shadow-2xs';
            } else {
                btn.className = 'uas-class-pill px-3 py-1 rounded-lg font-medium text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 shrink-0 transition-all';
            }
        });
        runUasFilter();
    }

    // 4. Runner Filter UAS (Live Search + Status + Jenjang + Kelas)
    function runUasFilter() {
        const query = (document.getElementById('searchUasInput')?.value || '').toLowerCase().trim();
        const statusVal = (document.getElementById('filterUasStatus')?.value || '').toLowerCase().trim();
        let totalVisibleClass = 0;

        document.querySelectorAll('.uas-class-card').forEach(card => {
            const classId = card.getAttribute('data-class-id') || '';
            const className = card.getAttribute('data-class-name') || '';
            const grade = card.getAttribute('data-grade') || '';

            const matchesGrade = !currentUasGrade || grade === currentUasGrade;
            const matchesClass = !currentUasClassId || classId === currentUasClassId;

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

            if (matchesGrade && matchesClass && visibleExamsInClass > 0) {
                card.classList.remove('hidden');
                totalVisibleClass++;
            } else {
                card.classList.add('hidden');
            }
        });

        const noMatchUas = document.getElementById('noMatchUasMessage');
        if (noMatchUas) {
            noMatchUas.classList.toggle('hidden', totalVisibleClass > 0);
        }
    }

    // 5. Runner Filter UTS (Live Search + Status)
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

                const matchesQuery = !query || title.includes(query) || classes.includes(query) || teacherName.includes(query);
                const matchesStatus = !statusVal || status === statusVal;

                if (matchesQuery && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleExamsInTeacher++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (matchesTeacher || visibleExamsInTeacher > 0) {
                block.classList.remove('hidden');
                totalVisibleTeachers++;

                if (query.length > 1) {
                    const bodyEl = document.getElementById('body-uts-' + teacherId);
                    const chevronEl = document.getElementById('chevron-uts-' + teacherId);
                    if (bodyEl) bodyEl.classList.remove('hidden');
                    if (chevronEl) chevronEl.classList.add('rotate-180');
                }
            } else {
                block.classList.add('hidden');
            }
        });

        const noMatchUts = document.getElementById('noMatchUtsMessage');
        if (noMatchUts) {
            noMatchUts.classList.toggle('hidden', totalVisibleTeachers > 0);
        }
    }

    // 6. Accordion UTS Guru
    function toggleUtsTeacherAccordion(teacherId) {
        const bodyEl = document.getElementById('body-uts-' + teacherId);
        const chevronEl = document.getElementById('chevron-uts-' + teacherId);
        if (!bodyEl) return;

        if (bodyEl.classList.contains('hidden')) {
            bodyEl.classList.remove('hidden');
            if (chevronEl) chevronEl.classList.add('rotate-180');
        } else {
            bodyEl.classList.add('hidden');
            if (chevronEl) chevronEl.classList.remove('rotate-180');
        }
    }

    function selectUtsTeacherGrade(teacherId, targetGrade) {
        const bodyEl = document.getElementById('body-uts-' + teacherId);
        const chevronEl = document.getElementById('chevron-uts-' + teacherId);
        if (bodyEl && bodyEl.classList.contains('hidden')) {
            bodyEl.classList.remove('hidden');
            if (chevronEl) chevronEl.classList.add('rotate-180');
        }

        const grades = ['X', 'XI', 'XII', 'UMUM'];
        grades.forEach(g => {
            const btn = document.getElementById('bar-btn-' + teacherId + '-' + g);
            const pane = document.getElementById('pane-uts-' + teacherId + '-' + g);

            if (pane) {
                pane.classList.toggle('hidden', g !== targetGrade);
                pane.classList.toggle('block', g === targetGrade);
            }

            if (btn) {
                const countBadge = btn.querySelector('span:last-child');
                if (g === targetGrade) {
                    btn.className = `grade-choice-btn-${teacherId} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all bg-emerald-600 text-white shadow-xs`;
                    if (countBadge) countBadge.className = 'ml-0.5 px-1 py-0.2 rounded text-[10px] bg-white/20 text-white';
                } else {
                    btn.className = `grade-choice-btn-${teacherId} px-2 sm:px-2.5 py-1 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900 hover:bg-white/80`;
                    if (countBadge) {
                        const cnt = parseInt(countBadge.textContent.trim()) || 0;
                        countBadge.className = `ml-0.5 px-1 py-0.2 rounded text-[10px] ${cnt > 0 ? 'bg-emerald-100 text-emerald-700 font-extrabold' : 'bg-slate-200/60 text-slate-400'}`;
                    }
                }
            }
        });
    }

    // 7. Inisialisasi saat DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        // Setup Search & Filter Listeners
        const sUas = document.getElementById('searchUasInput');
        const fUasStatus = document.getElementById('filterUasStatus');
        const fUasGrade = document.getElementById('filterUasGrade');
        if (sUas) sUas.addEventListener('input', runUasFilter);
        if (fUasStatus) fUasStatus.addEventListener('change', runUasFilter);
        if (fUasGrade) fUasGrade.addEventListener('change', function() { filterUasGrade(this.value); });

        const sUts = document.getElementById('searchUtsInput');
        const fUtsStatus = document.getElementById('filterUtsStatus');
        if (sUts) sUts.addEventListener('input', runUtsFilter);
        if (fUtsStatus) fUtsStatus.addEventListener('change', runUtsFilter);

        // Expand / Collapse UTS Teachers
        const expandBtn = document.getElementById('expandAllUtsTeachers');
        const expandText = document.getElementById('expandAllUtsText');
        let isUtsAllExpanded = false;
        if (expandBtn) {
            expandBtn.addEventListener('click', function() {
                isUtsAllExpanded = !isUtsAllExpanded;
                document.querySelectorAll('.uts-teacher-block').forEach(b => {
                    const tId = b.getAttribute('data-teacher-id');
                    const body = document.getElementById('body-uts-' + tId);
                    const chev = document.getElementById('chevron-uts-' + tId);
                    if (body) body.classList.toggle('hidden', !isUtsAllExpanded);
                    if (chev) chev.classList.toggle('rotate-180', isUtsAllExpanded);
                });
                if (expandText) expandText.textContent = isUtsAllExpanded ? 'Tutup Semua Guru' : 'Buka Semua Guru';
            });
        }

        // Bulk Delete Selection Handler
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

        // Setup Category Switcher Listeners
        const cUas = document.getElementById('cardCategoryUas');
        const cUts = document.getElementById('cardCategoryUts');
        if (cUas) cUas.addEventListener('click', () => selectExamCategory('uas'));
        if (cUts) cUts.addEventListener('click', () => selectExamCategory('uts'));

        // Restore Mode Pilihan (Default: 'uas')
        const urlParams = new URLSearchParams(window.location.search);
        const modeFromUrl = urlParams.get('mode') || urlParams.get('category');
        const savedMode = modeFromUrl || localStorage.getItem('admin_exam_category') || 'uas';
        selectExamCategory(savedMode);
    });

    // 8. File Upload Preview Handlers
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
