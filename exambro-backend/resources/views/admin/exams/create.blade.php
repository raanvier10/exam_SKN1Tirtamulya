@extends('layouts.admin')
@section('title', 'Tambah Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Jadwal Ujian Baru</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi rincian jadwal, kelas target per sesi, dan tautan Google Form soal.</p>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-4 border-b border-slate-100">
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
                        <span>Durasi pengerjaan: <span id="preview_duration_pill">90 Menit</span></span>
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

    function renderScheduleSlot(idx, defaultDate, defaultTime = '07:30', selectedClassIds = []) {
        const container = document.getElementById('scheduleContainer');
        const num = container.querySelectorAll('.schedule-slot').length + 1;
        const dateVal = defaultDate || getTodayString();

        let classCardsHtml = '';
        allClasses.forEach(c => {
            const isChecked = selectedClassIds.includes(c.id);
            classCardsHtml += `
                <label class="slot-class-card group flex items-center gap-2 p-2 bg-slate-50/70 border border-slate-200 rounded-lg cursor-pointer transition-all hover:border-indigo-300 select-none" data-classname="${c.name.toLowerCase()}">
                    <input type="checkbox" name="schedules[${idx}][classes][]" value="${c.id}" ${isChecked ? 'checked' : ''} 
                           class="slot-class-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4"
                           onchange="onSlotClassChanged(${idx})">
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

            <!-- Inputs Waktu -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Ujian <span class="text-rose-500">*</span></label>
                    <input type="date" name="schedules[${idx}][date]" value="${dateVal}" required class="schedule-date w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="schedules[${idx}][time]" value="${defaultTime}" required class="schedule-time w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 font-medium">
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

    document.addEventListener('DOMContentLoaded', () => {
        // Slot awal
        renderScheduleSlot(scheduleIndex++);

        document.getElementById('exam_duration').addEventListener('input', updateAllPreviews);
        document.getElementById('scheduleContainer').addEventListener('input', updateAllPreviews);
        document.getElementById('scheduleContainer').addEventListener('change', updateAllPreviews);

        // Validasi sebelum submit form
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
    });

    function removeScheduleSlot(btn) {
        const slot = btn.closest('.schedule-slot');
        const container = document.getElementById('scheduleContainer');
        if (container.querySelectorAll('.schedule-slot').length <= 1) return;
        slot.remove();
        renumberSlots();
        updateAllPreviews();
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
            const checkedBoxes = slot.querySelectorAll('.slot-class-checkbox:checked');

            const classNames = Array.from(checkedBoxes).map(cb => {
                const label = cb.closest('label');
                return label ? label.querySelector('span').textContent.trim() : '';
            }).filter(Boolean);

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

            if (previewEl) previewEl.textContent = `${sH}:${sM}–${eH}:${eM} WIB`;

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
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">${i + 1}</span>
                        <span class="font-semibold text-slate-900">${dayName}, ${dayDate} ${monthName} ${year}</span>
                        <span class="text-slate-300">•</span>
                        <span class="font-mono font-medium text-indigo-700">${sH}:${sM}–${eH}:${eM} WIB</span>
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
    }

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
