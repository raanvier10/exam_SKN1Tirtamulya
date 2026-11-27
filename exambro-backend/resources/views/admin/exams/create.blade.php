@extends('layouts.admin')
@section('title', 'Tambah Ujian')

@section('content')
<style>
    .slot-class-grid[data-active-grade="10"] .slot-class-card:not([data-grade="10"]) { display: none !important; }
    .slot-class-grid[data-active-grade="11"] .slot-class-card:not([data-grade="11"]) { display: none !important; }
    .slot-class-grid[data-active-grade="12"] .slot-class-card:not([data-grade="12"]) { display: none !important; }
</style>
@php
    $lockedType = Auth::user()->isTeacher() ? 'uts' : (in_array(request('type'), ['uts', 'uas'], true) ? request('type') : null);
    $activeType = old('type', $lockedType ?? 'uas');
    $backUrl = route('admin.exams.index', $lockedType ? ['mode' => $lockedType] : []);
@endphp
<div class="w-full pb-12">
    <!-- Header -->
    <div class="mb-[22px] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Buat Jadwal Ujian {{ $lockedType ? strtoupper($lockedType) : 'Baru' }}</h2>
                <!-- Badge Status Autosave -->
                <div id="autosaveStatusBadge" class="hidden items-center gap-1.5 px-2.5 py-0.5 rounded-[6px] text-[11px] font-medium transition-all">
                    <span id="autosaveStatusDot" class="w-2 h-2 rounded-full"></span>
                    <span id="autosaveStatusText">Draft tersimpan</span>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">Lengkapi informasi soal, pembagian sesi kelas, dan aturan keamanan ujian.</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
            <button type="button" id="clearDraftBtn" onclick="clearExamDraft(true)" class="hidden items-center gap-[6px] px-3 py-2 rounded-[8px] text-xs font-medium text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-all shadow-2xs cursor-pointer" title="Hapus draf yang tersimpan dan kosongkan formulir">
                <svg class="w-[14px] h-[14px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                <span>Reset Draf</span>
            </button>
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-[6px] px-3.5 py-2 rounded-[8px] text-xs sm:text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:border-[#142C5C] hover:text-[#142C5C] transition-all shadow-2xs whitespace-nowrap">
                <svg class="w-[15px] h-[15px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Banner Pemulihan Draf Autosave -->
    <div id="draftRestoredBanner" class="hidden mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-[#142C5C] flex items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-[8px] bg-[#142C5C] text-white flex items-center justify-center shrink-0">
                <i data-lucide="check" class="w-4 h-4"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-[#142C5C]">Draf Ujian Dipulihkan Otomatis</div>
                <div class="text-[11px] text-slate-600" id="draftRestoredTimeText">Data input dan jadwal sesi terakhir Anda berhasil dipulihkan dari penyimpanan browser.</div>
            </div>
        </div>
        <button type="button" onclick="dismissDraftBanner()" class="text-xs font-semibold px-2.5 py-1 text-[#142C5C] bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shrink-0 cursor-pointer">
            Tutup
        </button>
    </div>

    <!-- STEPPER PROGRESS BAR -->
    <div class="mb-6 bg-white p-2.5 sm:p-3 rounded-[12px] border border-slate-200 shadow-2xs">
        <div class="grid grid-cols-3 gap-2 sm:gap-3 relative">
            <!-- Step 1 Button -->
            <button type="button" onclick="handleStepTabClick(1)" id="step-tab-1" class="step-tab flex items-center gap-2 sm:gap-3 p-2.5 rounded-[8px] transition-all text-left bg-slate-50 text-[#142C5C] border border-slate-200 shadow-2xs cursor-pointer">
                <div class="step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-[#142C5C] text-white flex items-center justify-center shrink-0 transition-all">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="step-sub text-[10px] font-semibold text-[#142C5C] uppercase tracking-wider block">Langkah 1</span>
                    <span class="step-label text-xs sm:text-sm font-bold text-[#142C5C] truncate block">Info &amp; Soal</span>
                </div>
            </button>

            <!-- Step 2 Button -->
            <button type="button" onclick="handleStepTabClick(2)" id="step-tab-2" class="step-tab flex items-center gap-2 sm:gap-3 p-2.5 rounded-[8px] transition-all text-left bg-slate-50/70 text-slate-500 hover:bg-slate-100/80 border border-transparent cursor-pointer">
                <div class="step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 transition-all">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="step-sub text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Langkah 2</span>
                    <span class="step-label text-xs sm:text-sm font-medium text-slate-600 truncate block">Jadwal &amp; Kelas</span>
                </div>
            </button>

            <!-- Step 3 Button -->
            <button type="button" onclick="handleStepTabClick(3)" id="step-tab-3" class="step-tab flex items-center gap-2 sm:gap-3 p-2.5 rounded-[8px] transition-all text-left bg-slate-50/70 text-slate-500 hover:bg-slate-100/80 border border-transparent cursor-pointer">
                <div class="step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 transition-all">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="step-sub text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Langkah 3</span>
                    <span class="step-label text-xs sm:text-sm font-medium text-slate-600 truncate block">Review &amp; Terbit</span>
                </div>
            </button>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm">
        @csrf

        <!-- ============================================================= -->
        <!-- LANGKAH 1: INFORMASI UJIAN & LINK SOAL                        -->
        <!-- ============================================================= -->
        <div id="step-panel-1" class="step-panel space-y-5">
            <div class="card overflow-hidden">
                <div class="bg-[#142C5C] px-6 py-4 text-white">
                    <h3 class="text-sm sm:text-base font-bold tracking-tight">Informasi Ujian &amp; Kuesioner Soal</h3>
                    <p class="text-xs text-blue-200 mt-0.5">Tentukan nama ujian dan cantumkan tautan Google Form kuesioner soal.</p>
                </div>

                <div class="p-6 space-y-5">
                    @if($lockedType)
                        <input type="hidden" name="type" id="radioTypeLocked" value="{{ $lockedType }}">
                    @else
                        <!-- Hanya tampil jika membuka create langsung tanpa parameter ?type= -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-2">
                                Kategori / Jenis Ujian <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div onclick="setExamType('uas')"
                                     onkeydown="if(event.key==='Enter'||event.key===' '){setExamType('uas');event.preventDefault();}"
                                     id="cardExamTypeUas"
                                     role="button"
                                     tabindex="0"
                                     class="relative flex items-center justify-between p-3.5 rounded-[8px] border-2 border-[#142C5C] bg-slate-50 cursor-pointer transition-all duration-150 select-none">
                                    <input type="radio" name="type" id="radioTypeUas" value="uas" {{ $activeType === 'uas' ? 'checked' : '' }} class="sr-only">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-[#142C5C]/10 text-[#142C5C] flex items-center justify-center shrink-0">
                                            <i data-lucide="building-2" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-[#142C5C]">UAS (Ujian Akhir Semester)</div>
                                            <div class="text-[11px] text-slate-500">Ujian sekolah serentak, diawasi per ruang kelas</div>
                                        </div>
                                    </div>
                                    <div id="indicatorTypeUas" class="w-5 h-5 rounded-full border-2 border-[#142C5C] bg-[#142C5C] text-white flex items-center justify-center shrink-0 transition-all">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>

                                <div onclick="setExamType('uts')"
                                     onkeydown="if(event.key==='Enter'||event.key===' '){setExamType('uts');event.preventDefault();}"
                                     id="cardExamTypeUts"
                                     role="button"
                                     tabindex="0"
                                     class="relative flex items-center justify-between p-3.5 rounded-[8px] border-2 border-slate-200 bg-white hover:border-slate-300 cursor-pointer transition-all duration-150 select-none">
                                    <input type="radio" name="type" id="radioTypeUts" value="uts" {{ $activeType === 'uts' ? 'checked' : '' }} class="sr-only">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                            <i data-lucide="file-text" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-900">UTS / PTS (Tengah Semester)</div>
                                            <div class="text-[11px] text-slate-500">Penilaian mapel, diawasi oleh guru pengampu</div>
                                        </div>
                                    </div>
                                    <div id="indicatorTypeUts" class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <!-- Judul Ujian -->
                        <div>
                            <label for="input_title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Judul Ujian <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="book-open" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="title" id="input_title" value="{{ old('title') }}" required
                                       class="w-full bg-white border border-slate-200 rounded-[8px] pl-10 pr-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all"
                                       placeholder="Contoh: PTS Matematika Wajib Kelas X">
                            </div>
                            <div id="error_title" class="hidden text-xs text-rose-500 font-semibold mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>Judul ujian wajib diisi.</span>
                            </div>
                            @error('title') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Google Form URL -->
                        <div>
                            <label for="input_google_form_url" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Tautan Google Form Soal <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#142C5C]">
                                    <i data-lucide="link" class="w-4 h-4"></i>
                                </div>
                                <input type="url" name="google_form_url" id="input_google_form_url" value="{{ old('google_form_url') }}" required
                                       class="w-full bg-white border border-slate-200 rounded-[8px] pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-900 tabular-nums placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all"
                                       placeholder="https://docs.google.com/forms/d/e/.../viewform">
                            </div>
                            <div id="error_google_form_url" class="hidden text-xs text-rose-500 font-semibold mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>Tautan Google Form wajib diisi.</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-1.5 flex-wrap">
                                <p class="text-[11px] text-slate-500">Pastikan form disetel terbuka tanpa login khusus editor.</p>
                                <button type="button" onclick="testFormUrl()" class="text-[11px] font-semibold text-[#142C5C] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                    <span>Uji Buka Form</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </button>
                            </div>
                            @error('google_form_url') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Deskripsi / Petunjuk -->
                    <div>
                        <label for="input_description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Petunjuk Pengerjaan <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <textarea name="description" id="input_description" rows="3"
                                  class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all"
                                  placeholder="Tuliskan catatan tata tertib atau petunjuk pengerjaan bagi siswa...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Navigasi Langkah 1 -->
            <div class="flex items-center justify-between pt-1">
                <a href="{{ $backUrl }}" class="px-4 py-2.5 text-xs sm:text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-[8px] transition-colors shadow-2xs">
                    Batal
                </a>
                <button type="button" onclick="validateAndGoToStep(2)" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                    <span>Lanjut ke Jadwal &amp; Kelas</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LANGKAH 2: JADWAL SESI & KELAS TARGET                         -->
        <!-- ============================================================= -->
        <div id="step-panel-2" class="step-panel hidden space-y-5">
            <!-- Card Gabungan: Durasi Standar & Sesi Jadwal -->
            <div class="card overflow-hidden">
                <div class="bg-[#142C5C] px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold tracking-tight">Durasi, Sesi Jadwal &amp; Kelas Target</h3>
                        <p class="text-xs text-blue-200 mt-0.5">Atur durasi standar (<span id="preview_duration_jp_text" class="text-white font-semibold">2 Jam Pelajaran</span>) serta pembagian sesi per kelas.</p>
                    </div>
                    <span class="text-xs font-semibold text-blue-100 shrink-0">
                        <span id="preview_duration_pill">{{ old('duration', 70) }} Menit</span> / <span id="preview_duration_jp_pill">2 JP</span>
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Bagian 1: Durasi Standar Global -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="relative flex-1">
                            <input type="number" name="duration" id="exam_duration" required min="1" value="{{ old('duration', 70) }}"
                                   class="w-full bg-white border border-slate-200 rounded-[8px] pl-4 pr-16 py-2.5 text-sm text-slate-900 font-bold focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-xs font-bold text-slate-400 uppercase tracking-wider">MENIT</span>
                        </div>

                        <div class="grid grid-cols-3 sm:flex items-center gap-2 shrink-0">
                            <button type="button" data-duration="35" onclick="setExamDuration(35)" class="duration-preset-btn w-full sm:w-28 py-2.5 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-[#142C5C] hover:border-[#142C5C] hover:bg-slate-50/80 rounded-[8px] transition-all text-center cursor-pointer">1 JP (35m)</button>
                            <button type="button" data-duration="70" onclick="setExamDuration(70)" class="duration-preset-btn w-full sm:w-28 py-2.5 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-[#142C5C] hover:border-[#142C5C] hover:bg-slate-50/80 rounded-[8px] transition-all text-center cursor-pointer">2 JP (70m)</button>
                            <button type="button" data-duration="105" onclick="setExamDuration(105)" class="duration-preset-btn w-full sm:w-28 py-2.5 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-[#142C5C] hover:border-[#142C5C] hover:bg-slate-50/80 rounded-[8px] transition-all text-center cursor-pointer">3 JP (105m)</button>
                        </div>
                    </div>

                    <!-- Bagian 2: Daftar Sesi Jadwal -->
                    <div class="pt-5 border-t border-slate-100 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-bold text-[#142C5C]">Daftar Sesi Jadwal <span class="text-rose-500">*</span></h4>
                                <p class="text-xs text-slate-500 mt-0.5">Tiap sesi hanya muncul pada akun siswa dari kelas yang dipilih pada sesi tersebut.</p>
                            </div>
                            <button type="button" id="addScheduleBtn" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-[#142C5C] bg-slate-50 hover:bg-slate-100 rounded-[8px] border border-slate-200 transition-colors shrink-0 cursor-pointer">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Sesi Baru</span>
                            </button>
                        </div>

                        @error('schedules') <span class="text-rose-500 text-xs block">{{ $message }}</span> @enderror

                        <div id="scheduleContainer" class="space-y-4">
                            <!-- Schedule slots will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Langkah 2 -->
            <div class="flex items-center justify-between pt-1">
                <button type="button" onclick="goToStep(1)" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-[8px] transition-colors shadow-2xs cursor-pointer">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali</span>
                </button>
                <button type="button" onclick="validateAndGoToStep(3)" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                    <span>Lanjut ke Review &amp; Keamanan</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LANGKAH 3: KEAMANAN, STATUS & REVIEW AKHIR                    -->
        <!-- ============================================================= -->
        <div id="step-panel-3" class="step-panel hidden space-y-5">
            <!-- Pengaturan Keamanan & Status -->
            <div class="card overflow-hidden">
                <div class="bg-[#142C5C] px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold tracking-tight">Keamanan &amp; Status Publikasi</h3>
                        <p class="text-xs text-blue-200 mt-0.5">Tentukan batas toleransi keluar aplikasi serta status penayangan jadwal.</p>
                    </div>
                    <span class="text-xs font-semibold text-blue-100 shrink-0" id="review_status_pill">Siap Diterbitkan</span>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Toleransi Pelanggaran -->
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-[#142C5C]">Batas Toleransi Keluar (Max Violation) <span class="text-rose-500">*</span></h4>
                                <p class="text-xs text-slate-500 mt-0.5">Tentukan batas minimize aplikasi sebelum ujian siswa terkunci otomatis.</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 shrink-0" id="violation_badge">Mode Standar (3x Keluar)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <button type="button" onclick="selectViolationPreset(1)" id="preset_violation_1" class="violation-preset-card p-3.5 rounded-[8px] border text-left transition-all border-slate-200 bg-white hover:border-slate-300 cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-900">1x Keluar</span>
                                    <span class="text-[11px] font-semibold text-rose-600">Ketat</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Langsung terkunci saat pertama kali minimize aplikasi.</p>
                            </button>

                            <button type="button" onclick="selectViolationPreset(3)" id="preset_violation_3" class="violation-preset-card p-3.5 rounded-[8px] border-2 text-left transition-all border-[#142C5C] bg-slate-50 cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-[#142C5C]">3x Keluar</span>
                                    <span class="text-[11px] font-semibold text-[#142C5C]">Standar</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">Rekomendasi resmi SMKN 1 Tirtamulya untuk ujian formal.</p>
                            </button>

                            <button type="button" onclick="selectViolationPreset(5)" id="preset_violation_5" class="violation-preset-card p-3.5 rounded-[8px] border text-left transition-all border-slate-200 bg-white hover:border-slate-300 cursor-pointer group">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-900">5x Keluar</span>
                                    <span class="text-[11px] font-semibold text-slate-600">Longgar</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Toleransi tinggi, cocok untuk simulasi &amp; kendala teknis.</p>
                            </button>
                        </div>

                        <!-- Custom Stepper Row -->
                        <div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 rounded-[8px] bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <i data-lucide="sliders" class="w-4 h-4 text-slate-400"></i>
                                <span>Atau sesuaikan angka manual:</span>
                            </div>
                            <div class="flex items-center gap-2 self-center sm:self-auto">
                                <div class="inline-flex items-center bg-white border border-slate-200 rounded-[8px] p-1">
                                    <button type="button" onclick="stepViolation(-1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer">−</button>
                                    <input type="number" name="max_violation" id="max_violation" min="1" max="20" value="{{ old('max_violation', 3) }}" required
                                           oninput="handleViolationInput(this.value)"
                                           class="w-12 text-center font-bold text-slate-900 text-sm focus:outline-none py-1">
                                    <button type="button" onclick="stepViolation(1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer">+</button>
                                </div>
                                <span class="text-xs font-semibold text-slate-500">Kali Keluar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Publikasi Ujian -->
                    <div class="pt-5 border-t border-slate-100">
                        <div class="mb-3">
                            <h4 class="text-sm font-bold text-[#142C5C]">Status Publikasi Ujian <span class="text-rose-500">*</span></h4>
                            <p class="text-xs text-slate-500 mt-0.5">Pilih apakah jadwal langsung tayang di aplikasi siswa atau disimpan sebagai draf.</p>
                        </div>

                        <input type="hidden" name="status" id="status_input" value="{{ old('status', 'active') }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div onclick="selectExamStatus('active')" id="card_status_active" class="status-card p-4 rounded-[8px] border-2 border-[#142C5C] bg-slate-50 cursor-pointer transition-all flex items-start gap-3">
                                <div class="w-4 h-4 rounded-full border-2 border-[#142C5C] bg-[#142C5C] mt-0.5 flex items-center justify-center shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-[#142C5C]">Aktif (Tersedia untuk Siswa)</div>
                                    <p class="text-xs text-slate-600 mt-1">Ujian langsung muncul di aplikasi siswa sesuai jadwal masing-masing kelas.</p>
                                </div>
                            </div>

                            <div onclick="selectExamStatus('inactive')" id="card_status_inactive" class="status-card p-4 rounded-[8px] border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition-all flex items-start gap-3">
                                <div class="w-4 h-4 rounded-full border-2 border-slate-400 mt-0.5 flex items-center justify-center shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full bg-transparent"></div>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-700">Nonaktif (Simpan Draft)</div>
                                    <p class="text-xs text-slate-500 mt-1">Disimpan sementara sebagai draf sebelum ditayangkan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Konfirmasi -->
                    <div class="pt-5 border-t border-slate-100 space-y-3">
                        <div>
                            <h4 class="text-sm font-bold text-[#142C5C]">Ringkasan Konfirmasi Ujian</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Periksa kembali detail sebelum menyimpan jadwal ke sistem.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="bg-slate-50 p-3.5 rounded-[8px] border border-slate-200 space-y-1">
                                <div class="text-[11px] font-semibold text-slate-500">Judul &amp; Tautan Soal</div>
                                <div id="review_title" class="font-bold text-slate-900 text-sm truncate">—</div>
                                <div class="text-[11px] text-slate-500 truncate tabular-nums" id="review_url">—</div>
                            </div>

                            <div class="bg-slate-50 p-3.5 rounded-[8px] border border-slate-200 space-y-1">
                                <div class="text-[11px] font-semibold text-slate-500">Durasi &amp; Keamanan</div>
                                <div class="flex items-center gap-3 font-bold text-slate-900">
                                    <span id="review_duration">70 Menit (2 JP)</span>
                                    <span id="review_violation" class="text-[#142C5C]">Max 3x Keluar</span>
                                </div>
                                <div class="text-[11px] text-slate-500" id="review_sessions_count">Total 1 Sesi Terdaftar</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-xs font-semibold text-slate-700">Jadwal Sesi &amp; Kelas Terdaftar</div>
                            <div id="review_schedule_list" class="space-y-2">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Langkah 3 & Submit -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-1">
                <button type="button" onclick="goToStep(2)" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-[8px] transition-colors shadow-2xs cursor-pointer">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Jadwal</span>
                </button>
                <button type="submit" id="submitExamBtn" class="inline-flex items-center justify-center gap-2 px-7 py-2.5 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Simpan &amp; Jadwalkan Ujian</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    // Master data kelas dari backend
    const allClasses = @json($classes);

    let scheduleIndex = 0; // counter untuk index unik
    let currentStep = 1;

    function getTodayString() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function formatJP(mins) {
        const num = parseInt(mins) || 0;
        if (num <= 0) return '0 JP';
        const jp = num / 35;
        const formatted = Number.isInteger(jp) ? jp : parseFloat(jp.toFixed(1));
        return `${formatted} JP`;
    }

    // ==========================================
    // STEPPER NAVIGATION LOGIC
    // ==========================================
    function goToStep(step) {
        currentStep = step;

        // Tampilkan panel yang sesuai
        document.querySelectorAll('.step-panel').forEach((panel, idx) => {
            if (idx + 1 === step) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });

        // Perbarui visual tab stepper
        [1, 2, 3].forEach(s => {
            const tab = document.getElementById(`step-tab-${s}`);
            if (!tab) return;
            const iconBox = tab.querySelector('.step-icon-box');
            const sub = tab.querySelector('.step-sub');
            const label = tab.querySelector('.step-label');

            if (s === step) {
                tab.className = "step-tab flex items-center gap-2 sm:gap-3 p-2.5 rounded-[8px] transition-all text-left bg-slate-50 text-[#142C5C] border border-slate-200 shadow-2xs cursor-pointer";
                if (iconBox) iconBox.className = "step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-[#142C5C] text-white flex items-center justify-center shrink-0 transition-all";
                if (sub) sub.className = "step-sub text-[10px] font-bold text-[#142C5C] uppercase tracking-wider block";
                if (label) label.className = "step-label text-xs sm:text-sm font-bold text-[#142C5C] truncate block";
            } else if (s < step) {
                tab.className = "step-tab flex items-center gap-2 sm:gap-3 p-2.5 rounded-[8px] transition-all text-left bg-slate-50 text-[#142C5C] border border-slate-200 shadow-2xs cursor-pointer";
                if (iconBox) iconBox.className = "step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-[#142C5C] text-white flex items-center justify-center shrink-0 transition-all";
                if (sub) sub.className = "step-sub text-[10px] font-semibold text-slate-500 uppercase tracking-wider block";
                if (label) label.className = "step-label text-xs sm:text-sm font-semibold text-[#142C5C] truncate block";
            } else {
                tab.className = "step-tab flex items-center gap-2 sm:gap-3 p-2.5 rounded-[8px] transition-all text-left bg-slate-50/70 text-slate-500 hover:bg-slate-100/80 border border-transparent cursor-pointer";
                if (iconBox) iconBox.className = "step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 transition-all";
                if (sub) sub.className = "step-sub text-[10px] font-semibold text-slate-400 uppercase tracking-wider block";
                if (label) label.className = "step-label text-xs sm:text-sm font-medium text-slate-600 truncate block";
            }
        });

        // Jika masuk langkah 3, populate review data
        if (step === 3) {
            updateReviewStep();
        }

        // Render ulang icon Lucide
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Scroll halus ke atas form
        window.scrollTo({ top: 0, behavior: 'smooth' });
        triggerAutosave();
    }

    function handleStepTabClick(targetStep) {
        if (targetStep === currentStep) return;

        if (targetStep > currentStep) {
            if (currentStep === 1) {
                if (!validateStep1()) return;
            }
            if (currentStep === 2 && targetStep === 3) {
                if (!validateStep2()) return;
            }
        }
        goToStep(targetStep);
    }

    // ==========================================
    // VALIDASI FORM & INDIKATOR MERAH (REQUIRED)
    // ==========================================
    function markFieldInvalid(el, errorEl, message) {
        if (!el) return;
        el.classList.remove('border-slate-200', 'focus:border-[#142C5C]', 'focus:ring-[#142C5C]/10');
        el.classList.add('border-rose-500', 'bg-rose-50/20', 'ring-2', 'ring-rose-500/20');
        if (errorEl) {
            if (message) {
                const textSpan = errorEl.querySelector('span');
                if (textSpan) textSpan.textContent = message;
                else errorEl.textContent = message;
            }
            errorEl.classList.remove('hidden');
        }
    }

    function clearFieldInvalid(el, errorEl) {
        if (!el) return;
        el.classList.remove('border-rose-500', 'bg-rose-50/20', 'ring-2', 'ring-rose-500/20');
        el.classList.add('border-slate-200');
        if (errorEl) {
            errorEl.classList.add('hidden');
        }
    }

    function validateStep1() {
        const titleInput = document.getElementById('input_title');
        const urlInput = document.getElementById('input_google_form_url');
        const errorTitle = document.getElementById('error_title');
        const errorUrl = document.getElementById('error_google_form_url');

        let isValid = true;
        let firstInvalid = null;

        if (!titleInput || !titleInput.value.trim()) {
            markFieldInvalid(titleInput, errorTitle, 'Judul ujian wajib diisi.');
            isValid = false;
            if (!firstInvalid) firstInvalid = titleInput;
        } else {
            clearFieldInvalid(titleInput, errorTitle);
        }

        if (!urlInput || !urlInput.value.trim()) {
            markFieldInvalid(urlInput, errorUrl, 'Tautan Google Form wajib diisi.');
            isValid = false;
            if (!firstInvalid) firstInvalid = urlInput;
        } else {
            try {
                new URL(urlInput.value.trim());
                clearFieldInvalid(urlInput, errorUrl);
            } catch (e) {
                markFieldInvalid(urlInput, errorUrl, 'Format URL tidak valid. Pastikan diawali dengan https:// atau http://');
                isValid = false;
                if (!firstInvalid) firstInvalid = urlInput;
            }
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        if (!isValid && firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
            return false;
        }

        return true;
    }

    function validateAndGoToStep(targetStep) {
        if (targetStep === 2) {
            if (!validateStep1()) return;
            goToStep(2);
        } else if (targetStep === 3) {
            if (!validateStep2()) return;
            goToStep(3);
        }
    }

    function validateStep2() {
        const slots = document.querySelectorAll('.schedule-slot');
        if (slots.length === 0) {
            alert('Harap tambahkan minimal 1 sesi ujian.');
            return false;
        }

        let isValid = true;
        let firstInvalid = null;

        for (let i = 0; i < slots.length; i++) {
            const slot = slots[i];
            const dateInput = slot.querySelector('.schedule-date');
            const timeInput = slot.querySelector('.schedule-time');
            const dateError = slot.querySelector('.schedule-date-error');
            const timeError = slot.querySelector('.schedule-time-error');
            const classGrid = slot.querySelector('.slot-class-grid');
            const classError = slot.querySelector('.slot-class-error');
            const checked = slot.querySelectorAll('.slot-class-checkbox:checked');

            if (!dateInput || !dateInput.value) {
                markFieldInvalid(dateInput, dateError, 'Tanggal ujian wajib diisi.');
                isValid = false;
                if (!firstInvalid) firstInvalid = dateInput;
            } else {
                clearFieldInvalid(dateInput, dateError);
            }

            if (!timeInput || !timeInput.value) {
                markFieldInvalid(timeInput, timeError, 'Jam mulai wajib diisi.');
                isValid = false;
                if (!firstInvalid) firstInvalid = timeInput;
            } else {
                clearFieldInvalid(timeInput, timeError);
            }

            if (checked.length === 0) {
                if (classGrid) {
                    classGrid.classList.remove('border-slate-200/90');
                    classGrid.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/20', 'bg-rose-50/10');
                }
                if (classError) classError.classList.remove('hidden');
                isValid = false;
                if (!firstInvalid) firstInvalid = classGrid;
            } else {
                if (classGrid) {
                    classGrid.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/20', 'bg-rose-50/10');
                    classGrid.classList.add('border-slate-200/90');
                }
                if (classError) classError.classList.add('hidden');
            }
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        if (!isValid && firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof firstInvalid.focus === 'function') firstInvalid.focus();
            return false;
        }

        return true;
    }

    function testFormUrl() {
        const urlInput = document.getElementById('input_google_form_url');
        const errorUrl = document.getElementById('error_google_form_url');
        const val = urlInput ? urlInput.value.trim() : '';
        if (!val) {
            markFieldInvalid(urlInput, errorUrl, 'Tautan Google Form wajib diisi terlebih dahulu.');
            if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            urlInput.focus();
            return;
        }
        try {
            new URL(val);
            clearFieldInvalid(urlInput, errorUrl);
            window.open(val, '_blank');
        } catch (e) {
            markFieldInvalid(urlInput, errorUrl, 'Format URL tidak valid. Pastikan diawali dengan https:// atau http://');
            if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            urlInput.focus();
        }
    }

    // ==========================================
    // RENDER SLOT JADWAL SESI
    // ==========================================
    function renderScheduleSlot(idx, defaultDate, defaultTime = '07:30', defaultDuration = '', selectedClassIds = []) {
        const container = document.getElementById('scheduleContainer');
        const num = container.querySelectorAll('.schedule-slot').length + 1;
        const dateVal = defaultDate || getTodayString();
        const globalDurationVal = parseInt(document.getElementById('exam_duration') ? document.getElementById('exam_duration').value : 70) || 70;

        let classCardsHtml = '';
        allClasses.forEach(c => {
            const isChecked = selectedClassIds.includes(c.id);
            const lowerName = c.name.toLowerCase().trim();
            let grade = 'other';
            if (lowerName.startsWith('10') || lowerName.startsWith('x ') || lowerName === 'x') grade = '10';
            else if (lowerName.startsWith('11') || lowerName.startsWith('xi ') || lowerName === 'xi') grade = '11';
            else if (lowerName.startsWith('12') || lowerName.startsWith('xii ') || lowerName === 'xii') grade = '12';

            classCardsHtml += `
                <label class="slot-class-card group flex items-center gap-2 p-2.5 rounded-[8px] border transition-all cursor-pointer select-none ${isChecked ? 'bg-slate-50 border-[#142C5C]' : 'bg-white border-slate-200 hover:border-slate-300'}" data-classname="${lowerName}" data-grade="${grade}">
                    <input type="checkbox" name="schedules[${idx}][classes][]" value="${c.id}" ${isChecked ? 'checked' : ''}
                           class="slot-class-checkbox rounded text-[#142C5C] focus:ring-[#142C5C]/10 border-slate-300 w-4 h-4 shrink-0"
                           onchange="onSlotClassChanged(${idx}); triggerAutosave();">
                    <span class="text-xs ${isChecked ? 'font-bold text-[#142C5C]' : 'font-medium text-slate-700'} truncate">${c.name}</span>
                </label>
            `;
        });

        const slot = document.createElement('div');
        slot.className = 'schedule-slot p-5 bg-white border border-slate-200 rounded-2xl space-y-4 transition-all';
        slot.setAttribute('data-index', idx);
        slot.innerHTML = `
            <!-- Header Sesi -->
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-6 h-6 rounded-lg bg-[#142C5C] text-white text-xs font-bold shrink-0 schedule-num">${num}</div>
                    <span class="text-xs sm:text-sm font-bold text-[#142C5C]">Jadwal Sesi <span class="slot-title-num">${num}</span></span>
                </div>
                <button type="button" onclick="removeScheduleSlot(this)" class="remove-slot-btn inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer ${num === 1 ? 'hidden' : ''}">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Hapus Sesi</span>
                </button>
            </div>

            <!-- Baris 1: Tanggal, Jam Mulai & Durasi Sesi -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Ujian <span class="text-rose-500">*</span></label>
                    <input type="date" name="schedules[${idx}][date]" value="${dateVal}" required class="schedule-date w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 font-medium focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all">
                    <div class="schedule-date-error hidden text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Tanggal ujian wajib diisi.</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="schedules[${idx}][time]" value="${defaultTime}" required class="schedule-time w-full bg-white border border-slate-200 rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 font-medium focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all">
                    <div class="schedule-time-error hidden text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Jam mulai wajib diisi.</span>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Durasi Sesi</label>
                        <button type="button" onclick="toggleSlotDurationLock(${idx}, this)" class="slot-dur-lock-btn text-[11px] font-semibold text-[#142C5C] hover:underline cursor-pointer">
                            ${defaultDuration ? 'Ikuti Standar' : 'Ubah'}
                        </button>
                    </div>
                    <div class="relative">
                        <input type="number" name="schedules[${idx}][duration]" value="${defaultDuration || ''}" min="1" ${defaultDuration ? '' : 'readonly'} placeholder="Ikuti Standar (${globalDurationVal} mnt)" class="schedule-duration w-full border border-slate-200 rounded-[8px] pl-3.5 pr-14 py-2.5 text-sm font-medium placeholder:text-slate-500 focus:outline-none focus:border-[#142C5C] focus:ring-2 focus:ring-[#142C5C]/10 transition-all ${defaultDuration ? 'bg-white text-slate-900' : 'bg-slate-100/80 text-slate-600 cursor-not-allowed'}" oninput="updateAllPreviews(); triggerAutosave();">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-[11px] font-semibold text-slate-400 uppercase">Menit</span>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Pilih Kelas Target -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
                    <div class="inline-flex p-1 bg-slate-100 rounded-[8px] text-xs font-semibold text-slate-600 shrink-0 self-start sm:self-auto">
                        <button type="button" onclick="filterSlotGrade(${idx}, 'all', this)" class="grade-filter-btn px-2.5 py-1 rounded-lg bg-white text-[#142C5C] shadow-2xs cursor-pointer">Semua</button>
                        <button type="button" onclick="filterSlotGrade(${idx}, '10', this)" class="grade-filter-btn px-2.5 py-1 rounded-lg hover:text-slate-900 cursor-pointer">Kelas X</button>
                        <button type="button" onclick="filterSlotGrade(${idx}, '11', this)" class="grade-filter-btn px-2.5 py-1 rounded-lg hover:text-slate-900 cursor-pointer">Kelas XI</button>
                        <button type="button" onclick="filterSlotGrade(${idx}, '12', this)" class="grade-filter-btn px-2.5 py-1 rounded-lg hover:text-slate-900 cursor-pointer">Kelas XII</button>
                    </div>

                    <input type="text" oninput="filterSlotClasses(${idx}, this.value)" placeholder="Cari nama kelas..." class="flex-1 min-w-[160px] px-3.5 py-2 bg-white border border-slate-200 rounded-[8px] text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#142C5C]">

                    <button type="button" onclick="toggleAllSlotClasses(${idx})" class="slot-toggle-btn text-xs font-bold text-[#142C5C] bg-slate-50 hover:bg-slate-100 border border-slate-200 px-3.5 py-2 rounded-[8px] transition-all shrink-0 cursor-pointer">
                        Pilih Semua
                    </button>
                </div>

                <div class="slot-class-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 max-h-48 overflow-y-auto transition-all">
                    ${classCardsHtml}
                </div>
                <div class="slot-class-error hidden text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                    <span>Wajib memilih minimal 1 kelas untuk sesi ini.</span>
                </div>
            </div>
        `;

        container.appendChild(slot);
        onSlotClassChanged(idx);
        updateAllPreviews();

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function toggleSlotDurationLock(idx, btn) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        const durInput = slot?.querySelector('.schedule-duration');
        if (!durInput) return;

        const isLocked = durInput.hasAttribute('readonly');
        const globalDur = parseInt(document.getElementById('exam_duration')?.value || 70) || 70;

        if (isLocked) {
            durInput.removeAttribute('readonly');
            durInput.classList.remove('bg-slate-100/80', 'text-slate-600', 'cursor-not-allowed');
            durInput.classList.add('bg-white', 'text-slate-900');
            if (!durInput.value) durInput.value = globalDur;
            durInput.focus();
            if (btn) btn.textContent = 'Ikuti Standar';
        } else {
            durInput.setAttribute('readonly', 'readonly');
            durInput.value = '';
            durInput.placeholder = `Ikuti Standar (${globalDur} mnt)`;
            durInput.classList.remove('bg-white', 'text-slate-900');
            durInput.classList.add('bg-slate-100/80', 'text-slate-600', 'cursor-not-allowed');
            if (btn) btn.textContent = 'Ubah';
        }
        updateAllPreviews();
        triggerAutosave();
    }

    function setSlotDuration(idx, val) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const durInput = slot.querySelector('.schedule-duration');
        if (durInput) {
            durInput.value = val;
        }
        updateAllPreviews();
        triggerAutosave();
    }

    function filterSlotGrade(idx, grade, btn) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;

        // Visual button toggle
        slot.querySelectorAll('.grade-filter-btn').forEach(b => {
            b.className = "grade-filter-btn px-2.5 py-1 rounded-lg hover:text-slate-900 text-slate-600 cursor-pointer";
        });
        btn.className = "grade-filter-btn px-2.5 py-1 rounded-lg bg-white text-[#142C5C] font-bold shadow-2xs cursor-pointer";

        // Pure CSS data-attribute filter (instant 0ms, zero DOM loops!)
        const grid = slot.querySelector('.slot-class-grid');
        if (grid) {
            grid.setAttribute('data-active-grade', grade);
        }
        onSlotClassChanged(idx);
    }

    function onSlotClassChanged(idx) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const checked = slot.querySelectorAll('.slot-class-checkbox:checked');
        const countBadge = slot.querySelector('.slot-class-count');
        const toggleBtn = slot.querySelector('.slot-toggle-btn');
        const activeGrade = slot.querySelector('.slot-class-grid')?.getAttribute('data-active-grade') || 'all';
        let visibleSelector = '.slot-class-card:not([style*="display: none"])';
        if (activeGrade && activeGrade !== 'all') {
            visibleSelector += `[data-grade="${activeGrade}"]`;
        }
        const visibleCards = slot.querySelectorAll(visibleSelector);
        const visibleBoxes = Array.from(visibleCards).map(c => c.querySelector('.slot-class-checkbox'));

        // Visual highlight card yang dipilih
        slot.querySelectorAll('.slot-class-card').forEach(card => {
            const cb = card.querySelector('.slot-class-checkbox');
            const span = card.querySelector('span');
            if (cb && cb.checked) {
                card.className = "slot-class-card group flex items-center gap-2 p-2.5 rounded-[8px] border transition-all cursor-pointer select-none bg-slate-50 border-[#142C5C]";
                if (span) span.className = "text-xs font-bold text-[#142C5C] truncate";
            } else {
                card.className = "slot-class-card group flex items-center gap-2 p-2.5 rounded-[8px] border transition-all cursor-pointer select-none bg-white border-slate-200 hover:border-slate-300";
                if (span) span.className = "text-xs font-medium text-slate-700 truncate";
            }
        });

     

        if (toggleBtn) {
            const allVisibleChecked = visibleBoxes.length > 0 && visibleBoxes.every(cb => cb.checked);
            toggleBtn.textContent = allVisibleChecked ? 'Batal Semua' : 'Pilih Semua';
        }

        const classGrid = slot.querySelector('.slot-class-grid');
        const classError = slot.querySelector('.slot-class-error');
        if (checked.length > 0) {
            if (classGrid) {
                classGrid.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/20', 'bg-rose-50/10');
                classGrid.classList.add('border-slate-200/90');
            }
            if (classError) classError.classList.add('hidden');
        }

        updateAllPreviews();
    }

    function toggleAllSlotClasses(idx) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const activeGrade = slot.querySelector('.slot-class-grid')?.getAttribute('data-active-grade') || 'all';
        let visibleSelector = '.slot-class-card:not([style*="display: none"])';
        if (activeGrade && activeGrade !== 'all') {
            visibleSelector += `[data-grade="${activeGrade}"]`;
        }
        const visibleCards = slot.querySelectorAll(visibleSelector);
        const visibleBoxes = Array.from(visibleCards).map(c => c.querySelector('.slot-class-checkbox')).filter(Boolean);
        const allChecked = visibleBoxes.every(cb => cb.checked);
        visibleBoxes.forEach(cb => cb.checked = !allChecked);
        onSlotClassChanged(idx);
        triggerAutosave();
    }

    function filterSlotClasses(idx, query) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const q = query.toLowerCase().trim();
        slot.querySelectorAll('.slot-class-card').forEach(card => {
            const name = card.getAttribute('data-classname') || '';
            card.style.display = (q === '' || name.includes(q)) ? '' : 'none';
        });
    }


    function setExamDuration(mins) {
        document.getElementById('exam_duration').value = mins;
        updateAllPreviews();
        triggerAutosave();
    }

    function removeScheduleSlot(btn) {
        const slot = btn.closest('.schedule-slot');
        const container = document.getElementById('scheduleContainer');
        if (container.querySelectorAll('.schedule-slot').length <= 1) return;
        slot.remove();
        renumberSlots();
        updateAllPreviews();
        triggerAutosave();
    }

    function renumberSlots() {
        const slots = document.querySelectorAll('.schedule-slot');
        slots.forEach((slot, i) => {
            const num = i + 1;
            const numEl = slot.querySelector('.schedule-num');
            const titleNumEl = slot.querySelector('.slot-title-num');
            const removeBtn = slot.querySelector('.remove-slot-btn');

            if (numEl) numEl.textContent = num;
            if (titleNumEl) titleNumEl.textContent = num;
            if (removeBtn) {
                if (slots.length === 1) {
                    removeBtn.classList.add('hidden');
                } else {
                    removeBtn.classList.remove('hidden');
                }
            }
        });
    }

    function updateAllPreviews() {
        const durationInput = document.getElementById('exam_duration');
        const durationVal = parseInt(durationInput ? durationInput.value : 0) || 0;
        const durationPill = document.getElementById('preview_duration_pill');
        const durationJpPill = document.getElementById('preview_duration_jp_pill');
        const durationJpText = document.getElementById('preview_duration_jp_text');

        if (durationPill) durationPill.textContent = `${durationVal} Menit`;
        if (durationJpPill) durationJpPill.textContent = formatJP(durationVal);
        if (durationJpText) durationJpText.textContent = `${formatJP(durationVal)}`;

        // Update active style on preset buttons
        document.querySelectorAll('.duration-preset-btn').forEach(btn => {
            const btnVal = parseInt(btn.getAttribute('data-duration'));
            if (btnVal === durationVal) {
                btn.className = 'duration-preset-btn w-full sm:w-28 py-2.5 text-xs font-bold rounded-[8px] transition-all bg-[#142C5C] text-white shadow-xs text-center cursor-pointer';
            } else {
                btn.className = 'duration-preset-btn w-full sm:w-28 py-2.5 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-[#142C5C] hover:border-[#142C5C] hover:bg-slate-50/80 rounded-[8px] transition-all text-center cursor-pointer';
            }
        });

        const slots = document.querySelectorAll('.schedule-slot');

        slots.forEach((slot) => {
            const durationInput = slot.querySelector('.schedule-duration');
            if (durationInput && durationInput.hasAttribute('readonly')) {
                durationInput.placeholder = `Ikuti Standar (${durationVal} mnt)`;
            }
        });
    }

    // ==========================================
    // UPDATE REVIEW SUMMARY PADA LANGKAH 3
    // ==========================================
    function updateReviewStep() {
        const titleVal = document.getElementById('input_title')?.value || '—';
        const urlVal = document.getElementById('input_google_form_url')?.value || '—';
        const durationVal = parseInt(document.getElementById('exam_duration')?.value || 70);
        const violationVal = document.getElementById('max_violation')?.value || '3';
        const statusVal = document.getElementById('status_input')?.value || 'active';

        document.getElementById('review_title').textContent = titleVal;
        document.getElementById('review_url').textContent = urlVal;
        document.getElementById('review_duration').textContent = `${durationVal} Menit (${formatJP(durationVal)})`;
        document.getElementById('review_violation').textContent = `Max ${violationVal}x Keluar`;

        const statusPill = document.getElementById('review_status_pill');
        if (statusPill) {
            if (statusVal === 'active') {
                statusPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-white/10 border border-white/15 text-white shrink-0";
                statusPill.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Siap Diterbitkan (Aktif)';
            } else {
                statusPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-white/10 border border-white/15 text-blue-100 shrink-0";
                statusPill.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Disimpan Draft (Nonaktif)';
            }
        }

        const slots = document.querySelectorAll('.schedule-slot');
        const countEl = document.getElementById('review_sessions_count');
        if (countEl) countEl.textContent = `Total ${slots.length} Sesi Terdaftar`;

        const listContainer = document.getElementById('review_schedule_list');
        if (!listContainer) return;

        let rowsHtml = '';
        slots.forEach((slot, i) => {
            const dateInput = slot.querySelector('.schedule-date');
            const timeInput = slot.querySelector('.schedule-time');
            const durationInput = slot.querySelector('.schedule-duration');
            const customDur = durationInput && durationInput.value ? parseInt(durationInput.value) : null;
            const effectiveDur = customDur || durationVal;
            const checkedBoxes = slot.querySelectorAll('.slot-class-checkbox:checked');

            let timeString = '—';
            let dateString = '—';
            if (dateInput && dateInput.value && timeInput && timeInput.value) {
                const dt = new Date(`${dateInput.value}T${timeInput.value}`);
                const endDT = new Date(dt.getTime() + effectiveDur * 60000);
                dateString = `${days[dt.getDay()]}, ${dt.getDate()} ${months[dt.getMonth()]} ${dt.getFullYear()}`;
                const sH = String(dt.getHours()).padStart(2, '0');
                const sM = String(dt.getMinutes()).padStart(2, '0');
                const eH = String(endDT.getHours()).padStart(2, '0');
                const eM = String(endDT.getMinutes()).padStart(2, '0');
                timeString = `${sH}:${sM} - ${eH}:${eM} WIB`;
            }

            const classNames = Array.from(checkedBoxes).map(cb => {
                const label = cb.closest('label');
                return label ? label.querySelector('span').textContent.trim() : '';
            }).filter(Boolean);

            const classLabel = classNames.length > 0
                ? `${classNames.length} Kelas (${classNames.slice(0, 3).join(', ')}${classNames.length > 3 ? ', ...' : ''})`
                : '<span class="text-rose-600 font-medium">Belum ada kelas</span>';

            rowsHtml += `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-4 font-semibold text-[#142C5C] whitespace-nowrap">Sesi ${i + 1}</td>
                    <td class="py-3 px-4 text-slate-800 whitespace-nowrap">${dateString}</td>
                    <td class="py-3 px-4 tabular-nums text-xs text-[#142C5C] font-semibold whitespace-nowrap">${timeString}</td>
                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">${effectiveDur} Menit</td>
                    <td class="py-3 px-4 text-slate-700">${classLabel}</td>
                </tr>
            `;
        });

        listContainer.innerHTML = `
            <div class="overflow-x-auto rounded-[8px] border border-slate-200 bg-white">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                            <th class="py-2.5 px-4 whitespace-nowrap">Sesi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Waktu</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Durasi</th>
                            <th class="py-2.5 px-4">Kelas Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
        `;
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    // ==========================================
    // STATUS UJIAN & VIOLATION LOGIC
    // ==========================================
    function selectExamStatus(status) {
        document.getElementById('status_input').value = status;
        const activeCard = document.getElementById('card_status_active');
        const inactiveCard = document.getElementById('card_status_inactive');

        if (status === 'active') {
            activeCard.className = "status-card p-4 rounded-[8px] border-2 border-[#142C5C] bg-slate-50 cursor-pointer transition-all flex items-start gap-3";
            inactiveCard.className = "status-card p-4 rounded-[8px] border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition-all flex items-start gap-3";
            activeCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-[#142C5C] bg-[#142C5C] mt-0.5 flex items-center justify-center shrink-0";
            inactiveCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-slate-400 mt-0.5 flex items-center justify-center shrink-0";
        } else {
            inactiveCard.className = "status-card p-4 rounded-[8px] border-2 border-[#142C5C] bg-slate-50 cursor-pointer transition-all flex items-start gap-3";
            activeCard.className = "status-card p-4 rounded-[8px] border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition-all flex items-start gap-3";
            inactiveCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-[#142C5C] bg-[#142C5C] mt-0.5 flex items-center justify-center shrink-0";
            activeCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-slate-400 mt-0.5 flex items-center justify-center shrink-0";
        }
        triggerAutosave();
    }

    function selectViolationPreset(val) {
        document.getElementById('max_violation').value = val;
        updateViolationUI(val);
        triggerAutosave();
    }

    function stepViolation(delta) {
        const input = document.getElementById('max_violation');
        let current = parseInt(input.value) || 3;
        let nextVal = Math.min(20, Math.max(1, current + delta));
        input.value = nextVal;
        updateViolationUI(nextVal);
        triggerAutosave();
    }

    function handleViolationInput(val) {
        let num = parseInt(val) || 1;
        updateViolationUI(num);
        triggerAutosave();
    }

    function updateViolationUI(val) {
        const num = parseInt(val) || 1;
        
        [1, 3, 5].forEach(v => {
            const el = document.getElementById(`preset_violation_${v}`);
            if (!el) return;
            if (v === num) {
                el.className = "violation-preset-card p-3.5 rounded-[8px] border-2 text-left transition-all border-[#142C5C] bg-slate-50 cursor-pointer group";
                const title = el.querySelector('span.font-bold');
                if (title) title.className = "text-sm font-bold text-[#142C5C]";
            } else {
                el.className = "violation-preset-card p-3.5 rounded-[8px] border text-left transition-all border-slate-200 bg-white hover:border-slate-300 cursor-pointer group";
                const title = el.querySelector('span.font-bold');
                if (title) title.className = "text-sm font-bold text-slate-900";
            }
        });

        const badge = document.getElementById('violation_badge');
        if (badge) {
            if (num === 1) badge.textContent = 'Mode Ketat (1x Keluar)';
            else if (num === 3) badge.textContent = 'Mode Standar (3x Keluar)';
            else if (num === 5) badge.textContent = 'Mode Longgar (5x Keluar)';
            else badge.textContent = `Mode Kustom (${num}x Keluar)`;
        }
    }

    // ==========================================
    // KATEGORI UJIAN (UAS vs UTS)
    // ==========================================
    function setExamType(type) {
        const radioUas = document.getElementById('radioTypeUas');
        const radioUts = document.getElementById('radioTypeUts');
        const cardUas = document.getElementById('cardExamTypeUas');
        const cardUts = document.getElementById('cardExamTypeUts');
        const indUas = document.getElementById('indicatorTypeUas');
        const indUts = document.getElementById('indicatorTypeUts');

        if (!radioUas || !radioUts) return;

        if (type === 'uts') {
            radioUts.checked = true;
            radioUas.checked = false;

            if (cardUts) cardUts.className = 'relative flex items-center justify-between p-3.5 rounded-[8px] border-2 border-[#142C5C] bg-slate-50 cursor-pointer transition-all duration-150 select-none';
            if (indUts) {
                indUts.className = 'w-5 h-5 rounded-full border-2 border-[#142C5C] bg-[#142C5C] text-white flex items-center justify-center shrink-0 transition-all';
                indUts.innerHTML = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
            }
            if (cardUas) cardUas.className = 'relative flex items-center justify-between p-3.5 rounded-[8px] border-2 border-slate-200 bg-white hover:border-slate-300 cursor-pointer transition-all duration-150 select-none';
            if (indUas) {
                indUas.className = 'w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 transition-all';
                indUas.innerHTML = '';
            }
        } else {
            radioUas.checked = true;
            radioUts.checked = false;

            if (cardUas) cardUas.className = 'relative flex items-center justify-between p-3.5 rounded-[8px] border-2 border-[#142C5C] bg-slate-50 cursor-pointer transition-all duration-150 select-none';
            if (indUas) {
                indUas.className = 'w-5 h-5 rounded-full border-2 border-[#142C5C] bg-[#142C5C] text-white flex items-center justify-center shrink-0 transition-all';
                indUas.innerHTML = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
            }
            if (cardUts) cardUts.className = 'relative flex items-center justify-between p-3.5 rounded-[8px] border-2 border-slate-200 bg-white hover:border-slate-300 cursor-pointer transition-all duration-150 select-none';
            if (indUts) {
                indUts.className = 'w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 transition-all';
                indUts.innerHTML = '';
            }
        }

        if (typeof triggerAutosave === 'function') {
            triggerAutosave();
        }
    }

    // ==========================================
    // AUTOSAVE DRAFT FORM UJIAN
    // ==========================================
    const DRAFT_TYPE = '{{ $activeType }}';
    const DRAFT_STORAGE_KEY = `exambro_exam_create_draft_v2_${DRAFT_TYPE}`;
    let autosaveTimeout = null;

    function getFormDraftData() {
        const title = document.querySelector('input[name="title"]')?.value || '';
        const type = document.querySelector('input[name="type"]:checked')?.value || document.querySelector('input[name="type"][type="hidden"]')?.value || DRAFT_TYPE;
        const description = document.querySelector('textarea[name="description"]')?.value || '';
        const google_form_url = document.querySelector('input[name="google_form_url"]')?.value || '';
        const duration = document.querySelector('input[name="duration"]')?.value || '70';
        const max_violation = document.querySelector('input[name="max_violation"]')?.value || '3';
        const status = document.querySelector('input[name="status"]')?.value || 'active';

        const slotsData = [];
        document.querySelectorAll('.schedule-slot').forEach(slot => {
            const date = slot.querySelector('.schedule-date')?.value || '';
            const time = slot.querySelector('.schedule-time')?.value || '';
            const dur = slot.querySelector('.schedule-duration')?.value || '';
            const checkedClasses = Array.from(slot.querySelectorAll('.slot-class-checkbox:checked')).map(cb => parseInt(cb.value));
            slotsData.push({ date, time, duration: dur, classes: checkedClasses });
        });

        const now = new Date();
        const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

        return {
            currentStep,
            title,
            type,
            description,
            google_form_url,
            duration,
            max_violation,
            status,
            slots: slotsData,
            savedAt: timeStr,
        };
    }

    function triggerAutosave() {
        updateAutosaveUI('saving');
        clearTimeout(autosaveTimeout);
        autosaveTimeout = setTimeout(() => {
            try {
                const data = getFormDraftData();
                const hasSubstantialData = Boolean(
                    data.title.trim() || 
                    data.google_form_url.trim() || 
                    (data.slots.length > 0 && data.slots.some(s => s.classes && s.classes.length > 0))
                );

                if (hasSubstantialData) {
                    localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(data));
                    updateAutosaveUI('saved', data.savedAt);
                    const resetBtn = document.getElementById('clearDraftBtn');
                    if (resetBtn) {
                        resetBtn.classList.remove('hidden');
                        resetBtn.classList.add('inline-flex');
                    }
                } else {
                    updateAutosaveUI('idle');
                }
            } catch (err) {
                console.warn('Autosave failed:', err);
                updateAutosaveUI('idle');
            }
        }, 400);
    }

    function updateAutosaveUI(state, timeStr) {
        const badge = document.getElementById('autosaveStatusBadge');
        const dot = document.getElementById('autosaveStatusDot');
        const text = document.getElementById('autosaveStatusText');
        if (!badge || !dot || !text) return;

        if (state === 'saving') {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/80 shadow-2xs';
            dot.className = 'w-2 h-2 rounded-full bg-amber-500 animate-ping';
            text.textContent = 'Menyimpan draft...';
        } else if (state === 'saved') {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs';
            dot.className = 'w-2 h-2 rounded-full bg-emerald-500';
            text.textContent = `Draft tersimpan (${timeStr || 'otomatis'})`;
        } else {
            badge.className = 'hidden';
        }
    }

    function restoreExamDraft() {
        const hasOldSession = Boolean(@json(old('title')));
        if (hasOldSession) {
            return false;
        }

        try {
            const raw = localStorage.getItem(DRAFT_STORAGE_KEY);
            if (!raw) return false;
            const data = JSON.parse(raw);
            if (!data || !Array.isArray(data.slots) || data.slots.length === 0) return false;

            if (data.title) document.getElementById('input_title').value = data.title;
            if (data.description) document.getElementById('input_description').value = data.description;
            if (data.google_form_url) document.getElementById('input_google_form_url').value = data.google_form_url;
            if (data.duration) {
                document.getElementById('exam_duration').value = data.duration;
            }
            if (data.max_violation) {
                document.getElementById('max_violation').value = data.max_violation;
                updateViolationUI(data.max_violation);
            }
            if (data.status) {
                selectExamStatus(data.status);
            }
            if (data.type) {
                setExamType(data.type);
            }

            const container = document.getElementById('scheduleContainer');
            container.innerHTML = '';
            scheduleIndex = 0;

            data.slots.forEach(slot => {
                renderScheduleSlot(scheduleIndex++, slot.date, slot.time, slot.duration, slot.classes || []);
            });
            renumberSlots();
            updateAllPreviews();

            updateAutosaveUI('saved', data.savedAt);
            const resetBtn = document.getElementById('clearDraftBtn');
            if (resetBtn) {
                resetBtn.classList.remove('hidden');
                resetBtn.classList.add('inline-flex');
            }

            const banner = document.getElementById('draftRestoredBanner');
            if (banner) {
                banner.classList.remove('hidden');
                const timeText = document.getElementById('draftRestoredTimeText');
                if (timeText && data.savedAt) {
                    timeText.textContent = `Draft ujian terakhir Anda (tersimpan pukul ${data.savedAt}) berhasil dipulihkan.`;
                }
            }

            if (data.currentStep && data.currentStep >= 1 && data.currentStep <= 3) {
                goToStep(data.currentStep);
            }

            return true;
        } catch (e) {
            console.warn('Gagal memulihkan draft:', e);
            return false;
        }
    }

    function dismissDraftBanner() {
        const banner = document.getElementById('draftRestoredBanner');
        if (banner) banner.classList.add('hidden');
    }

    function clearExamDraft(confirmUser = true) {
        if (confirmUser && !confirm('Kosongkan formulir dan hapus draf ujian yang tersimpan di browser ini?')) {
            return;
        }
        localStorage.removeItem(DRAFT_STORAGE_KEY);
        localStorage.removeItem('exambro_exam_create_draft_v1');
        window.location.reload();
    }

    // ==========================================
    // INITIALIZATION & EVENT LISTENERS
    // ==========================================
    document.addEventListener('DOMContentLoaded', () => {
        const restored = restoreExamDraft();
        if (!restored) {
            renderScheduleSlot(scheduleIndex++);
            const urlType = new URLSearchParams(window.location.search).get('type');
            const defaultType = urlType || (document.getElementById('radioTypeUts')?.checked ? 'uts' : 'uas');
            setExamType(defaultType);
        }

        // Auto-clear invalid state on user typing
        const titleInput = document.getElementById('input_title');
        const urlInput = document.getElementById('input_google_form_url');
        if (titleInput) {
            titleInput.addEventListener('input', () => {
                if (titleInput.value.trim()) {
                    clearFieldInvalid(titleInput, document.getElementById('error_title'));
                }
            });
        }
        if (urlInput) {
            urlInput.addEventListener('input', () => {
                if (urlInput.value.trim()) {
                    clearFieldInvalid(urlInput, document.getElementById('error_google_form_url'));
                }
            });
        }

        // Listener untuk kalkulasi & autosave
        document.getElementById('exam_duration').addEventListener('input', () => {
            updateAllPreviews();
            triggerAutosave();
        });
        document.getElementById('scheduleContainer').addEventListener('input', (e) => {
            if (e.target.classList.contains('schedule-date')) {
                const slot = e.target.closest('.schedule-slot');
                if (e.target.value) {
                    clearFieldInvalid(e.target, slot?.querySelector('.schedule-date-error'));
                }
            }
            if (e.target.classList.contains('schedule-time')) {
                const slot = e.target.closest('.schedule-slot');
                if (e.target.value) {
                    clearFieldInvalid(e.target, slot?.querySelector('.schedule-time-error'));
                }
            }
            updateAllPreviews();
            triggerAutosave();
        });
        document.getElementById('scheduleContainer').addEventListener('change', (e) => {
            if (e.target.classList.contains('schedule-date')) {
                const slot = e.target.closest('.schedule-slot');
                if (e.target.value) {
                    clearFieldInvalid(e.target, slot?.querySelector('.schedule-date-error'));
                }
            }
            if (e.target.classList.contains('schedule-time')) {
                const slot = e.target.closest('.schedule-slot');
                if (e.target.value) {
                    clearFieldInvalid(e.target, slot?.querySelector('.schedule-time-error'));
                }
            }
            updateAllPreviews();
            triggerAutosave();
        });

        document.getElementById('examForm').addEventListener('input', triggerAutosave);
        document.getElementById('examForm').addEventListener('change', triggerAutosave);

        // Tambah slot baru
        document.getElementById('addScheduleBtn').addEventListener('click', function() {
            const slots = document.querySelectorAll('.schedule-slot');
            let nextTime = '07:30';
            let nextDate = getTodayString();

            if (slots.length > 0) {
                const lastSlot = slots[slots.length - 1];
                const lastDateInput = lastSlot.querySelector('.schedule-date');
                const lastTimeInput = lastSlot.querySelector('.schedule-time');
                if (lastDateInput && lastDateInput.value) nextDate = lastDateInput.value;
                if (lastTimeInput && lastTimeInput.value) nextTime = lastTimeInput.value;
            }

            renderScheduleSlot(scheduleIndex++, nextDate, nextTime);
            renumberSlots();
            updateAllPreviews();
            triggerAutosave();
        });

        // Submit form validation
        document.getElementById('examForm').addEventListener('submit', function(e) {
            if (!validateStep1()) {
                e.preventDefault();
                goToStep(1);
                return false;
            }
            if (!validateStep2()) {
                e.preventDefault();
                goToStep(2);
                return false;
            }

            // Hapus draft setelah submit berhasil
            localStorage.removeItem(DRAFT_STORAGE_KEY);
            localStorage.removeItem('exambro_exam_create_draft_v1');
        });

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
