@extends('layouts.admin')
@section('title', 'Tambah Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Jadwal Ujian Baru</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi rincian jadwal, kelas target, dan tautan Google Form soal.</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium text-slate-600 bg-white border border-slate-200/80 hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-2xs self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('admin.exams.store') }}" method="POST" id="examForm" class="space-y-6">
        @csrf

        <!-- Card 1: Informasi Dasar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">1</div>
                <h3 class="text-base font-bold text-slate-900">Informasi & Kuesioner Soal</h3>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Judul Ujian <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all" placeholder="Contoh: PTS Matematika Wajib Kelas XI">
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
            </div>
        </div>

        <!-- Card 2: Multi-Jadwal Pelaksanaan & Durasi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">2</div>
                <h3 class="text-base font-bold text-slate-900">Jadwal Pelaksanaan & Durasi</h3>
            </div>

            <!-- Durasi (shared across all schedules) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Durasi Per Sesi (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="duration" id="exam_duration" required min="1" value="{{ old('duration', 90) }}" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all font-medium">
                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                        <button type="button" onclick="setExamDuration(45)" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">45m</button>
                        <button type="button" onclick="setExamDuration(60)" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">60m</button>
                        <button type="button" onclick="setExamDuration(90)" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">90m</button>
                        <button type="button" onclick="setExamDuration(120)" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">120m</button>
                    </div>
                </div>
                <div class="flex items-end">
                    <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-50/60 border border-indigo-100/90 text-xs font-bold text-indigo-700">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Durasi: <span id="preview_duration_pill">90 Menit</span></span>
                    </div>
                </div>
            </div>

            <!-- Multi-Schedule Slots -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Jadwal Sesi <span class="text-rose-500">*</span></label>
                        <p class="text-xs text-slate-500 mt-0.5">Tambahkan satu atau lebih jadwal untuk mapel yang sama di hari/jam berbeda.</p>
                    </div>
                    <button type="button" id="addScheduleBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Jadwal
                    </button>
                </div>

                @error('schedules') <span class="text-rose-500 text-xs mb-2 block">{{ $message }}</span> @enderror

                <div id="scheduleContainer" class="space-y-3">
                    <!-- Schedule slot 0 (default) -->
                    <div class="schedule-slot flex items-center gap-3 p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-2xl" data-index="0">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-bold shrink-0 schedule-num">1</div>
                        <div class="flex-1 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                                <input type="date" name="schedules[0][date]" required class="schedule-date w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jam Mulai</label>
                                <input type="time" name="schedules[0][time]" required value="07:30" class="schedule-time w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 font-medium whitespace-nowrap schedule-preview">—</div>
                    </div>
                </div>

                <!-- Quick time buttons -->
                <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                    <span class="text-[10px] text-slate-400 font-semibold uppercase mr-1">Preset Jam:</span>
                    <button type="button" onclick="setLastSlotTime('07:30')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">07:30</button>
                    <button type="button" onclick="setLastSlotTime('08:00')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">08:00</button>
                    <button type="button" onclick="setLastSlotTime('09:30')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">09:30</button>
                    <button type="button" onclick="setLastSlotTime('13:00')" class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md transition-colors">13:00</button>
                </div>
            </div>

            <!-- Live Schedule Summary -->
            <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/60 border border-indigo-100/90 space-y-2.5">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Ringkasan Jadwal</span>
                    <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full bg-white border border-indigo-200 text-xs font-bold text-indigo-700" id="scheduleCountBadge">1 Sesi</span>
                </div>
                <div id="scheduleSummaryList" class="space-y-1.5 text-xs">
                    <!-- Filled by JS -->
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-indigo-800/70 pt-0.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Setiap jadwal akan menjadi sesi ujian terpisah dengan judul & soal yang sama.</span>
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

        <!-- Card 3: Target Kelas & Status -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">3</div>
                <h3 class="text-base font-bold text-slate-900">Target Kelas & Status Publikasi</h3>
            </div>

            <!-- Target Kelas Interactive Badges -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Target Kelas / Peserta <span class="text-rose-500">*</span></label>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih minimal 1 kelas yang wajib mengikuti ujian ini.</p>
                    </div>
                    <button type="button" id="toggle-all-classes" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                        Pilih Semua Kelas
                    </button>
                </div>

                @error('classes') <span class="text-rose-500 text-xs mb-2 block">{{ $message }}</span> @enderror

                <!-- Search kelas -->
                <div class="relative mb-2.5">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="classSearchInput" placeholder="Cari kelas..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 p-4 bg-slate-50/70 border border-slate-200/80 rounded-2xl max-h-56 overflow-y-auto">
                    @forelse($classes as $c)
                    <label class="class-card group flex items-center gap-2.5 p-2.5 bg-white border border-slate-200/80 rounded-xl cursor-pointer transition-all hover:border-indigo-300 select-none" data-classname="{{ strtolower($c->name) }}">
                        <input type="checkbox" name="classes[]" value="{{ $c->id }}" {{ in_array($c->id, old('classes', [])) ? 'checked' : '' }} class="class-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4">
                        <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-700 transition-colors">{{ $c->name }}</span>
                    </label>
                    @empty
                    <div class="col-span-full text-xs text-slate-400 py-3 text-center">Belum ada kelas yang terdaftar.</div>
                    @endforelse
                </div>
            </div>

            <!-- Status Ujian -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-3">Status Publikasi Ujian</label>
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
                            <p class="text-xs text-emerald-800/80 mt-1">Ujian langsung muncul di aplikasi siswa sesuai dengan jadwal yang ditentukan.</p>
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
    const daysShort = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    let scheduleIndex = 1; // next index to use

    document.addEventListener('DOMContentLoaded', () => {
        // Set default date to today on first slot
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const firstDate = document.querySelector('.schedule-date');
        if (firstDate && !firstDate.value) firstDate.value = `${yyyy}-${mm}-${dd}`;

        document.getElementById('exam_duration').addEventListener('input', updateAllPreviews);

        // Delegate change events for schedule inputs
        document.getElementById('scheduleContainer').addEventListener('change', updateAllPreviews);
        document.getElementById('scheduleContainer').addEventListener('input', updateAllPreviews);

        updateAllPreviews();

        // Class search filter
        document.getElementById('classSearchInput').addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.class-card').forEach(card => {
                const name = card.getAttribute('data-classname') || '';
                card.style.display = q === '' || name.includes(q) ? '' : 'none';
            });
        });
    });

    // Add schedule slot
    document.getElementById('addScheduleBtn').addEventListener('click', function() {
        const container = document.getElementById('scheduleContainer');
        const idx = scheduleIndex++;
        const num = container.querySelectorAll('.schedule-slot').length + 1;

        const slot = document.createElement('div');
        slot.className = 'schedule-slot flex items-center gap-3 p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-2xl';
        slot.setAttribute('data-index', idx);
        slot.innerHTML = `
            <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-bold shrink-0 schedule-num">${num}</div>
            <div class="flex-1 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="date" name="schedules[${idx}][date]" required class="schedule-date w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jam Mulai</label>
                    <input type="time" name="schedules[${idx}][time]" required value="07:30" class="schedule-time w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                </div>
            </div>
            <div class="text-xs text-slate-500 font-medium whitespace-nowrap schedule-preview">—</div>
            <button type="button" onclick="removeScheduleSlot(this)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors shrink-0" title="Hapus jadwal ini">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        container.appendChild(slot);
        updateAllPreviews();
    });

    function removeScheduleSlot(btn) {
        const slot = btn.closest('.schedule-slot');
        const container = document.getElementById('scheduleContainer');
        if (container.querySelectorAll('.schedule-slot').length <= 1) return; // keep at least 1
        slot.remove();
        renumberSlots();
        updateAllPreviews();
    }

    function renumberSlots() {
        document.querySelectorAll('.schedule-slot').forEach((slot, i) => {
            slot.querySelector('.schedule-num').textContent = i + 1;
        });
    }

    function setLastSlotTime(timeStr) {
        const slots = document.querySelectorAll('.schedule-time');
        if (slots.length > 0) {
            slots[slots.length - 1].value = timeStr;
            updateAllPreviews();
        }
    }

    function setExamDuration(mins) {
        document.getElementById('exam_duration').value = mins;
        updateAllPreviews();
    }

    function updateAllPreviews() {
        const durationVal = parseInt(document.getElementById('exam_duration').value) || 0;
        const durationPill = document.getElementById('preview_duration_pill');
        if (durationPill) durationPill.textContent = `${durationVal} Menit`;

        const slots = document.querySelectorAll('.schedule-slot');
        const summaryList = document.getElementById('scheduleSummaryList');
        const countBadge = document.getElementById('scheduleCountBadge');
        let summaryHTML = '';

        slots.forEach((slot, i) => {
            const dateInput = slot.querySelector('.schedule-date');
            const timeInput = slot.querySelector('.schedule-time');
            const previewEl = slot.querySelector('.schedule-preview');

            if (!dateInput.value || !timeInput.value) {
                if (previewEl) previewEl.textContent = '—';
                return;
            }

            const startDT = new Date(`${dateInput.value}T${timeInput.value}`);
            const endDT = new Date(startDT.getTime() + durationVal * 60000);

            const dayName = days[startDT.getDay()];
            const dayDate = startDT.getDate();
            const monthName = months[startDT.getMonth()];
            const year = startDT.getFullYear();

            const sH = String(startDT.getHours()).padStart(2, '0');
            const sM = String(startDT.getMinutes()).padStart(2, '0');
            const eH = String(endDT.getHours()).padStart(2, '0');
            const eM = String(endDT.getMinutes()).padStart(2, '0');

            if (previewEl) previewEl.textContent = `${sH}:${sM}–${eH}:${eM}`;

            summaryHTML += `<div class="flex items-center gap-2 p-2 rounded-lg bg-white border border-indigo-100/80">
                <span class="w-5 h-5 rounded-md bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">${i+1}</span>
                <span class="font-semibold text-slate-800">${dayName}, ${dayDate} ${monthName} ${year}</span>
                <span class="text-slate-400">•</span>
                <span class="font-mono font-medium text-indigo-700">${sH}:${sM} – ${eH}:${eM} WIB</span>
            </div>`;
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
    }

    // Toggle All Classes button
    document.getElementById('toggle-all-classes').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.class-checkbox');
        const visibleCards = document.querySelectorAll('.class-card:not([style*="display: none"])');
        const visibleCheckboxes = Array.from(visibleCards).map(c => c.querySelector('.class-checkbox'));
        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        visibleCheckboxes.forEach(cb => cb.checked = !allChecked);
        this.textContent = allChecked ? 'Pilih Semua Kelas' : 'Batal Pilih Semua';
    });

    function selectViolationPreset(val) {
        document.getElementById('max_violation').value = val;
        updateViolationUI(val);
    }

    function stepViolation(delta) {
        const input = document.getElementById('max_violation');
        let current = parseInt(input.value) || 3;
        let nextVal = Math.min(20, Math.max(1, current + delta));
        input.value = nextVal;
        updateViolationUI(nextVal);
    }

    function handleViolationInput(val) {
        let num = parseInt(val) || 1;
        updateViolationUI(num);
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
