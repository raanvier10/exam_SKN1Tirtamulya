@extends('layouts.admin')
@section('title', 'Tambah Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
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
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi rincian jadwal, kelas target per sesi, dan tautan Google Form soal.</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <button type="button" id="clearDraftBtn" onclick="clearExamDraft(true)" class="hidden items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-all shadow-2xs" title="Hapus draf yang tersimpan dan kosongkan formulir">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Reset Draf</span>
            </button>
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium text-slate-600 bg-white border border-slate-200/80 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-2xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Banner Pemulihan Draf Autosave -->
    <div id="draftRestoredBanner" class="hidden mb-6 p-4 rounded-2xl bg-indigo-50/90 border border-indigo-200 text-indigo-900 flex items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
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

    <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm" class="space-y-6">
        @csrf

        <!-- Card 1: Informasi Dasar & Soal -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">1</div>
                <h3 class="text-base font-bold text-slate-900">Informasi & Kuesioner Soal</h3>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Judul Ujian <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" placeholder="Contoh: PTS Matematika Wajib">
                @error('title') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Deskripsi / Petunjuk (Opsional)</label>
                <textarea name="description" rows="2" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" placeholder="Petunjuk pengerjaan (misal: Tutup buku, kalkulator dilarang)...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Google Form URL <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <input type="url" name="google_form_url" value="{{ old('google_form_url') }}" required class="w-full bg-slate-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-900 font-mono placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" placeholder="https://docs.google.com/forms/d/e/.../viewform">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Pastikan link Google Form dapat diakses siswa tanpa perlu login edit akun.</p>
                @error('google_form_url') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Card 2: Jadwal Sesi & Kelas Target Per Sesi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">2</div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Jadwal Sesi & Kelas Target</h3>
                    <p class="text-xs text-slate-500">Tentukan jadwal dan kelas spesifik untuk setiap sesi ujian.</p>
                </div>
            </div>

            <!-- Durasi (berlaku untuk semua sesi ujian pada form ini) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/90 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100/80 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <label for="exam_duration" class="text-xs font-bold text-slate-800 uppercase tracking-wider">Durasi Per Sesi <span class="text-rose-500">*</span></label>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-50 border border-indigo-200/80 text-[11px] font-bold text-indigo-700">
                            <span id="preview_duration_pill">{{ old('duration', 90) }} Menit</span>
                            <span class="text-indigo-400">•</span>
                            <span id="preview_duration_jp_pill">2 JP</span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 sm:pl-9">
                        Setara <strong id="preview_duration_jp_text" class="text-indigo-700 font-semibold">2 Jam Pelajaran (JP)</strong> — 1 JP = 45 menit. Dapat disesuaikan per sesi jika ada jadwal kelas dengan JP berbeda di bawah.
                    </p>
                    @error('duration') <span class="text-rose-500 text-xs sm:pl-9 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap sm:pl-9 md:pl-0">
                    <!-- Input dengan suffix satuan Menit -->
                    <div class="relative w-36 sm:w-40 shrink-0">
                        <input type="number" name="duration" id="exam_duration" required min="1" value="{{ old('duration', 90) }}" class="w-full bg-white border border-slate-200 rounded-xl pl-3.5 pr-14 py-2 text-sm text-slate-900 font-semibold focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all text-center">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-xs font-semibold text-slate-400 uppercase tracking-wider">menit</span>
                    </div>

                    <!-- Preset Chips dengan JP -->
                    <div class="inline-flex items-center p-1 bg-white border border-slate-200 rounded-xl gap-1 shadow-2xs flex-wrap">
                        <button type="button" data-duration="45" onclick="setExamDuration(45)" class="duration-preset-btn px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="1 Jam Pelajaran (45 Menit)">1 JP (45m)</button>
                        <button type="button" data-duration="60" onclick="setExamDuration(60)" class="duration-preset-btn px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">60m</button>
                        <button type="button" data-duration="90" onclick="setExamDuration(90)" class="duration-preset-btn px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="2 Jam Pelajaran (90 Menit)">2 JP (90m)</button>
                        <button type="button" data-duration="120" onclick="setExamDuration(120)" class="duration-preset-btn px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">120m</button>
                        <button type="button" data-duration="135" onclick="setExamDuration(135)" class="duration-preset-btn px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="3 Jam Pelajaran (135 Menit)">3 JP (135m)</button>
                    </div>
                </div>
            </div>

            <!-- Multi-Schedule Slots -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Daftar Sesi Jadwal <span class="text-rose-500">*</span></label>
                        <p class="text-xs text-slate-500 mt-0.5">Tiap sesi hanya akan muncul pada akun siswa dari kelas yang dipilih pada sesi tersebut.</p>
                    </div>
                    <button type="button" id="addScheduleBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Jadwal Sesi
                    </button>
                </div>

                @error('schedules') <span class="text-rose-500 text-xs block">{{ $message }}</span> @enderror

                <div id="scheduleContainer" class="space-y-4">
                    <!-- Schedule slots will be rendered here by JS -->
                </div>

                <!-- Quick time buttons -->
                <div class="flex items-center gap-1.5 pt-1 flex-wrap">
                    <span class="text-[10px] text-slate-400 font-semibold uppercase mr-1">Preset Jam Sesi Terakhir:</span>
                    <button type="button" onclick="setLastSlotTime('07:30')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">07:30</button>
                    <button type="button" onclick="setLastSlotTime('08:00')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">08:00</button>
                    <button type="button" onclick="setLastSlotTime('09:30')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">09:30</button>
                    <button type="button" onclick="setLastSlotTime('13:00')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">13:00</button>
                </div>
            </div>

            <!-- Live Schedule & Class Summary -->
            <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/60 border border-indigo-100/90 space-y-2.5">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Ringkasan Jadwal & Peserta</span>
                    <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full bg-white border border-indigo-200 text-xs font-bold text-indigo-700" id="scheduleCountBadge">1 Sesi</span>
                </div>
                <div id="scheduleSummaryList" class="space-y-2 text-xs">
                    <!-- Filled dynamically by JS -->
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-indigo-800/70 pt-0.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Siswa hanya akan melihat 1 jadwal ujian pada jam yang dialokasikan khusus untuk kelasnya.</span>
                </div>
            </div>

            <!-- Toleransi Pelanggaran -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Batas Toleransi Keluar (Max Violation) <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan batas toleransi minimize/pindah aplikasi sebelum ujian siswa terkunci otomatis.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200/80 shrink-0 self-start sm:self-auto transition-colors" id="violation_badge">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Mode Standar (3x Keluar)
                    </span>
                </div>

                <!-- 3 Preset Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button" onclick="selectViolationPreset(1)" id="preset_violation_1" class="violation-preset-card p-4 rounded-2xl border text-left transition-all border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs cursor-pointer group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">1x Keluar</span>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 border border-rose-100">Ketat</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Langsung terkunci saat pertama kali minimize aplikasi.</p>
                    </button>

                    <button type="button" onclick="selectViolationPreset(3)" id="preset_violation_3" class="violation-preset-card p-4 rounded-2xl border text-left transition-all border-indigo-500 bg-indigo-50/50 ring-2 ring-indigo-500/20 shadow-xs cursor-pointer group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-indigo-900">3x Keluar</span>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 border border-indigo-200/60">Standar</span>
                        </div>
                        <p class="text-xs text-indigo-700/80 mt-1.5 leading-relaxed">Rekomendasi resmi SMKN 1 Tirtamulya untuk ujian formal.</p>
                    </button>

                    <button type="button" onclick="selectViolationPreset(5)" id="preset_violation_5" class="violation-preset-card p-4 rounded-2xl border text-left transition-all border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs cursor-pointer group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">5x Keluar</span>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100">Longgar</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Toleransi tinggi, cocok untuk simulasi & kendala teknis.</p>
                    </button>
                </div>

                <!-- Custom Stepper Row -->
                <div class="mt-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>Atau sesuaikan angka manual:</span>
                    </div>

                    <div class="flex items-center gap-2 self-center sm:self-auto">
                        <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-2xs">
                            <button type="button" onclick="stepViolation(-1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 active:bg-slate-200 font-bold transition-colors">
                                −
                            </button>
                            <input type="number" name="max_violation" id="max_violation" min="1" max="20" value="{{ old('max_violation', 3) }}" required
                                   oninput="handleViolationInput(this.value)"
                                   class="w-14 text-center font-bold text-slate-900 text-base focus:outline-none py-1">
                            <button type="button" onclick="stepViolation(1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 active:bg-slate-200 font-bold transition-colors">
                                +
                            </button>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">Kali Keluar</span>
                    </div>
                </div>

                <div class="mt-2.5 flex items-start gap-2 text-xs text-slate-500 px-1">
                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Jika siswa keluar aplikasi melebihi <strong id="violation_live_text" class="text-indigo-700 font-bold">3 kali</strong>, ujian otomatis terkunci dan wajib dibuka oleh guru pengawas atau kurikulum.</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Status Publikasi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">3</div>
                <h3 class="text-base font-bold text-slate-900">Status Publikasi Ujian</h3>
            </div>

            <div>
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

        <!-- Tombol Aksi Bawah -->
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.exams.index') }}" class="px-5 py-2.5 text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-2xs text-center">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-indigo-500/25 transition-all duration-150 active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Simpan Jadwal Ujian
            </button>
        </div>
    </form>
</div>

<script>
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    // Master data kelas dari backend
    const allClasses = @json($classes);

    let scheduleIndex = 0; // counter untuk index unik

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
        const jp = num / 45;
        const formatted = Number.isInteger(jp) ? jp : parseFloat(jp.toFixed(1));
        return `${formatted} JP`;
    }

    function renderScheduleSlot(idx, defaultDate, defaultTime = '07:30', defaultDuration = '', selectedClassIds = []) {
        const container = document.getElementById('scheduleContainer');
        const num = container.querySelectorAll('.schedule-slot').length + 1;
        const dateVal = defaultDate || getTodayString();
        const globalDurationVal = parseInt(document.getElementById('exam_duration') ? document.getElementById('exam_duration').value : 90) || 90;

        let classCardsHtml = '';
        allClasses.forEach(c => {
            const isChecked = selectedClassIds.includes(c.id);
            classCardsHtml += `
                <label class="slot-class-card group flex items-center gap-2 p-2 bg-slate-50/70 border border-slate-200 rounded-lg cursor-pointer transition-all hover:border-indigo-300 select-none" data-classname="${c.name.toLowerCase()}">
                    <input type="checkbox" name="schedules[${idx}][classes][]" value="${c.id}" ${isChecked ? 'checked' : ''} 
                           class="slot-class-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4"
                           onchange="onSlotClassChanged(${idx}); triggerAutosave();">
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-700 transition-colors">${c.name}</span>
                </label>
            `;
        });

        const slot = document.createElement('div');
        slot.className = 'schedule-slot p-4 sm:p-5 bg-slate-50/80 border border-slate-200/90 rounded-2xl space-y-3.5 shadow-2xs transition-all';
        slot.setAttribute('data-index', idx);
        slot.innerHTML = `
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200/80">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-bold shrink-0 schedule-num">${num}</div>
                    <span class="text-xs font-bold text-slate-900 uppercase tracking-wide">Jadwal Sesi <span class="slot-title-num">${num}</span></span>
                    <span class="text-xs text-indigo-700 font-mono font-semibold bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-100 schedule-preview">—</span>
                </div>
                <button type="button" onclick="removeScheduleSlot(this)" class="remove-slot-btn inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1 rounded-lg transition-colors font-medium ${num === 1 ? 'hidden' : ''}">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Sesi
                </button>
            </div>

            <!-- Inputs Waktu & Durasi -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Ujian <span class="text-rose-500">*</span></label>
                    <input type="date" name="schedules[${idx}][date]" value="${dateVal}" required class="schedule-date w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="schedules[${idx}][time]" value="${defaultTime}" required class="schedule-time w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Durasi Khusus Sesi Ini</label>
                        <span class="slot-jp-badge text-[9px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">Ikut default</span>
                    </div>
                    <div class="relative">
                        <input type="number" name="schedules[${idx}][duration]" value="${defaultDuration || ''}" min="1" placeholder="Ikut default (${globalDurationVal}m)" class="schedule-duration w-full bg-white border border-slate-200 rounded-xl pl-3 pr-14 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium" oninput="updateAllPreviews(); triggerAutosave();">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-xs font-semibold text-slate-400 uppercase tracking-wider">menit</span>
                    </div>
                    <!-- Quick JP buttons for this slot -->
                    <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                        <span class="text-[9px] text-slate-400 font-semibold uppercase mr-0.5">Preset:</span>
                        <button type="button" onclick="setSlotDuration(${idx}, 45)" class="px-1.5 py-0.5 text-[10px] font-medium bg-white hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 border border-slate-200 rounded transition-colors" title="1 Jam Pelajaran">1 JP (45m)</button>
                        <button type="button" onclick="setSlotDuration(${idx}, 90)" class="px-1.5 py-0.5 text-[10px] font-medium bg-white hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 border border-slate-200 rounded transition-colors" title="2 Jam Pelajaran">2 JP (90m)</button>
                        <button type="button" onclick="setSlotDuration(${idx}, '')" class="px-1.5 py-0.5 text-[10px] font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded transition-colors">Default</button>
                    </div>
                </div>
            </div>

            <!-- Target Kelas untuk Slot Ini -->
            <div class="pt-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Target Kelas Sesi Ini <span class="text-rose-500">*</span></label>
                        <span class="slot-class-count text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">0 Kelas Dipilih</span>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        ${num > 1 ? `
                        <button type="button" onclick="copyClassesFromFirstSlot(${idx})" class="text-[11px] font-semibold text-slate-600 hover:text-indigo-600 bg-white hover:bg-indigo-50 border border-slate-200 px-2 py-1 rounded-lg transition-colors">
                            Salin Kelas Jadwal 1
                        </button>
                        ` : ''}
                        <button type="button" onclick="toggleAllSlotClasses(${idx})" class="slot-toggle-btn text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition-colors">
                            Pilih Semua Kelas
                        </button>
                    </div>
                </div>

                <!-- Search filter mini -->
                <div class="relative mb-2">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" oninput="filterSlotClasses(${idx}, this.value)" placeholder="Cari kelas untuk jadwal ini..." class="w-full pl-7 pr-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Grid Checkboxes Kelas -->
                <div class="slot-class-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 p-3 bg-white border border-slate-200/90 rounded-xl max-h-44 overflow-y-auto">
                    ${classCardsHtml}
                </div>
            </div>
        `;

        container.appendChild(slot);
        onSlotClassChanged(idx);
        updateAllPreviews();
    }

    function setSlotDuration(idx, val) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const durInput = slot.querySelector('.schedule-duration');
        if (durInput) {
            durInput.value = val;
            updateAllPreviews();
            triggerAutosave();
        }
    }

    // ==========================================
    // FITUR AUTOSAVE DRAFT FORM UJIAN
    // ==========================================
    const DRAFT_STORAGE_KEY = 'exambro_exam_create_draft_v1';
    let autosaveTimeout = null;

    function getFormDraftData() {
        const title = document.querySelector('input[name="title"]')?.value || '';
        const description = document.querySelector('textarea[name="description"]')?.value || '';
        const google_form_url = document.querySelector('input[name="google_form_url"]')?.value || '';
        const duration = document.querySelector('input[name="duration"]')?.value || '90';
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
            return false; // Jangan timpa jika ada error validasi backend
        }

        try {
            const raw = localStorage.getItem(DRAFT_STORAGE_KEY);
            if (!raw) return false;
            const data = JSON.parse(raw);
            if (!data || !Array.isArray(data.slots) || data.slots.length === 0) return false;

            if (data.title) document.querySelector('input[name="title"]').value = data.title;
            if (data.description) document.querySelector('textarea[name="description"]').value = data.description;
            if (data.google_form_url) document.querySelector('input[name="google_form_url"]').value = data.google_form_url;
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
                    timeText.textContent = `Draft ujian terakhir Anda (tersimpan pukul ${data.savedAt}) berhasil dipulihkan secara otomatis.`;
                }
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
        window.location.reload();
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Coba pulihkan draft autosave terlebih dahulu
        const restored = restoreExamDraft();
        if (!restored) {
            // Jika tidak ada draft tersimpan, render 1 slot awal default
            renderScheduleSlot(scheduleIndex++);
        }

        // Listener untuk kalkulasi & autosave
        document.getElementById('exam_duration').addEventListener('input', () => {
            updateAllPreviews();
            triggerAutosave();
        });
        document.getElementById('scheduleContainer').addEventListener('input', () => {
            updateAllPreviews();
            triggerAutosave();
        });
        document.getElementById('scheduleContainer').addEventListener('change', () => {
            updateAllPreviews();
            triggerAutosave();
        });

        // Pasang listener input pada form untuk autosave
        document.getElementById('examForm').addEventListener('input', triggerAutosave);
        document.getElementById('examForm').addEventListener('change', triggerAutosave);

        // Bersihkan draft saat submit berhasil
        document.getElementById('examForm').addEventListener('submit', function(e) {
            const slots = document.querySelectorAll('.schedule-slot');
            for (let i = 0; i < slots.length; i++) {
                const checked = slots[i].querySelectorAll('.slot-class-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert(`Perhatian: Jadwal Sesi ${i + 1} belum memilih kelas target! Silakan pilih minimal 1 kelas untuk setiap jadwal.`);
                    slots[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            }

            // Hapus draft dari localStorage saat form disubmit
            localStorage.removeItem(DRAFT_STORAGE_KEY);
        });
    });

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

    function onSlotClassChanged(idx) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const checked = slot.querySelectorAll('.slot-class-checkbox:checked');
        const countBadge = slot.querySelector('.slot-class-count');
        const toggleBtn = slot.querySelector('.slot-toggle-btn');
        const allBoxes = slot.querySelectorAll('.slot-class-checkbox');

        if (countBadge) {
            if (checked.length > 0) {
                countBadge.className = 'slot-class-count text-[11px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200';
                countBadge.textContent = `${checked.length} Kelas Dipilih`;
            } else {
                countBadge.className = 'slot-class-count text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200';
                countBadge.textContent = `Wajib Pilih Kelas`;
            }
        }

        if (toggleBtn) {
            toggleBtn.textContent = (checked.length === allBoxes.length && allBoxes.length > 0)
                ? 'Batal Pilih Semua'
                : 'Pilih Semua Kelas';
        }

        updateAllPreviews();
    }

    function toggleAllSlotClasses(idx) {
        const slot = document.querySelector(`.schedule-slot[data-index="${idx}"]`);
        if (!slot) return;
        const visibleCards = slot.querySelectorAll('.slot-class-card:not([style*="display: none"])');
        const visibleBoxes = Array.from(visibleCards).map(c => c.querySelector('.slot-class-checkbox'));
        const allChecked = visibleBoxes.every(cb => cb.checked);
        visibleBoxes.forEach(cb => cb.checked = !allChecked);
        onSlotClassChanged(idx);
        triggerAutosave();
    }

    function copyClassesFromFirstSlot(targetIdx) {
        const firstSlot = document.querySelector('.schedule-slot');
        const targetSlot = document.querySelector(`.schedule-slot[data-index="${targetIdx}"]`);
        if (!firstSlot || !targetSlot) return;

        const firstCheckedValues = Array.from(firstSlot.querySelectorAll('.slot-class-checkbox:checked')).map(cb => cb.value);
        targetSlot.querySelectorAll('.slot-class-checkbox').forEach(cb => {
            cb.checked = firstCheckedValues.includes(cb.value);
        });

        onSlotClassChanged(targetIdx);
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

    function setLastSlotTime(timeStr) {
        const slots = document.querySelectorAll('.schedule-time');
        if (slots.length > 0) {
            slots[slots.length - 1].value = timeStr;
            updateAllPreviews();
            triggerAutosave();
        }
    }

    function setExamDuration(mins) {
        document.getElementById('exam_duration').value = mins;
        updateAllPreviews();
        triggerAutosave();
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
                btn.className = 'duration-preset-btn px-2.5 py-1 text-xs font-semibold rounded-lg transition-all bg-indigo-600 text-white shadow-xs';
            } else {
                btn.className = 'duration-preset-btn px-2.5 py-1 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-indigo-600 hover:bg-indigo-50';
            }
        });

        const slots = document.querySelectorAll('.schedule-slot');
        const summaryList = document.getElementById('scheduleSummaryList');
        const countBadge = document.getElementById('scheduleCountBadge');
        let summaryHTML = '';

        slots.forEach((slot, i) => {
            const dateInput = slot.querySelector('.schedule-date');
            const timeInput = slot.querySelector('.schedule-time');
            const durationInput = slot.querySelector('.schedule-duration');
            const previewEl = slot.querySelector('.schedule-preview');
            const slotJpBadge = slot.querySelector('.slot-jp-badge');
            const checkedBoxes = slot.querySelectorAll('.slot-class-checkbox:checked');

            if (durationInput) {
                durationInput.placeholder = `Ikut default (${durationVal}m / ${formatJP(durationVal)})`;
            }

            const customDur = durationInput && durationInput.value ? parseInt(durationInput.value) : null;
            const effectiveDur = (customDur && customDur > 0) ? customDur : durationVal;

            if (slotJpBadge) {
                if (customDur && customDur > 0) {
                    slotJpBadge.className = 'slot-jp-badge text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200';
                    slotJpBadge.textContent = `${customDur}m (${formatJP(customDur)})`;
                } else {
                    slotJpBadge.className = 'slot-jp-badge text-[9px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100';
                    slotJpBadge.textContent = `Default (${durationVal}m / ${formatJP(durationVal)})`;
                }
            }

            const classNames = Array.from(checkedBoxes).map(cb => {
                const label = cb.closest('label');
                return label ? label.querySelector('span').textContent.trim() : '';
            }).filter(Boolean);

            if (!dateInput.value || !timeInput.value) {
                if (previewEl) previewEl.textContent = '—';
                return;
            }

            const startDT = new Date(`${dateInput.value}T${timeInput.value}`);
            const endDT = new Date(startDT.getTime() + effectiveDur * 60000);

            const dayName = days[startDT.getDay()];
            const dayDate = startDT.getDate();
            const monthName = months[startDT.getMonth()];
            const year = startDT.getFullYear();

            const sH = String(startDT.getHours()).padStart(2, '0');
            const sM = String(startDT.getMinutes()).padStart(2, '0');
            const eH = String(endDT.getHours()).padStart(2, '0');
            const eM = String(endDT.getMinutes()).padStart(2, '0');

            if (previewEl) {
                previewEl.textContent = customDur 
                    ? `${sH}:${sM}–${eH}:${eM} WIB (${customDur}m / ${formatJP(customDur)} kustom)`
                    : `${sH}:${sM}–${eH}:${eM} WIB (${effectiveDur}m / ${formatJP(effectiveDur)})`;
            }

            let classDisplay = '';
            if (classNames.length === 0) {
                classDisplay = '<span class="text-rose-600 font-bold">Belum ada kelas yang dipilih!</span>';
            } else if (classNames.length <= 3) {
                classDisplay = `<span class="text-slate-700 font-medium">Kelas: <strong>${classNames.join(', ')}</strong></span>`;
            } else {
                classDisplay = `<span class="text-slate-700 font-medium">Kelas: <strong>${classNames.slice(0, 2).join(', ')}</strong> +${classNames.length - 2} kelas lainnya</span>`;
            }

            summaryHTML += `
                <div class="p-2.5 rounded-xl bg-white border border-indigo-100/90 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-2xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-5 h-5 rounded-md bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">${i + 1}</span>
                        <span class="font-semibold text-slate-900">${dayName}, ${dayDate} ${monthName} ${year}</span>
                        <span class="text-slate-300">•</span>
                        <span class="font-mono font-medium text-indigo-700">${sH}:${sM}–${eH}:${eM} WIB</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${customDur ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-100'}">${effectiveDur} Menit (${formatJP(effectiveDur)})${customDur ? ' (Kustom)' : ''}</span>
                    </div>
                    <div class="text-[11px] self-start sm:self-auto pl-7 sm:pl-0">
                        ${classDisplay}
                    </div>
                </div>
            `;
        });

        if (summaryList) summaryList.innerHTML = summaryHTML || '<div class="text-slate-400 py-1">Isi tanggal & jam di atas...</div>';
        if (countBadge) countBadge.textContent = `${slots.length} Sesi`;
    }

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
                const title = el.querySelector('span.text-lg');
                if (title) title.className = "text-lg font-bold text-indigo-900";
            } else {
                el.className = "violation-preset-card p-4 rounded-2xl border text-left transition-all border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs cursor-pointer group";
                const title = el.querySelector('span.text-lg');
                if (title) title.className = "text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors";
            }
        });

        const badge = document.getElementById('violation_badge');
        if (badge) {
            if (num === 1) badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Mode Ketat (1x Keluar)';
            else if (num === 3) badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Mode Standar (3x Keluar)';
            else if (num === 5) badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Mode Longgar (5x Keluar)';
            else badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Mode Kustom (${num}x Keluar)`;
        }

        const liveText = document.getElementById('violation_live_text');
        if (liveText) {
            liveText.textContent = `${num} kali`;
        }
    }
</script>
@endsection
