@extends('layouts.admin')
@section('title', 'Tambah Ujian')

@section('content')
<style>
    .slot-class-grid[data-active-grade="10"] .slot-class-card:not([data-grade="10"]) { display: none !important; }
    .slot-class-grid[data-active-grade="11"] .slot-class-card:not([data-grade="11"]) { display: none !important; }
    .slot-class-grid[data-active-grade="12"] .slot-class-card:not([data-grade="12"]) { display: none !important; }
</style>
<div class="max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Jadwal Ujian Baru</h2>
                <!-- Badge Status Autosave -->
                <div id="autosaveStatusBadge" class="hidden items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium transition-all">
                    <span id="autosaveStatusDot" class="w-2 h-2 rounded-full"></span>
                    <span id="autosaveStatusText">Draft tersimpan</span>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi informasi soal, pembagian sesi kelas, dan aturan keamanan ujian.</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <button type="button" id="clearDraftBtn" onclick="clearExamDraft(true)" class="hidden items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-all shadow-2xs" title="Hapus draf yang tersimpan dan kosongkan formulir">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset Draf</span>
            </button>
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium text-slate-600 bg-white border border-slate-200/80 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-2xs">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Banner Pemulihan Draf Autosave -->
    <div id="draftRestoredBanner" class="hidden mb-6 p-4 rounded-2xl bg-indigo-50/90 border border-indigo-200 text-indigo-900 flex items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="check" class="w-4 h-4"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-indigo-950">Draf Ujian Dipulihkan Otomatis</div>
                <div class="text-[11px] text-indigo-700/80" id="draftRestoredTimeText">Data input dan jadwal sesi terakhir Anda berhasil dipulihkan dari penyimpanan browser.</div>
            </div>
        </div>
        <button type="button" onclick="dismissDraftBanner()" class="text-xs font-semibold px-2.5 py-1 text-indigo-700 hover:text-indigo-900 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors shrink-0">
            Tutup
        </button>
    </div>

    <!-- STEPPER PROGRESS BAR -->
    <div class="mb-8 bg-white p-3 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="grid grid-cols-3 gap-2 sm:gap-4 relative">
            <!-- Step 1 Button -->
            <button type="button" onclick="handleStepTabClick(1)" id="step-tab-1" class="step-tab flex items-center gap-2 sm:gap-3 p-2 sm:p-2.5 rounded-xl transition-all text-left bg-indigo-50/80 text-indigo-900 border border-indigo-200/70 shadow-2xs">
                <div class="step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-2xs transition-all">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="step-sub text-[10px] font-semibold text-indigo-600 uppercase tracking-wider block">Langkah 1</span>
                    <span class="step-label text-xs sm:text-sm font-bold text-indigo-950 truncate block">Info & Soal</span>
                </div>
            </button>

            <!-- Step 2 Button -->
            <button type="button" onclick="handleStepTabClick(2)" id="step-tab-2" class="step-tab flex items-center gap-2 sm:gap-3 p-2 sm:p-2.5 rounded-xl transition-all text-left bg-slate-50/70 text-slate-500 hover:bg-slate-100/80 border border-transparent">
                <div class="step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 shadow-2xs transition-all">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="step-sub text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Langkah 2</span>
                    <span class="step-label text-xs sm:text-sm font-medium text-slate-600 truncate block">Jadwal & Kelas</span>
                </div>
            </button>

            <!-- Step 3 Button -->
            <button type="button" onclick="handleStepTabClick(3)" id="step-tab-3" class="step-tab flex items-center gap-2 sm:gap-3 p-2 sm:p-2.5 rounded-xl transition-all text-left bg-slate-50/70 text-slate-500 hover:bg-slate-100/80 border border-transparent">
                <div class="step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 shadow-2xs transition-all">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span class="step-sub text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Langkah 3</span>
                    <span class="step-label text-xs sm:text-sm font-medium text-slate-600 truncate block">Review & Terbit</span>
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
        <div id="step-panel-1" class="step-panel space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 shadow-2xs">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Ujian & Kuesioner Soal</h3>
                        <p class="text-xs text-slate-500">Tentukan nama ujian dan cantumkan tautan Google Form kuesioner soal.</p>
                    </div>
                </div>

                <!-- Judul Ujian -->
                <div>
                    <label for="input_title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Judul Ujian <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="title" id="input_title" value="{{ old('title') }}" required 
                               class="w-full bg-slate-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" 
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
                    <label for="input_google_form_url" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Tautan Google Form Soal <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-500">
                            <i data-lucide="link" class="w-4 h-4"></i>
                        </div>
                        <input type="url" name="google_form_url" id="input_google_form_url" value="{{ old('google_form_url') }}" required 
                               class="w-full bg-slate-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-900 font-mono placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" 
                               placeholder="https://docs.google.com/forms/d/e/.../viewform">
                    </div>
                    <div id="error_google_form_url" class="hidden text-xs text-rose-500 font-semibold mt-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Tautan Google Form wajib diisi.</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 mt-1.5 flex-wrap">
                        <p class="text-[11px] text-slate-400 flex items-center gap-1">
                            <i data-lucide="info" class="w-3 h-3 text-slate-400 shrink-0"></i>
                            Pastikan form disetel terbuka tanpa perlu izin login khusus editor.
                        </p>
                        <button type="button" onclick="testFormUrl()" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1 hover:underline">
                            <span>Uji Buka Form</span>
                            <i data-lucide="external-link" class="w-3 h-3"></i>
                        </button>
                    </div>
                    @error('google_form_url') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Deskripsi / Petunjuk -->
                <div>
                    <label for="input_description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Petunjuk Pengerjaan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <textarea name="description" id="input_description" rows="3" 
                              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" 
                              placeholder="Tuliskan catatan tata tertib atau petunjuk pengerjaan bagi siswa (misal: Kerjakan dengan teliti, dilarang membuka catatan)...">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Navigasi Langkah 1 -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.exams.index') }}" class="px-4 py-2.5 text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition-colors shadow-2xs">
                    Batal
                </a>
                <button type="button" onclick="validateAndGoToStep(2)" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-indigo-500/25 transition-all active:scale-[0.98]">
                    <span>Lanjut ke Jadwal & Kelas</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LANGKAH 2: JADWAL SESI & KELAS TARGET                         -->
        <!-- ============================================================= -->
        <div id="step-panel-2" class="step-panel hidden space-y-6">
            <!-- Card Gabungan: Durasi Standar & Sesi Jadwal -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
                <!-- Bagian 1: Durasi Standar Global -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 shadow-2xs">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Durasi Standar Ujian <span class="text-rose-500">*</span></h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Setara <strong id="preview_duration_jp_text" class="text-indigo-700 font-semibold">2 Jam Pelajaran (JP)</strong> — 1 JP = 35 menit.
                                </p>
                            </div>
                        </div>
                        <!-- Badge Biru di Sisi Kanan -->
                        <div class="self-start sm:self-auto shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100/70 text-indigo-700 text-xs font-bold border border-indigo-200/60 shadow-2xs">
                                <span id="preview_duration_pill">{{ old('duration', 70) }} Menit</span>
                                <span class="text-indigo-400">•</span>
                                <span id="preview_duration_jp_pill">2 JP</span>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-3 border-t border-slate-100">
                        <!-- Kiri: Input durasi fleksibel -->
                        <div class="relative flex-1">
                            <input type="number" name="duration" id="exam_duration" required min="1" value="{{ old('duration', 70) }}" 
                                   class="w-full bg-slate-50/50 border border-slate-200 rounded-xl pl-4 pr-16 py-2 text-sm text-slate-900 font-bold focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 shadow-2xs transition-all">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-xs font-bold text-slate-400 uppercase tracking-wider">MENIT</span>
                        </div>

                        <!-- Kanan: Pilihan 1, 2, 3 JP -->
                        <div class="grid grid-cols-3 sm:flex items-center gap-2 shrink-0">
                            <button type="button" data-duration="35" onclick="setExamDuration(35)" class="duration-preset-btn w-full sm:w-28 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 rounded-xl transition-all shadow-2xs active:scale-95 text-center">1 JP (35m)</button>
                            <button type="button" data-duration="70" onclick="setExamDuration(70)" class="duration-preset-btn w-full sm:w-28 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 rounded-xl transition-all shadow-2xs active:scale-95 text-center">2 JP (70m)</button>
                            <button type="button" data-duration="105" onclick="setExamDuration(105)" class="duration-preset-btn w-full sm:w-28 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 rounded-xl transition-all shadow-2xs active:scale-95 text-center">3 JP (105m)</button>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Daftar Sesi Jadwal (Di dalam card yang sama) -->
                <div class="pt-6 border-t border-slate-100 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100 shadow-2xs">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Daftar Sesi Jadwal <span class="text-rose-500">*</span></h3>
                                <p class="text-xs text-slate-500 mt-0.5">Tiap sesi hanya akan muncul pada akun siswa dari kelas yang dipilih pada sesi tersebut.</p>
                            </div>
                        </div>
                        <button type="button" id="addScheduleBtn" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-100 transition-colors shrink-0 shadow-2xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Sesi Baru</span>
                        </button>
                    </div>

                    @error('schedules') <span class="text-rose-500 text-xs block">{{ $message }}</span> @enderror

                    <!-- Schedule Container (Rendered by JS) -->
                    <div id="scheduleContainer" class="space-y-4">
                        <!-- Schedule slots will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Navigasi Langkah 2 -->
            <div class="flex items-center justify-between pt-2">
                <button type="button" onclick="goToStep(1)" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition-colors shadow-2xs">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali</span>
                </button>
                <button type="button" onclick="validateAndGoToStep(3)" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-indigo-500/25 transition-all active:scale-[0.98]">
                    <span>Lanjut ke Review & Keamanan</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LANGKAH 3: KEAMANAN, STATUS & REVIEW AKHIR                    -->
        <!-- ============================================================= -->
        <div id="step-panel-3" class="step-panel hidden space-y-6">
            <!-- Pengaturan Keamanan & Status -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
                <!-- Toleransi Pelanggaran -->
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100 shadow-2xs">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Batas Toleransi Keluar (Max Violation) <span class="text-rose-500">*</span></h3>
                                <p class="text-xs text-slate-500 mt-0.5">Tentukan batas toleransi minimize aplikasi sebelum ujian siswa terkunci otomatis.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200/80 shrink-0 self-start sm:self-auto transition-colors" id="violation_badge">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Mode Standar (3x Keluar)
                        </span>
                    </div>

                    <!-- 3 Preset Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button type="button" onclick="selectViolationPreset(1)" id="preset_violation_1" class="violation-preset-card p-4 rounded-2xl border text-left transition-all border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">1x Keluar</span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 border border-rose-100">Ketat</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Langsung terkunci saat pertama kali minimize aplikasi.</p>
                        </button>

                        <button type="button" onclick="selectViolationPreset(3)" id="preset_violation_3" class="violation-preset-card p-4 rounded-2xl border text-left transition-all border-indigo-500 bg-indigo-50/50 ring-2 ring-indigo-500/20 shadow-xs cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-base sm:text-lg font-bold text-indigo-900">3x Keluar</span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 border border-indigo-200/60">Standar</span>
                            </div>
                            <p class="text-xs text-indigo-700/80 mt-1.5 leading-relaxed">Rekomendasi resmi SMKN 1 Tirtamulya untuk ujian formal.</p>
                        </button>

                        <button type="button" onclick="selectViolationPreset(5)" id="preset_violation_5" class="violation-preset-card p-4 rounded-2xl border text-left transition-all border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <span class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">5x Keluar</span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100">Longgar</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Toleransi tinggi, cocok untuk simulasi & kendala teknis.</p>
                        </button>
                    </div>

                    <!-- Custom Stepper Row -->
                    <div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <i data-lucide="sliders" class="w-4 h-4 text-slate-400"></i>
                            <span>Atau sesuaikan angka manual:</span>
                        </div>

                        <div class="flex items-center gap-2 self-center sm:self-auto">
                            <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-2xs">
                                <button type="button" onclick="stepViolation(-1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 active:bg-slate-200 font-bold transition-colors">
                                    −
                                </button>
                                <input type="number" name="max_violation" id="max_violation" min="1" max="20" value="{{ old('max_violation', 3) }}" required
                                       oninput="handleViolationInput(this.value)"
                                       class="w-12 text-center font-bold text-slate-900 text-sm focus:outline-none py-1">
                                <button type="button" onclick="stepViolation(1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 active:bg-slate-200 font-bold transition-colors">
                                    +
                                </button>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">Kali Keluar</span>
                        </div>
                    </div>
                </div>

                <!-- Status Publikasi Ujian -->
                <div class="pt-5 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-2xs">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Status Publikasi Ujian <span class="text-rose-500">*</span></h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pilih apakah jadwal langsung tayang di aplikasi siswa atau disimpan sebagai draf.</p>
                        </div>
                    </div>

                    <input type="hidden" name="status" id="status_input" value="{{ old('status', 'active') }}">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div onclick="selectExamStatus('active')" id="card_status_active" class="status-card p-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50/40 cursor-pointer transition-all flex items-start gap-3">
                            <div class="w-4 h-4 rounded-full border-2 border-emerald-600 bg-emerald-600 mt-0.5 flex items-center justify-center shrink-0">
                                <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-emerald-950 flex items-center gap-1.5">
                                    Aktif (Tersedia untuk Siswa)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                </div>
                                <p class="text-xs text-emerald-800/80 mt-1">Ujian langsung muncul di aplikasi siswa sesuai dengan jadwal masing-masing kelas.</p>
                            </div>
                        </div>

                        <div onclick="selectExamStatus('inactive')" id="card_status_inactive" class="status-card p-4 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100 cursor-pointer transition-all flex items-start gap-3">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-400 mt-0.5 flex items-center justify-center shrink-0">
                                <div class="w-1.5 h-1.5 rounded-full bg-transparent"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-700">Nonaktif (Simpan Draft)</div>
                                <p class="text-xs text-slate-500 mt-1">Disimpan sementara. Otomatis aktif saat waktu ujian tiba.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pre-Flight Review & Konfirmasi -->
            <div class="bg-indigo-50/70 rounded-2xl border border-indigo-100 shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-indigo-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center shadow-2xs">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-indigo-950">Ringkasan Konfirmasi Ujian</h4>
                            <p class="text-[11px] text-indigo-700/80">Periksa kembali detail sebelum menyimpan jadwal ke sistem.</p>
                        </div>
                    </div>
                    <span id="review_status_pill" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Siap Diterbitkan
                    </span>
                </div>

                <!-- Review Content Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="bg-white p-3.5 rounded-xl border border-indigo-100/90 shadow-2xs space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Judul Ujian:</div>
                        <div id="review_title" class="font-bold text-slate-900 text-sm truncate">—</div>
                        <div class="text-[11px] text-slate-500 truncate" id="review_url">—</div>
                    </div>

                    <div class="bg-white p-3.5 rounded-xl border border-indigo-100/90 shadow-2xs space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Keamanan & Durasi:</div>
                        <div class="flex items-center gap-2 font-bold text-slate-900">
                            <span id="review_duration">90 Menit (2 JP)</span>
                            <span class="text-slate-300">•</span>
                            <span id="review_violation" class="text-indigo-600">Max 3x Keluar</span>
                        </div>
                        <div class="text-[11px] text-slate-500" id="review_sessions_count">Total 1 Sesi Terdaftar</div>
                    </div>
                </div>

                <!-- Review Sesi List -->
                <div class="space-y-2">
                    <div class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider">Jadwal Sesi & Kelas Terdaftar:</div>
                    <div id="review_schedule_list" class="space-y-2">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Navigasi Langkah 3 & Submit -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                <button type="button" onclick="goToStep(2)" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition-colors shadow-2xs">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Jadwal</span>
                </button>
                <button type="submit" id="submitExamBtn" class="inline-flex items-center justify-center gap-2 px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-600/20 transition-all active:scale-[0.98]">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    <span>Simpan & Jadwalkan Ujian</span>
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
                // Active step
                tab.className = "step-tab flex items-center gap-2 sm:gap-3 p-2 sm:p-2.5 rounded-xl transition-all text-left bg-indigo-50/80 text-indigo-900 border border-indigo-200/70 shadow-2xs";
                if (iconBox) iconBox.className = "step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-2xs transition-all";
                if (sub) sub.className = "step-sub text-[10px] font-bold text-indigo-600 uppercase tracking-wider block";
                if (label) label.className = "step-label text-xs sm:text-sm font-bold text-indigo-950 truncate block";
            } else if (s < step) {
                // Completed step
                tab.className = "step-tab flex items-center gap-2 sm:gap-3 p-2 sm:p-2.5 rounded-xl transition-all text-left bg-emerald-50/60 text-emerald-900 border border-emerald-200/60 shadow-2xs";
                if (iconBox) iconBox.className = "step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs transition-all";
                if (sub) sub.className = "step-sub text-[10px] font-semibold text-emerald-600 uppercase tracking-wider block";
                if (label) label.className = "step-label text-xs sm:text-sm font-semibold text-emerald-950 truncate block";
            } else {
                // Upcoming step
                tab.className = "step-tab flex items-center gap-2 sm:gap-3 p-2 sm:p-2.5 rounded-xl transition-all text-left bg-slate-50/70 text-slate-500 hover:bg-slate-100/80 border border-transparent";
                if (iconBox) iconBox.className = "step-icon-box w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 shadow-2xs transition-all";
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
        el.classList.remove('border-slate-200', 'focus:border-indigo-500', 'focus:ring-indigo-500/15');
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
                <label class="slot-class-card group flex items-center gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none ${isChecked ? 'bg-indigo-50/80 border-indigo-500 ring-1 ring-indigo-500/30 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/80'}" data-classname="${lowerName}" data-grade="${grade}">
                    <input type="checkbox" name="schedules[${idx}][classes][]" value="${c.id}" ${isChecked ? 'checked' : ''} 
                           class="slot-class-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 shrink-0"
                           onchange="onSlotClassChanged(${idx}); triggerAutosave();">
                    <span class="text-xs ${isChecked ? 'font-bold text-indigo-950' : 'font-medium text-slate-700 group-hover:text-slate-900'} transition-colors">${c.name}</span>
                </label>
            `;
        });

        const slot = document.createElement('div');
        slot.className = 'schedule-slot p-4 sm:p-5 bg-slate-50/80 border border-slate-200/90 rounded-2xl space-y-4 shadow-2xs transition-all';
        slot.setAttribute('data-index', idx);
        slot.innerHTML = `
            <!-- Header Sesi -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-bold shrink-0 schedule-num">${num}</div>
                    <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Jadwal Sesi <span class="slot-title-num">${num}</span></span>
                </div>
                <!-- Jam & Aksi di Sisi Kanan -->
                <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold border border-indigo-100 schedule-preview shadow-2xs">—</span>
                    <button type="button" onclick="removeScheduleSlot(this)" class="remove-slot-btn inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1 rounded-lg transition-colors font-semibold ${num === 1 ? 'hidden' : ''}">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Hapus Sesi</span>
                    </button>
                </div>
            </div>

            <!-- Baris 1: Waktu Pelaksanaan (Tanggal & Jam Mulai) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Ujian <span class="text-rose-500">*</span></label>
                    <input type="date" name="schedules[${idx}][date]" value="${dateVal}" required class="schedule-date w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 shadow-2xs transition-all">
                    <div class="schedule-date-error hidden text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Tanggal ujian wajib diisi.</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="schedules[${idx}][time]" value="${defaultTime}" required class="schedule-time w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 shadow-2xs transition-all">
                    <div class="schedule-time-error hidden text-xs text-rose-500 font-semibold mt-1 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Jam mulai wajib diisi.</span>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Durasi Sesi (Kiri: Status, Kanan: Pilihan Durasi) -->
            <div class="p-3.5 bg-slate-100/70 border border-slate-200/80 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 shrink-0">
                    <div class="w-7 h-7 rounded-lg bg-white text-slate-500 flex items-center justify-center shrink-0 border border-slate-200 shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-800">Durasi Sesi</span>
                            <span class="text-[10px] font-medium text-slate-400 bg-white px-1.5 py-0.2 rounded border border-slate-200/70">Opsional</span>
                        </div>
                        <div class="slot-dur-status text-xs text-slate-500 mt-0.5 font-medium">
                            Sesuai default (<span class="slot-default-hint">${globalDurationVal}m / ${formatJP(globalDurationVal)}</span>)
                        </div>
                    </div>
                </div>

                <!-- Kontrol Pilihan Durasi di Sisi Kanan -->
                <div class="flex items-center gap-2 shrink-0">
                    <div class="inline-flex p-1 bg-white border border-slate-200 rounded-xl shadow-2xs gap-1">
                        <button type="button" onclick="setSlotDuration(${idx}, '')" data-dur="" class="slot-dur-btn px-3 py-1 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900" title="Ikuti durasi standar">Default</button>
                        <button type="button" onclick="setSlotDuration(${idx}, 35)" data-dur="35" class="slot-dur-btn px-3 py-1 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900" title="1 JP (35 Menit)">1 JP</button>
                        <button type="button" onclick="setSlotDuration(${idx}, 70)" data-dur="70" class="slot-dur-btn px-3 py-1 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900" title="2 JP (70 Menit)">2 JP</button>
                        <button type="button" onclick="setSlotDuration(${idx}, 105)" data-dur="105" class="slot-dur-btn px-3 py-1 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900" title="3 JP (105 Menit)">3 JP</button>
                    </div>
                    
                    <div class="relative w-24 sm:w-28">
                        <input type="number" name="schedules[${idx}][duration]" value="${defaultDuration || ''}" min="1" placeholder="Kustom" class="schedule-duration w-full bg-white border border-slate-200 rounded-xl pl-2.5 pr-6 py-1 text-xs text-slate-900 font-bold focus:outline-none transition-all shadow-2xs" oninput="updateAllPreviews(); triggerAutosave();" title="Durasi menit kustom">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-[10px] font-bold text-slate-400 uppercase">m</span>
                    </div>
                </div>
            </div>

            <!-- Baris 3: Target Kelas untuk Sesi Ini -->
            <div class="pt-1 space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">Target Kelas Sesi Ini <span class="text-rose-500">*</span></label>
                        <span class="slot-class-count text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200">Wajib Pilih Kelas</span>
                    </div>

                    <!-- Filter Tingkat & Search Box -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <!-- Grade Pills (Semua, X, XI, XII) -->
                        <div class="inline-flex p-0.5 bg-slate-200/60 rounded-lg text-[11px] font-semibold text-slate-600">
                            <button type="button" onclick="filterSlotGrade(${idx}, 'all', this)" class="grade-filter-btn px-2 py-0.5 rounded-md bg-white text-indigo-700 shadow-2xs">Semua</button>
                            <button type="button" onclick="filterSlotGrade(${idx}, '10', this)" class="grade-filter-btn px-2 py-0.5 rounded-md hover:text-slate-900">Kelas X</button>
                            <button type="button" onclick="filterSlotGrade(${idx}, '11', this)" class="grade-filter-btn px-2 py-0.5 rounded-md hover:text-slate-900">Kelas XI</button>
                            <button type="button" onclick="filterSlotGrade(${idx}, '12', this)" class="grade-filter-btn px-2 py-0.5 rounded-md hover:text-slate-900">Kelas XII</button>
                        </div>

                        <!-- Search Box Mini -->
                        <div class="relative w-36">
                            <input type="text" oninput="filterSlotClasses(${idx}, this.value)" placeholder="Cari..." class="w-full px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 shadow-2xs">
                        </div>

                        <button type="button" onclick="toggleAllSlotClasses(${idx})" class="slot-toggle-btn text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-white border border-indigo-200 px-2.5 py-1 rounded-lg transition-all shadow-2xs">
                            Pilih Semua
                        </button>
                    </div>
                </div>

                <!-- Grid Checkboxes Kelas Interaktif -->
                <div class="slot-class-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 p-3 bg-white border border-slate-200/90 rounded-xl max-h-48 overflow-y-auto transition-all">
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
            b.className = "grade-filter-btn px-2 py-0.5 rounded-md hover:text-slate-900 text-slate-600";
        });
        btn.className = "grade-filter-btn px-2 py-0.5 rounded-md bg-white text-indigo-700 font-bold shadow-2xs";

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
                card.className = "slot-class-card group flex items-center gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none bg-indigo-50/80 border-indigo-500 ring-1 ring-indigo-500/30 shadow-2xs";
                if (span) span.className = "text-xs font-bold text-indigo-950 transition-colors";
            } else {
                card.className = "slot-class-card group flex items-center gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/80";
                if (span) span.className = "text-xs font-medium text-slate-700 group-hover:text-slate-900 transition-colors";
            }
        });

        if (countBadge) {
            if (checked.length > 0) {
                countBadge.className = 'slot-class-count text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200';
                countBadge.textContent = `${checked.length} Kelas Dipilih`;
            } else {
                countBadge.className = 'slot-class-count text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200';
                countBadge.textContent = `Wajib Pilih Kelas`;
            }
        }

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
                btn.className = 'duration-preset-btn w-full sm:w-28 py-2 text-xs font-bold rounded-xl transition-all bg-indigo-600 text-white shadow-xs text-center';
            } else {
                btn.className = 'duration-preset-btn w-full sm:w-28 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 rounded-xl transition-all shadow-2xs active:scale-95 text-center';
            }
        });

        const slots = document.querySelectorAll('.schedule-slot');

        slots.forEach((slot, i) => {
            const dateInput = slot.querySelector('.schedule-date');
            const timeInput = slot.querySelector('.schedule-time');
            const durationInput = slot.querySelector('.schedule-duration');
            const previewEl = slot.querySelector('.schedule-preview');

            const customDur = durationInput && durationInput.value ? parseInt(durationInput.value) : null;
            const effectiveDur = (customDur && customDur > 0) ? customDur : durationVal;
            const durStatusEl = slot.querySelector('.slot-dur-status');

            if (durStatusEl) {
                if (customDur && customDur > 0) {
                    durStatusEl.innerHTML = `<span class="text-amber-700 font-bold inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Khusus sesi ini: ${customDur}m (${formatJP(customDur)})</span>`;
                } else {
                    durStatusEl.innerHTML = `Sesuai default (<span class="slot-default-hint">${durationVal}m / ${formatJP(durationVal)}</span>)`;
                }
            }

            // Update status tombol preset durasi sesi
            const durButtons = slot.querySelectorAll('.slot-dur-btn');
            durButtons.forEach(b => {
                const bDur = b.getAttribute('data-dur');
                const isDefaultBtn = bDur === '';
                const isActive = isDefaultBtn ? (!customDur) : (parseInt(bDur) === customDur);
                if (isActive) {
                    if (isDefaultBtn) {
                        b.className = 'slot-dur-btn px-3 py-1 text-xs font-bold rounded-lg transition-all bg-slate-800 text-white shadow-xs text-center';
                    } else {
                        b.className = 'slot-dur-btn px-3 py-1 text-xs font-bold rounded-lg transition-all bg-indigo-600 text-white shadow-xs text-center';
                    }
                } else {
                    b.className = 'slot-dur-btn px-3 py-1 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-center';
                }
            });

            const defaultHint = slot.querySelector('.slot-default-hint');
            if (defaultHint) {
                defaultHint.textContent = `${durationVal}m / ${formatJP(durationVal)}`;
            }

            if (!dateInput.value || !timeInput.value) {
                if (previewEl) previewEl.textContent = '—';
                return;
            }

            const startDT = new Date(`${dateInput.value}T${timeInput.value}`);
            const endDT = new Date(startDT.getTime() + effectiveDur * 60000);

            const sH = String(startDT.getHours()).padStart(2, '0');
            const sM = String(startDT.getMinutes()).padStart(2, '0');
            const eH = String(endDT.getHours()).padStart(2, '0');
            const eM = String(endDT.getMinutes()).padStart(2, '0');

            if (previewEl) {
                previewEl.textContent = customDur 
                    ? `${sH}:${sM}–${eH}:${eM} WIB (${customDur}m kustom)`
                    : `${sH}:${sM}–${eH}:${eM} WIB (${effectiveDur}m)`;
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
                statusPill.className = "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200";
                statusPill.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Siap Diterbitkan (Aktif)';
            } else {
                statusPill.className = "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200";
                statusPill.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Disimpan Draft (Nonaktif)';
            }
        }

        const slots = document.querySelectorAll('.schedule-slot');
        const countEl = document.getElementById('review_sessions_count');
        if (countEl) countEl.textContent = `Total ${slots.length} Sesi Terdaftar`;

        const listContainer = document.getElementById('review_schedule_list');
        if (!listContainer) return;

        let html = '';
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
                timeString = `${sH}:${sM}–${eH}:${eM} WIB`;
            }

            const classNames = Array.from(checkedBoxes).map(cb => {
                const label = cb.closest('label');
                return label ? label.querySelector('span').textContent.trim() : '';
            }).filter(Boolean);

            html += `
                <div class="p-3 rounded-xl bg-white border border-indigo-100/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-2xs">${i + 1}</span>
                        <div>
                            <span class="font-bold text-slate-900">${dateString}</span>
                            <span class="text-slate-300 mx-1">•</span>
                            <span class="font-mono font-bold text-indigo-600">${timeString}</span>
                            <span class="text-xs text-slate-400 ml-1.5">(${effectiveDur}m)</span>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-slate-700 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 self-start sm:self-auto">
                        <i data-lucide="users" class="w-3 h-3 inline-block mr-1 text-indigo-500"></i>
                        ${classNames.length > 0 ? `${classNames.length} Kelas: ${classNames.slice(0, 3).join(', ')}${classNames.length > 3 ? '...' : ''}` : '<span class="text-rose-600">Belum ada kelas</span>'}
                    </div>
                </div>
            `;
        });

        listContainer.innerHTML = html;
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
            activeCard.className = "status-card p-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50/40 cursor-pointer transition-all flex items-start gap-3";
            inactiveCard.className = "status-card p-4 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100 cursor-pointer transition-all flex items-start gap-3";
            activeCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-emerald-600 bg-emerald-600 mt-0.5 flex items-center justify-center shrink-0";
            inactiveCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-slate-400 mt-0.5 flex items-center justify-center shrink-0";
        } else {
            inactiveCard.className = "status-card p-4 rounded-2xl border-2 border-indigo-500 bg-indigo-50/40 cursor-pointer transition-all flex items-start gap-3";
            activeCard.className = "status-card p-4 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100 cursor-pointer transition-all flex items-start gap-3";
            inactiveCard.querySelector('div').className = "w-4 h-4 rounded-full border-2 border-indigo-600 bg-indigo-600 mt-0.5 flex items-center justify-center shrink-0";
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
                el.className = "violation-preset-card p-4 rounded-2xl border text-left transition-all border-indigo-500 bg-indigo-50/50 ring-2 ring-indigo-500/20 shadow-xs cursor-pointer group";
                const title = el.querySelector('span.font-bold');
                if (title) title.className = "text-base sm:text-lg font-bold text-indigo-900";
            } else {
                el.className = "violation-preset-card p-4 rounded-2xl border text-left transition-all border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs cursor-pointer group";
                const title = el.querySelector('span.font-bold');
                if (title) title.className = "text-base sm:text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors";
            }
        });

        const badge = document.getElementById('violation_badge');
        if (badge) {
            if (num === 1) badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Mode Ketat (1x Keluar)';
            else if (num === 3) badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Mode Standar (3x Keluar)';
            else if (num === 5) badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Mode Longgar (5x Keluar)';
            else badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Mode Kustom (${num}x Keluar)`;
        }
    }

    // ==========================================
    // AUTOSAVE DRAFT FORM UJIAN
    // ==========================================
    const DRAFT_STORAGE_KEY = 'exambro_exam_create_draft_v2';
    let autosaveTimeout = null;

    function getFormDraftData() {
        const title = document.querySelector('input[name="title"]')?.value || '';
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
