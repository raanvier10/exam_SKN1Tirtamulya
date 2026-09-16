@extends('layouts.admin')
@section('title', 'Detail & Monitoring Ujian - ' . $exam->title)

@section('content')
<div class="w-full space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.exams.index') }}" class="text-xs sm:text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Jadwal Ujian
                </a>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $exam->title }}</h2>
                @if($exam->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sesi Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Draft / Nonaktif
                    </span>
                @endif
            </div>
        </div>

        @php
            $canManageExam = auth()->user()->isAdmin() || ($exam->created_by === auth()->id());
        @endphp
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-2.5 w-full md:w-auto">
            <!-- Tombol Pintasan Cepat ke Log Pelanggaran -->
            <a href="#violationsSection" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs transition-all active:scale-[0.98] w-full sm:w-auto" title="Gulir cepat ke Riwayat Pelanggaran Siswa">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                <span>Log Pelanggaran ({{ $stats['total_violations'] }})</span>
            </a>

            <!-- Tombol Auto Refresh -->
            <button id="autoRefreshBtn" onclick="toggleAutoRefresh()" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-indigo-50 hover:text-indigo-600 shadow-xs transition-all active:scale-[0.98] w-full sm:w-auto">
                <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0" id="autoRefreshIndicator"></span>
                <span id="autoRefreshText" class="truncate">Auto Refresh: OFF</span>
            </button>

            <!-- Tombol Manual Refresh -->
            <button onclick="window.location.reload();" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 hover:text-indigo-600 shadow-xs transition-all active:scale-[0.98] w-full sm:w-auto">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Refresh Data</span>
            </button>

            <!-- Tombol Export Pelanggaran -->
            <a href="{{ route('admin.exams.export-violations', $exam->id) }}" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs transition-all active:scale-[0.98] w-full sm:w-auto {{ $canManageExam ? '' : 'col-span-2' }}" title="Download Rekap Riwayat Pelanggaran Siswa (CSV)">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>{{ $canManageExam ? 'Export CSV' : 'Export Rekap Pelanggaran (CSV)' }}</span>
            </a>

            @if($canManageExam)
            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-4 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-xs transition-all active:scale-[0.98] w-full sm:w-auto">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Ujian</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Alert / Toast -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Quick Stat Cards (Master Card on top for mobile, balanced 2x2 grid, 5-col on desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- 1. Total Peserta (Master Card) -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs col-span-2 lg:col-span-1 flex items-center justify-between lg:block">
            <div class="flex items-center gap-2.5 lg:block">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0 lg:hidden">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider mb-0.5 lg:mb-1">Total Peserta</div>
                    <div class="text-[10px] sm:text-[11px] text-slate-400">Siswa Terdaftar</div>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $stats['total_participants'] }}</div>
        </div>

        <!-- 2. Belum Mulai -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs col-span-1">
            <div class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Belum Mulai</div>
            <div class="text-xl sm:text-2xl font-bold text-slate-600">{{ $stats['registered'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Menunggu Jadwal</div>
        </div>

        <!-- 3. Mengerjakan -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs col-span-1">
            <div class="text-[10px] sm:text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Mengerjakan</div>
            <div class="text-xl sm:text-2xl font-bold text-emerald-600">{{ $stats['working'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-emerald-600/80 mt-0.5 truncate">Sesi Aktif di App</div>
        </div>

        <!-- 4. Terkunci (Locked) -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border {{ $stats['locked'] > 0 ? 'border-rose-400 bg-rose-50/40 ring-1 ring-rose-200' : 'border-slate-200' }} shadow-xs col-span-1">
            <div class="text-[10px] sm:text-xs font-semibold {{ $stats['locked'] > 0 ? 'text-rose-600' : 'text-slate-500' }} uppercase tracking-wider mb-1">Terkunci</div>
            <div class="text-xl sm:text-2xl font-bold {{ $stats['locked'] > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $stats['locked'] }}</div>
            <div class="text-[10px] sm:text-[11px] {{ $stats['locked'] > 0 ? 'text-rose-500 font-medium' : 'text-slate-400' }} mt-0.5 truncate">Butuh Buka Kunci</div>
        </div>

        <!-- 5. Selesai -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs col-span-1">
            <div class="text-[10px] sm:text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">Selesai</div>
            <div class="text-xl sm:text-2xl font-bold text-indigo-600">{{ $stats['finished'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-indigo-500/80 mt-0.5 truncate">Submit Form</div>
        </div>
    </div>

    <!-- Informasi Ujian Box -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-stretch">
        <!-- Kolom Kiri: Informasi Utama & Link -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 lg:col-span-2 flex flex-col justify-between space-y-4">
            <div>
                <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Target Kelas / Peserta</div>
                <div class="flex flex-wrap items-center gap-1.5 mb-3">
                    @if($exam->classes->count() > 0)
                        @foreach($exam->classes as $cls)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $cls->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            Semua Kelas Aktif
                        </span>
                    @endif
                </div>

                <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Deskripsi Ujian</div>
                <div class="text-slate-700 text-xs sm:text-sm leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-200">{{ $exam->description ?: 'Tidak ada deskripsi tambahan untuk ujian ini.' }}</div>
            </div>
            
            <div class="p-3.5 sm:p-4 rounded-xl bg-indigo-50/60 border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="overflow-hidden min-w-0">
                    <div class="text-indigo-900 text-xs font-semibold uppercase tracking-wider mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Google Form URL (Secure Gateway)
                    </div>
                    <a href="{{ $exam->google_form_url }}" target="_blank" class="text-indigo-600 text-xs hover:underline font-mono break-all font-medium flex items-center gap-1 mt-0.5">
                        {{ $exam->google_form_url }}
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
                
                @if(auth()->user()->isAdmin() || ($exam->created_by === auth()->id()))
                <!-- Tombol Ubah Link Google Form Cepat -->
                <button type="button" onclick="document.getElementById('quickEditFormModal').showModal()" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 hover:border-indigo-600 rounded-xl text-xs font-bold shadow-2xs transition-all shrink-0 active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Ubah Link Form
                </button>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 pt-3 border-t border-slate-200 text-xs">
                <div>
                    <div class="text-slate-400 font-semibold uppercase tracking-wider mb-1">Waktu Mulai</div>
                    <div class="text-slate-900 font-semibold text-sm">{{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y, H:i') }} WIB</div>
                </div>
                <div>
                    <div class="text-slate-400 font-semibold uppercase tracking-wider mb-1">Waktu Selesai</div>
                    <div class="text-slate-900 font-semibold text-sm">{{ \Carbon\Carbon::parse($exam->end_at)->format('d M Y, H:i') }} WIB</div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pengaturan & Toleransi -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-4">Parameter Keamanan</div>
                
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                        <span class="text-slate-500 font-medium">Durasi Ujian</span>
                        <span class="text-slate-900 font-bold flex items-center gap-1.5">
                            {{ $exam->duration }} Menit
                            <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200/80 px-2 py-0.5 rounded-md">
                                {{ round($exam->duration / 45, 1) }} JP
                            </span>
                        </span>
                    </div>

                    <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                        <span class="text-slate-500 font-medium">Batas Pelanggaran (Max)</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                            {{ $exam->max_violation }}x Keluar / Switch
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Total Pelanggaran Tercatat</span>
                        <span class="text-slate-900 font-bold text-amber-600">{{ $stats['total_violations'] }} Kejadian</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 mt-4">
                <div class="text-[11px] text-slate-400 leading-relaxed flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                    Siswa yang melebihi batas {{ $exam->max_violation }}x otomatis terkunci dan harus dibuka oleh pengawas.
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL 1: Live Monitoring Peserta Ujian (Dual Mode: Desktop Table + Mobile Cards) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Monitoring Peserta Ujian</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar siswa yang terdaftar, status pengerjaan, dan kontrol buka kunci sesi.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 rounded-xl px-2.5 py-1.5 shrink-0 justify-between sm:justify-start">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tampil:</span>
                    <select id="perPageSelect" onchange="changePerPage(this.value)" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
                        <option value="5">5 baris</option>
                        <option value="10" selected>10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
                <input type="text" id="searchStudent" placeholder="Cari nama atau NIS siswa..." class="w-full sm:w-60 px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" onkeyup="filterStudents()">
            </div>
        </div>

        <!-- Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse" id="studentsTable">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                        <th class="py-3.5 px-6">No</th>
                        <th class="py-3.5 px-6">Siswa</th>
                        <th class="py-3.5 px-6">Kelas</th>
                        <th class="py-3.5 px-6">Status Pengerjaan</th>
                        <th class="py-3.5 px-6">Pelanggaran</th>
                        <th class="py-3.5 px-6">Waktu Mulai</th>
                        <th class="py-3.5 px-6 text-right">Aksi Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($participants as $index => $p)
                        @php
                        $user = $p->user;
                        $isLocked = ($p->status === 'locked' || (isset($p->session) && $p->session->status === 'LOCKED'));
                        $canUnlockAndReset = auth()->user()->isAdmin() || ($exam->created_by === auth()->id());
                    @endphp
                    <tr class="student-row hover:bg-slate-50/70 transition-colors {{ $isLocked ? 'bg-rose-50/30' : '' }}">
                            <td class="py-4 px-6 text-xs text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full {{ $isLocked ? 'bg-rose-100 text-rose-700 ring-2 ring-rose-400' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ substr($user->name ?? 'S', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 student-name">{{ $user->name ?? 'Siswa' }}</div>
                                        <div class="text-xs text-slate-400 font-mono student-nis">NIS: {{ $user->username ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                                    {{ $user->class->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $isExpired = $exam->end_at && now()->gt($exam->end_at);
                                @endphp
                                @if($isLocked)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 ring-1 ring-rose-500/30">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        TERKUNCI (LOCKED)
                                    </span>
                                @elseif($p->status === 'working')
                                    @if($isExpired)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Waktu Habis (Selesai)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Sedang Mengerjakan
                                        </span>
                                    @endif
                                @elseif($p->status === 'finished')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        Belum Mulai
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($p->total_violation_count > 0)
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $p->violation_count >= $exam->max_violation ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-300' : ($p->violation_count > 0 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-300' : 'bg-slate-100 text-slate-700') }}">
                                            <svg class="w-3.5 h-3.5 {{ $p->violation_count >= $exam->max_violation ? 'text-rose-500' : 'text-amber-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            {{ $p->violation_count }} / {{ $exam->max_violation }}
                                        </span>
                                        @if($p->total_violation_count > $p->violation_count)
                                            <div class="text-[10px] text-slate-400 mt-0.5 font-medium">({{ $p->total_violation_count }} total riwayat)</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">0 / {{ $exam->max_violation }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $p->started_at ? \Carbon\Carbon::parse($p->started_at)->format('H:i:s') : '-' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if($canUnlockAndReset)
                                    <div class="inline-flex items-center gap-2">
                                        @if($isLocked)
                                            <!-- Tombol Buka Kunci (Unlock) -->
                                            <form action="{{ route('admin.exams.students.unlock', [$exam->id, $p->user_id]) }}" method="POST" onsubmit="return confirm('Buka kunci ujian untuk {{ $user->name }}? Siswa akan dapat melanjutkan ujian kembali.');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition-all active:scale-[0.98]">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                    Buka Kunci
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Tombol Reset Sesi -->
                                        <form action="{{ route('admin.exams.students.reset', [$exam->id, $p->user_id]) }}" method="POST" onsubmit="return confirm('Reset sesi ujian {{ $user->name }} dari awal? Semua waktu pengerjaan dan pelanggaran akan direset.');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-rose-600 rounded-xl text-xs font-medium transition-colors" title="Reset Sesi Siswa">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Reset
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    @if($isLocked)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200" title="Buka kunci ujian UAS ditangani langsung oleh Kurikulum">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Buka Kunci: Kurikulum
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-mono">-</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                Belum ada siswa yang terdaftar sebagai peserta ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (< md) -->
        <div class="md:hidden p-3 sm:p-4 space-y-3 bg-slate-50/60" id="studentsMobileList">
            @forelse($participants as $index => $p)
                @php
                    $user = $p->user;
                    $isLocked = ($p->status === 'locked' || (isset($p->session) && $p->session->status === 'LOCKED'));
                    $canUnlockAndReset = auth()->user()->isAdmin() || ($exam->created_by === auth()->id());
                @endphp
                <div class="student-card bg-white rounded-xl border {{ $isLocked ? 'border-2 border-rose-400 bg-rose-50/40 ring-1 ring-rose-200' : 'border border-slate-200' }} shadow-xs p-3.5 sm:p-4 space-y-2.5 transition-all">
                    <!-- Header Siswa & Kelas -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full {{ $isLocked ? 'bg-rose-100 text-rose-700 ring-2 ring-rose-400' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                {{ substr($user->name ?? 'S', 0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-900 text-xs sm:text-sm truncate student-name">{{ $user->name ?? 'Siswa' }}</div>
                                <div class="text-[10px] sm:text-[11px] text-slate-400 font-mono student-nis">NIS: {{ $user->username ?? '-' }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md shrink-0">
                            {{ $user->class->name ?? '-' }}
                        </span>
                    </div>

                    <!-- Informasi Status & Pelanggaran (Clean 2-Column Grid) -->
                    <div class="grid grid-cols-2 gap-2 p-2.5 bg-slate-50/90 rounded-xl border border-slate-200 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Status Pengerjaan</span>
                            @php
                                $isExpired = $exam->end_at && now()->gt($exam->end_at);
                            @endphp
                            @if($isLocked)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 ring-1 ring-rose-500/30">
                                    <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    TERKUNCI
                                </span>
                            @elseif($p->status === 'working')
                                @if($isExpired)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">
                                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Waktu Habis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span> Mengerjakan
                                    </span>
                                @endif
                            @elseif($p->status === 'finished')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20">
                                    <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-200/70 text-slate-600">
                                    Belum Mulai
                                </span>
                            @endif
                        </div>

                        <div class="text-right flex flex-col items-end">
                            <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Pelanggaran</span>
                            <div class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $p->violation_count >= $exam->max_violation ? 'text-rose-600' : ($p->violation_count > 0 ? 'text-amber-600' : 'text-slate-600') }}">
                                <svg class="w-3 h-3 {{ $p->violation_count >= $exam->max_violation ? 'text-rose-500' : ($p->violation_count > 0 ? 'text-amber-500' : 'text-slate-400') }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>{{ $p->violation_count }} / {{ $exam->max_violation }}</span>
                            </div>
                        </div>

                        @if($p->started_at)
                        <div class="col-span-2 pt-1.5 border-t border-slate-200 flex items-center justify-between text-[10px] text-slate-500">
                            <span>Mulai Ujian:</span>
                            <span class="font-mono text-slate-700 font-medium">{{ \Carbon\Carbon::parse($p->started_at)->format('H:i:s') }} WIB</span>
                        </div>
                        @endif
                    </div>

                    <!-- Operator Actions -->
                    @if($canUnlockAndReset)
                        @if($isLocked)
                            <div class="pt-1 flex items-center gap-2">
                                <form action="{{ route('admin.exams.students.unlock', [$exam->id, $p->user_id]) }}" method="POST" onsubmit="return confirm('Buka kunci ujian untuk {{ $user->name }}? Siswa akan dapat melanjutkan ujian.');" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs active:scale-[0.98] transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                        Buka Kunci (Unlock)
                                    </button>
                                </form>
                                <form action="{{ route('admin.exams.students.reset', [$exam->id, $p->user_id]) }}" method="POST" onsubmit="return confirm('Reset sesi ujian {{ $user->name }} dari awal?');">
                                    @csrf
                                    <button type="submit" class="p-2 inline-flex items-center justify-center bg-white border border-slate-300 hover:bg-rose-50 text-slate-500 hover:text-rose-600 rounded-xl text-xs transition-colors" title="Reset Sesi Siswa">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @elseif($p->status === 'working' || $p->status === 'finished')
                            <div class="pt-0.5 flex justify-end">
                                <form action="{{ route('admin.exams.students.reset', [$exam->id, $p->user_id]) }}" method="POST" onsubmit="return confirm('Reset sesi ujian {{ $user->name }} dari awal?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-500 hover:text-rose-600 rounded-lg text-[11px] font-medium transition-colors">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Reset Sesi</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        @if($isLocked)
                            <div class="text-[10px] text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200 text-center font-medium">
                                Sesi ujian terkunci. Pembukaan kunci ditangani oleh Kurikulum.
                            </div>
                        @endif
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-xs bg-white rounded-xl border border-slate-200">
                    Belum ada siswa yang terdaftar sebagai peserta ujian ini.
                </div>
            @endforelse
            <div id="noMatchStudentMobile" class="hidden p-6 text-center text-slate-400 text-xs bg-white rounded-xl border border-slate-200">
                Tidak ada siswa yang cocok dengan pencarian.
            </div>
        </div>

        <!-- Pagination Controls Bar -->
        <div id="participantPaginationContainer" class="p-3 sm:p-4 bg-slate-50/80 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="text-slate-500 text-center sm:text-left font-medium" id="paginationInfoText">
                Menampilkan <span class="font-bold text-slate-800" id="pageStartNum">1</span>–<span class="font-bold text-slate-800" id="pageEndNum">10</span> dari <span class="font-bold text-slate-800" id="pageTotalNum">0</span> peserta
            </div>
            <div class="flex items-center justify-center gap-1 self-center sm:self-auto flex-wrap" id="paginationButtons">
                <!-- Tombol Pagination di-render via Javascript -->
            </div>
        </div>
    </div>

    <!-- TABEL 2: Realtime Violation Logs (Dual Mode: Desktop Table + Mobile Cards) -->
    <div id="violationsSection" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-6 scroll-mt-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Log Riwayat Pelanggaran Siswa
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas mencurigakan yang terdeteksi secara otomatis oleh aplikasi mobile.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 shrink-0">
                {{ $violations->count() }} Log
            </span>
        </div>

        @if($violations->count() > 0)
            <!-- Desktop Table View (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Tipe Pelanggaran</th>
                            <th class="py-3 px-4">Keterangan / Kejadian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($violations as $v)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-slate-500">
                                    {{ \Carbon\Carbon::parse($v->created_at)->format('H:i:s') }} WIB
                                    <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($v->created_at)->format('d M Y') }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    {{ $v->user->name ?? 'User #' . $v->user_id }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $v->user->class->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if(str_contains(strtoupper($v->type), 'SWITCH') || str_contains(strtoupper($v->type), 'APP'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-500/20">
                                            Keluar Aplikasi (App Switch)
                                        </span>
                                    @elseif(str_contains(strtoupper($v->type), 'SCREENSHOT'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-500/20">
                                            Screenshot Attempt
                                        </span>
                                    @elseif(str_contains(strtoupper($v->type), 'SPLIT'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-500/20">
                                            Split Screen
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                            {{ $v->type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-mono text-[11px]">
                                    {{ $v->description ?: 'Terdeteksi meninggalkan layar ujian' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Violation Cards (< md) -->
            <div class="md:hidden space-y-2.5">
                @foreach($violations as $v)
                    <div class="p-3 bg-white hover:bg-slate-50 rounded-xl border border-slate-200 transition-colors space-y-1.5 shadow-2xs">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $v->user->name ?? 'User #' . $v->user_id }}</span>
                                <span class="text-[10px] text-slate-500 bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded shrink-0">{{ $v->user->class->name ?? '-' }}</span>
                            </div>
                            <span class="font-mono text-[10px] text-slate-400 shrink-0">{{ \Carbon\Carbon::parse($v->created_at)->format('H:i:s') }} WIB</span>
                        </div>
                        <div class="flex items-start gap-2 pt-0.5">
                            @if(str_contains(strtoupper($v->type), 'SWITCH') || str_contains(strtoupper($v->type), 'APP'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-500/20 shrink-0">
                                    App Switch
                                </span>
                            @elseif(str_contains(strtoupper($v->type), 'SCREENSHOT'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-500/20 shrink-0">
                                    Screenshot
                                </span>
                            @elseif(str_contains(strtoupper($v->type), 'SPLIT'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-500/20 shrink-0">
                                    Split Screen
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 shrink-0">
                                    {{ $v->type }}
                                </span>
                            @endif
                            <p class="text-[11px] text-slate-600 leading-snug font-mono">{{ $v->description ?: 'Terdeteksi meninggalkan layar ujian' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-slate-400 flex flex-col items-center justify-center py-10 border-2 border-dashed border-slate-100 rounded-xl bg-slate-50/50">
                <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Belum ada pelanggaran keamanan yang tercatat untuk ujian ini.</span>
            </div>
        @endif
    </div>
</div>

<script>
// ==============================================================
// PAGINATION & FILTER MONITORING PESERTA (Maksimal 5/10 ke bawah)
// ==============================================================
const PER_PAGE_KEY = 'exam_{{ $exam->id }}_per_page';
const PAGE_KEY = 'exam_{{ $exam->id }}_page';

let currentPerPage = sessionStorage.getItem(PER_PAGE_KEY) || '10';
let currentPage = parseInt(sessionStorage.getItem(PAGE_KEY)) || 1;

function getStudentData() {
    const input = document.getElementById('searchStudent')?.value.toLowerCase().trim() || '';
    const rows = Array.from(document.querySelectorAll('#studentsTable tbody tr.student-row'));
    const cards = Array.from(document.querySelectorAll('#studentsMobileList .student-card'));

    const matchingRows = [];
    const matchingCards = [];

    rows.forEach(row => {
        const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
        const nis = row.querySelector('.student-nis')?.textContent.toLowerCase() || '';
        if (input === '' || name.includes(input) || nis.includes(input)) {
            matchingRows.push(row);
        }
    });

    cards.forEach(card => {
        const name = card.querySelector('.student-name')?.textContent.toLowerCase() || '';
        const nis = card.querySelector('.student-nis')?.textContent.toLowerCase() || '';
        if (input === '' || name.includes(input) || nis.includes(input)) {
            matchingCards.push(card);
        }
    });

    return { rows, cards, matchingRows, matchingCards };
}

function updatePagination() {
    const { rows, cards, matchingRows, matchingCards } = getStudentData();
    const total = matchingRows.length;
    const perPageNum = currentPerPage === 'all' ? (total || 1) : parseInt(currentPerPage);
    const totalPages = Math.max(1, Math.ceil(total / perPageNum));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;
    sessionStorage.setItem(PAGE_KEY, currentPage);

    const startIdx = (currentPage - 1) * perPageNum;
    const endIdx = currentPerPage === 'all' ? total : Math.min(startIdx + perPageNum, total);

    // Hide all items first
    rows.forEach(r => r.style.display = 'none');
    cards.forEach(c => c.style.display = 'none');

    // Show current page slice
    matchingRows.slice(startIdx, endIdx).forEach(r => r.style.display = '');
    matchingCards.slice(startIdx, endIdx).forEach(c => c.style.display = '');

    // Mobile no-match state
    const noMatchMobile = document.getElementById('noMatchStudentMobile');
    if (noMatchMobile) {
        noMatchMobile.classList.toggle('hidden', total > 0 || cards.length === 0);
    }

    // Update label counter
    const startEl = document.getElementById('pageStartNum');
    const endEl = document.getElementById('pageEndNum');
    const totalEl = document.getElementById('pageTotalNum');
    if (startEl) startEl.textContent = total > 0 ? startIdx + 1 : 0;
    if (endEl) endEl.textContent = endIdx;
    if (totalEl) totalEl.textContent = total;

    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    const container = document.getElementById('paginationButtons');
    if (!container) return;

    if (totalPages <= 1 && currentPerPage === 'all') {
        container.innerHTML = '<span class="text-slate-400 text-xs italic">Semua baris ditampilkan</span>';
        return;
    }

    let html = '';
    const prevDisabled = currentPage <= 1;
    html += `
        <button type="button" onclick="goToPage(${currentPage - 1})" ${prevDisabled ? 'disabled' : ''} class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 bg-white ${prevDisabled ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-50 text-slate-700 hover:text-indigo-600'} transition-all">
            &larr; Prev
        </button>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (totalPages > 7) {
            if (i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                if (i === 2 || i === totalPages - 1) {
                    html += `<span class="px-1 text-slate-400 font-bold">...</span>`;
                }
                continue;
            }
        }
        const isActive = i === currentPage;
        html += `
            <button type="button" onclick="goToPage(${i})" class="min-w-[28px] h-7 px-2 text-xs rounded-lg border transition-all ${isActive ? 'bg-indigo-600 text-white border-indigo-600 font-bold shadow-2xs' : 'bg-white text-slate-700 hover:bg-slate-50 hover:text-indigo-600 border-slate-200 font-medium'}">
                ${i}
            </button>
        `;
    }

    const nextDisabled = currentPage >= totalPages;
    html += `
        <button type="button" onclick="goToPage(${currentPage + 1})" ${nextDisabled ? 'disabled' : ''} class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 bg-white ${nextDisabled ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-50 text-slate-700 hover:text-indigo-600'} transition-all">
            Next &rarr;
        </button>
    `;

    container.innerHTML = html;
}

function goToPage(pageNum) {
    currentPage = pageNum;
    sessionStorage.setItem(PAGE_KEY, currentPage);
    updatePagination();
}

function changePerPage(val) {
    currentPerPage = val;
    sessionStorage.setItem(PER_PAGE_KEY, currentPerPage);
    currentPage = 1;
    sessionStorage.setItem(PAGE_KEY, 1);
    updatePagination();
}

function filterStudents() {
    currentPage = 1;
    updatePagination();
}

// Auto Refresh Feature (10 seconds timer)
const AUTO_REFRESH_KEY = 'exam_auto_refresh_{{ $exam->id }}';
let autoRefreshTimer = null;
let countdownTimer = null;
let countdownSecs = 10;

function updateAutoRefreshUI(isActive) {
    const btn = document.getElementById('autoRefreshBtn');
    const indicator = document.getElementById('autoRefreshIndicator');
    const text = document.getElementById('autoRefreshText');

    if (isActive) {
        btn.classList.remove('bg-white', 'text-slate-700');
        btn.classList.add('bg-indigo-50', 'text-indigo-600', 'border-indigo-200');
        indicator.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
        text.textContent = `Auto Refresh: ${countdownSecs}s`;
    } else {
        btn.classList.remove('bg-indigo-50', 'text-indigo-600', 'border-indigo-200');
        btn.classList.add('bg-white', 'text-slate-700');
        indicator.className = 'w-2 h-2 rounded-full bg-slate-400';
        text.textContent = 'Auto Refresh: OFF';
    }
}

function startAutoRefresh() {
    countdownSecs = 10;
    updateAutoRefreshUI(true);

    if (countdownTimer) clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
        countdownSecs--;
        if (countdownSecs > 0) {
            document.getElementById('autoRefreshText').textContent = `Auto Refresh: ${countdownSecs}s`;
        } else {
            document.getElementById('autoRefreshText').textContent = 'Memuat data...';
        }
    }, 1000);

    if (autoRefreshTimer) clearTimeout(autoRefreshTimer);
    autoRefreshTimer = setTimeout(() => {
        window.location.reload();
    }, 10000);
}

function stopAutoRefresh() {
    if (countdownTimer) clearInterval(countdownTimer);
    if (autoRefreshTimer) clearTimeout(autoRefreshTimer);
    updateAutoRefreshUI(false);
}

function toggleAutoRefresh() {
    const currentState = localStorage.getItem(AUTO_REFRESH_KEY) === 'true';
    const newState = !currentState;
    localStorage.setItem(AUTO_REFRESH_KEY, newState ? 'true' : 'false');
    
    if (newState) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi dropdown per-page dari storage
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect && currentPerPage) {
        perPageSelect.value = currentPerPage;
    }

    // Jalankan kalkulasi pagination monitoring peserta
    updatePagination();

    const isAutoRefreshOn = localStorage.getItem(AUTO_REFRESH_KEY) === 'true';
    if (isAutoRefreshOn) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});
</script>
<!-- Modal Dialog Cepat Ubah Link Google Form -->
<dialog id="quickEditFormModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto m-auto">
    <div class="px-5 py-4 sm:px-6 sm:py-4 border-b border-slate-100 bg-white flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Ubah Link Google Form</h3>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui tautan kuesioner/soal ujian ini secara instan.</p>
        </div>
        <button type="button" onclick="document.getElementById('quickEditFormModal').close()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <form action="{{ route('admin.exams.quick-form-url', $exam->id) }}" method="POST" class="p-5 sm:p-6 bg-white space-y-4">
        @csrf
        @method('PATCH')
        
        <div class="space-y-1.5">
            <label for="google_form_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Link Google Form Baru</label>
            <input 
                type="url" 
                id="google_form_url" 
                name="google_form_url" 
                value="{{ $exam->google_form_url }}" 
                placeholder="https://docs.google.com/forms/d/e/.../viewform"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
            <p class="text-[11px] text-slate-400">Pastikan link dapat dibuka tanpa izin edit (hanya responden).</p>
        </div>

        <div class="flex justify-end items-center gap-2.5 pt-2">
            <button type="button" onclick="document.getElementById('quickEditFormModal').close()" class="px-4 py-2 text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-2xs">Batal</button>
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-xs transition-all active:scale-[0.98]">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Simpan Link
            </button>
        </div>
    </form>
</dialog>
@endsection
