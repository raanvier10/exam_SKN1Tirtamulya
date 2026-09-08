@extends('layouts.admin')
@section('title', 'Detail & Monitoring Ujian - ' . $exam->title)

@section('content')
<div class="w-full space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.exams.index') }}" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Jadwal Ujian
                </a>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $exam->title }}</h2>
                @if($exam->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sesi Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Draft / Nonaktif
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Tombol Auto Refresh -->
            <button id="autoRefreshBtn" onclick="toggleAutoRefresh()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-sm font-medium hover:bg-indigo-50 hover:text-indigo-600 shadow-xs transition-all active:scale-[0.98]">
                <span class="w-2 h-2 rounded-full bg-slate-400" id="autoRefreshIndicator"></span>
                <span id="autoRefreshText">Auto Refresh: OFF</span>
            </button>

            <!-- Tombol Export Pelanggaran -->
            <a href="{{ route('admin.exams.export-violations', $exam->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-sm font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs transition-all active:scale-[0.98]" title="Download Rekap Riwayat Pelanggaran Siswa (CSV)">
                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Pelanggaran (CSV)
            </a>

            <button onclick="window.location.reload();" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 hover:text-indigo-600 shadow-xs transition-all active:scale-[0.98]">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh Data
            </button>
            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-sm font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-xs transition-all active:scale-[0.98]">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Ujian
            </a>
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

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Peserta</div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total_participants'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Siswa Terdaftar</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Belum Mulai</div>
            <div class="text-2xl font-bold text-slate-600">{{ $stats['registered'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Menunggu Jadwal</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Mengerjakan</div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['working'] }}</div>
            <div class="text-[11px] text-emerald-600/80 mt-1">Sesi Aktif di App</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border {{ $stats['locked'] > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80' }} shadow-xs">
            <div class="text-xs font-semibold {{ $stats['locked'] > 0 ? 'text-rose-600' : 'text-slate-500' }} uppercase tracking-wider mb-1">Terkunci (Locked)</div>
            <div class="text-2xl font-bold {{ $stats['locked'] > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $stats['locked'] }}</div>
            <div class="text-[11px] {{ $stats['locked'] > 0 ? 'text-rose-500 font-medium' : 'text-slate-400' }} mt-1">Butuh Buka Kunci</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs col-span-2 md:col-span-1">
            <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">Selesai</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $stats['finished'] }}</div>
            <div class="text-[11px] text-indigo-500/80 mt-1">Submit Google Form</div>
        </div>
    </div>

    <!-- Informasi Ujian Box -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        <!-- Kolom Kiri: Informasi Utama & Link -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 lg:col-span-2 flex flex-col justify-between space-y-4">
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
                <div class="text-slate-700 text-sm leading-relaxed bg-slate-50/50 p-3.5 rounded-xl border border-slate-100">{{ $exam->description ?: 'Tidak ada deskripsi tambahan untuk ujian ini.' }}</div>
            </div>
            
            <div class="p-3.5 rounded-xl bg-indigo-50/50 border border-indigo-100">
                <div class="text-indigo-900 text-xs font-semibold uppercase tracking-wider mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 text-xs">
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
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-4">Parameter Keamanan</div>
                
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Durasi Ujian</span>
                        <span class="text-slate-900 font-bold">{{ $exam->duration }} Menit</span>
                    </div>

                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
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

            <div class="pt-4 border-t border-slate-100 mt-4">
                <div class="text-[11px] text-slate-400 leading-relaxed flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                    Siswa yang melebihi batas {{ $exam->max_violation }}x otomatis terkunci dan harus dibuka oleh pengawas.
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL 1: Live Monitoring Peserta Ujian -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Monitoring Peserta Ujian</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar siswa yang terdaftar, status pengerjaan, dan kontrol buka kunci sesi.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <input type="text" id="searchStudent" placeholder="Cari siswa atau NIS..." class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 w-48 sm:w-64" onkeyup="filterStudents()">
            </div>
        </div>

        <div class="overflow-x-auto">
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
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isLocked ? 'bg-rose-50/30' : '' }}">
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
                                @if($isLocked)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 ring-1 ring-rose-500/30">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        TERKUNCI (LOCKED)
                                    </span>
                                @elseif($p->status === 'working')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Sedang Mengerjakan
                                    </span>
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
    </div>

    <!-- TABEL 2: Realtime Violation Logs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Log Riwayat Pelanggaran Siswa
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas mencurigakan yang terdeteksi secara otomatis oleh aplikasi mobile.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                {{ $violations->count() }} Total Log
            </span>
        </div>

        @if($violations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
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
function filterStudents() {
    const input = document.getElementById('searchStudent').value.toLowerCase();
    const rows = document.querySelectorAll('#studentsTable tbody tr');
    
    rows.forEach(row => {
        const nameEl = row.querySelector('.student-name');
        const nisEl = row.querySelector('.student-nis');
        if (nameEl && nisEl) {
            const name = nameEl.textContent.toLowerCase();
            const nis = nisEl.textContent.toLowerCase();
            if (name.includes(input) || nis.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
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
    const isAutoRefreshOn = localStorage.getItem(AUTO_REFRESH_KEY) === 'true';
    if (isAutoRefreshOn) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});
</script>
@endsection
