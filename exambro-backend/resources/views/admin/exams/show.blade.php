@extends('layouts.admin')
@section('title', 'Detail & Monitoring Ujian')

@section('content')
<div class="w-full space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('admin.exams.index', ['mode' => 'uas']) }}" class="text-xs sm:text-sm font-medium text-slate-500 hover:text-[#2457E8] transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Jadwal Ujian
                </a>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">{{ $exam->title }}</h2>
                @if(isset($selectedClass) && $selectedClass)
                    <span class="text-xs font-bold text-[#142C5C] bg-[#F5F8FF] border border-blue-200 px-2.5 py-1 rounded-lg">
                        Ruang Kelas {{ $selectedClass->name }}
                    </span>
                @endif
            </div>
        </div>

        @php
            $canManageExam = auth()->user()->isAdmin() || ($exam->created_by === auth()->id());
        @endphp
        <div class="flex flex-wrap items-center gap-2.5">
            @if($exam->status === 'active')
                <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-emerald-700 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Sesi Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-500 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span> Draft / Nonaktif
                </span>
            @endif

            <a href="{{ route('admin.exams.export-violations', $exam->id) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:border-[#2457E8] hover:text-[#2457E8] shadow-2xs transition whitespace-nowrap">
                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>{{ $canManageExam ? 'Export CSV' : 'Export Pelanggaran (CSV)' }}</span>
            </a>

            @if($canManageExam)
            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs col-span-2 lg:col-span-1 flex items-center justify-between lg:block">
            <div>
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Peserta</div>
                <div class="text-[11px] text-slate-400 mt-0.5 lg:hidden">Siswa Terdaftar</div>
            </div>
            <div class="text-2xl font-bold text-[#142C5C] lg:mt-1">{{ $stats['total_participants'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5 hidden lg:block">Siswa Terdaftar</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                {{ ($exam->end_at && now()->gt($exam->end_at)) ? 'Tidak Mengerjakan' : 'Belum Mulai' }}
            </div>
            <div class="text-2xl font-bold text-[#142C5C] mt-1">{{ $stats['registered'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5 truncate">
                {{ ($exam->end_at && now()->gt($exam->end_at)) ? 'Tidak Mengikuti' : 'Menunggu Jadwal' }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Mengerjakan</div>
            <div class="text-2xl font-bold text-[#2457E8] mt-1">{{ $stats['working'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5 truncate">Sesi Aktif di App</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border {{ $stats['locked'] > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200' }} shadow-2xs">
            <div class="text-[11px] font-semibold {{ $stats['locked'] > 0 ? 'text-rose-600' : 'text-slate-500' }} uppercase tracking-wider">Terkunci</div>
            <div class="text-2xl font-bold {{ $stats['locked'] > 0 ? 'text-rose-600' : 'text-[#142C5C]' }} mt-1">{{ $stats['locked'] }}</div>
            <div class="text-[11px] {{ $stats['locked'] > 0 ? 'text-rose-600 font-medium' : 'text-slate-500' }} mt-0.5 truncate">Butuh Buka Kunci</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Selesai</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['finished'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5 truncate">Submit Form</div>
        </div>
    </div>

    <!-- Segmented Tab Navigation -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-1 grid grid-cols-1 sm:grid-cols-3 gap-1">
        <button type="button" id="tabBtn-monitoring" onclick="switchShowTab('monitoring')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer">
            <span>Monitoring Peserta ({{ $stats['total_participants'] }})</span>
        </button>
        <button type="button" id="tabBtn-info" onclick="switchShowTab('info')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer">
            <span>Informasi &amp; Akses Ujian</span>
        </button>
        <button type="button" id="tabBtn-violations" onclick="switchShowTab('violations')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer">
            <span>Log Pelanggaran ({{ $violations->count() }})</span>
        </button>
    </div>

    <!-- TAB 2: Informasi & Akses Ujian -->
    <div id="tabPanel-info" class="hidden">
        @php
            $sebWebUrl = \App\Http\Controllers\SebExamController::getExamWebUrl($exam);
            $sebSchemeUrl = \App\Http\Controllers\SebExamController::getSebSchemeUrl($exam);
            $serverHost = \App\Http\Controllers\SebExamController::getPublicServerBaseUrl();
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Informasi, Parameter &amp; Akses Ujian</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jadwal pelaksanaan, aturan keamanan, tautan Google Form, dan akses Safe Exam Browser (iOS).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#0D2149] text-white text-[11px] uppercase tracking-wider font-bold">
                            <th class="py-3.5 px-6 w-56 whitespace-nowrap">Parameter</th>
                            <th class="py-3.5 px-6">Informasi / Tautan</th>
                            <th class="py-3.5 px-6 text-right whitespace-nowrap w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Waktu Mulai</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y, H:i') }} WIB</td>
                            <td class="py-3.5 px-6 text-right text-slate-400">-</td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Waktu Selesai</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ \Carbon\Carbon::parse($exam->end_at)->format('d M Y, H:i') }} WIB</td>
                            <td class="py-3.5 px-6 text-right text-slate-400">-</td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Durasi Ujian</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ $exam->duration }} Menit ({{ round($exam->duration / 35, 1) }} JP)</td>
                            <td class="py-3.5 px-6 text-right text-slate-400">-</td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Batas Pelanggaran</td>
                            <td class="py-3.5 px-6 text-rose-700 font-medium">{{ $exam->max_violation }}x Keluar / Switch</td>
                            <td class="py-3.5 px-6 text-right text-slate-400">-</td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Total Pelanggaran</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ $stats['total_violations'] }} Kejadian</td>
                            <td class="py-3.5 px-6 text-right text-slate-400">-</td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Deskripsi Ujian</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ $exam->description ?: 'Tidak ada deskripsi tambahan.' }}</td>
                            <td class="py-3.5 px-6 text-right text-slate-400">-</td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Google Form URL</td>
                            <td class="py-3.5 px-6 max-w-md">
                                <a href="{{ $exam->google_form_url }}" target="_blank" class="text-[#2457E8] text-xs hover:underline font-mono truncate block" title="{{ $exam->google_form_url }}">
                                    {{ $exam->google_form_url }}
                                </a>
                            </td>
                            <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                @if(auth()->user()->isAdmin() || ($exam->created_by === auth()->id()))
                                    <button type="button" onclick="document.getElementById('quickEditFormModal').showModal()" class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-[#2457E8] text-[#142C5C] hover:text-white border border-slate-200 hover:border-[#2457E8] rounded-lg text-xs font-semibold shadow-2xs transition cursor-pointer">
                                        Ubah Link Form
                                    </button>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="hover:bg-[#F5F8FF] transition-colors">
                            <td class="py-3.5 px-6 font-semibold text-slate-700 whitespace-nowrap">Akses Siswa iOS (SEB)</td>
                            <td class="py-3.5 px-6 max-w-md">
                                <div class="text-slate-600 text-xs font-mono truncate select-all">{{ $sebSchemeUrl }}</div>
                            </td>
                            <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-1.5">
                                <button type="button" onclick="openSebQrModal()" class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-[#2457E8] text-slate-700 hover:text-white border border-slate-200 hover:border-[#2457E8] rounded-lg text-xs font-semibold shadow-2xs transition cursor-pointer">
                                    Tampilkan QR
                                </button>
                                <a href="{{ route('seb.exam.config', $exam) }}" class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-[#2457E8] text-slate-700 hover:text-white border border-slate-200 hover:border-[#2457E8] rounded-lg text-xs font-semibold shadow-2xs transition" title="Unduh file konfigurasi .seb">
                                    .seb
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 1: Monitoring Peserta Ujian (Default Active) -->
    <div id="tabPanel-monitoring" class="space-y-5">
        <!-- Card Filter Peserta -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-[#142C5C]">Filter Peserta Ujian</h3>
                <p class="text-xs text-slate-500 mt-0.5">Saring daftar peserta berdasarkan ruang kelas atau cari nama/NIS siswa.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ruang Kelas</label>
                    @if(isset($selectedClass) && $selectedClass)
                        <div class="flex items-center justify-between px-3.5 h-[42px] bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <span class="font-semibold text-[#142C5C]">{{ $selectedClass->name }}</span>
                            @if($exam->classes->count() > 1)
                                <a href="{{ route('admin.exams.show', $exam->id) }}" class="text-[#2457E8] hover:underline font-semibold">Semua Kelas</a>
                            @endif
                        </div>
                    @elseif($exam->classes->count() > 1)
                        <select id="roomSelectFilter" onchange="window.location.href = this.value" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium text-slate-800">
                            <option value="{{ route('admin.exams.show', $exam->id) }}" selected>Semua Ruang Kelas ({{ $exam->classes->count() }} Kelas)</option>
                            @foreach($exam->classes as $cls)
                                <option value="{{ route('admin.exams.show', ['exam' => $exam->id, 'class_id' => $cls->id]) }}">Ruang Kelas {{ $cls->name }}</option>
                            @endforeach
                        </select>
                    @elseif($exam->classes->count() === 1)
                        <div class="flex items-center px-3.5 h-[42px] bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-[#142C5C]">
                            {{ $exam->classes->first()->name }}
                        </div>
                    @else
                        <div class="flex items-center px-3.5 h-[42px] bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500">
                            Semua Kelas
                        </div>
                    @endif
                </div>

                <div class="md:col-span-6">
                    <label for="searchStudent" class="block text-xs font-semibold text-slate-700 mb-1.5">Cari Nama / NIS</label>
                    <input type="text" id="searchStudent" placeholder="Cari nama atau NIS siswa..." class="w-full h-[42px] px-3.5 text-xs sm:text-sm bg-white text-slate-900 placeholder:text-slate-400 border border-slate-200 rounded-xl focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15" onkeyup="filterStudents()">
                </div>

                <div class="md:col-span-2">
                    <label for="perPageSelect" class="block text-xs font-semibold text-slate-700 mb-1.5">Tampilkan</label>
                    <select id="perPageSelect" onchange="changePerPage(this.value)" data-native="true" class="w-full h-[42px] px-3 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-medium text-slate-800 focus:outline-none focus:border-[#2457E8] cursor-pointer">
                        <option value="5">5 baris</option>
                        <option value="10" selected>10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card Tabel Monitoring Peserta -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Daftar Peserta Ujian ({{ $stats['total_participants'] }} Siswa)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau status pengerjaan siswa dan lakukan buka kunci atau reset sesi jika diperlukan.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button id="autoRefreshBtn" onclick="toggleAutoRefresh()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition whitespace-nowrap cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0" id="autoRefreshIndicator"></span>
                        <span id="autoRefreshText">Auto Refresh: OFF</span>
                    </button>
                    <button onclick="window.location.reload();" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1E40AF] hover:bg-blue-800 text-white rounded-xl text-xs font-semibold transition whitespace-nowrap cursor-pointer">
                        Refresh
                    </button>
                </div>
            </div>

        <!-- Desktop Table View (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse" id="studentsTable">
                <thead>
                    <tr class="bg-[#0D2149] text-white text-[11px] uppercase tracking-wider font-bold">
                        <th class="py-3.5 px-6 whitespace-nowrap">No</th>
                        <th class="py-3.5 px-6 min-w-[200px]">Siswa</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Kelas</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Status Pengerjaan</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Pelanggaran</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Waktu Mulai</th>
                        <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi Operator</th>
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
                            <td class="py-4 px-6 text-xs text-slate-400 font-medium whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="py-4 px-6 min-w-[200px]">
                                <div class="font-bold text-slate-900 student-name">{{ $user->name ?? 'Siswa' }}</div>
                                <div class="text-xs text-slate-500 font-mono student-nis mt-0.5">NIS: {{ $user->username ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap text-xs font-medium text-slate-700">
                                {{ $user->class->name ?? '-' }}
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @php
                                    $isExpired = $exam->end_at && now()->gt($exam->end_at);
                                @endphp
                                @if($p->status === 'finished')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2457E8]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2457E8]"></span>
                                        {{ ($isExpired && $p->started_at && !$p->finished_at) ? 'Selesai (Waktu Habis)' : 'Selesai' }}
                                    </span>
                                @elseif($isLocked)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        TERKUNCI (LOCKED)
                                    </span>
                                @elseif($p->status === 'working')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sedang Mengerjakan
                                    </span>
                                @else
                                    @if($isExpired)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Tidak Mengerjakan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Belum Mulai
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($p->total_violation_count > 0)
                                    <div class="text-xs font-bold {{ $p->violation_count >= $exam->max_violation ? 'text-rose-700' : 'text-amber-700' }}">
                                        {{ $p->violation_count }} / {{ $exam->max_violation }}
                                    </div>
                                    @if($p->total_violation_count > $p->violation_count)
                                        <div class="text-[10px] text-slate-400 mt-0.5 font-medium">({{ $p->total_violation_count }} total riwayat)</div>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-500 font-medium">0 / {{ $exam->max_violation }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 whitespace-nowrap">
                                {{ $p->started_at ? \Carbon\Carbon::parse($p->started_at)->format('H:i:s') : '-' }}
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if($canUnlockAndReset && !$isExpired)
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
                                @elseif($isExpired)
                                    <span class="text-xs text-slate-400 font-mono">-</span>
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
                            @if($p->status === 'finished')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20">
                                    <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ ($isExpired && $p->started_at && !$p->finished_at) ? 'Selesai (Waktu Habis)' : 'Selesai' }}
                                </span>
                            @elseif($isLocked)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 ring-1 ring-rose-500/30">
                                    <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    TERKUNCI
                                </span>
                            @elseif($p->status === 'working')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span> Mengerjakan
                                </span>
                            @else
                                @if($isExpired)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                                        <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Tidak Mengerjakan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                        Belum Mulai
                                    </span>
                                @endif
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

    </div>

    <!-- TAB 3: Log Riwayat Pelanggaran Siswa -->
    <div id="tabPanel-violations" class="hidden">
        <div id="violationsSection" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden scroll-mt-6">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Log Riwayat Pelanggaran Siswa ({{ $violations->count() }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas mencurigakan yang terdeteksi secara otomatis oleh aplikasi mobile.</p>
                </div>
            </div>
            <div class="p-4 sm:p-6">

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
                                    @php $vType = strtoupper($v->type ?? ''); @endphp
                                    @if(str_contains($vType, 'SCREEN_OFF'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-500/20">
                                            Layar Dimatikan (Screen Off)
                                        </span>
                                    @elseif(str_contains($vType, 'RESTART') || str_contains($vType, 'DEVICE_OFF') || str_contains($vType, 'REBOOT'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-500/20">
                                            HP Dimatikan / Restart
                                        </span>
                                    @elseif(str_contains($vType, 'SWITCH') || str_contains($vType, 'APP'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-500/20">
                                            Keluar Aplikasi (App Switch)
                                        </span>
                                    @elseif(str_contains($vType, 'FLOATING'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-50 text-sky-700 ring-1 ring-sky-500/20">
                                            Jendela Mengambang (Floating Window)
                                        </span>
                                    @elseif(str_contains($vType, 'SPLIT') || str_contains($vType, 'MULTI_WINDOW'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-500/20">
                                            Layar Belah (Split Screen)
                                        </span>
                                    @elseif(str_contains($vType, 'RECORDING'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-pink-50 text-pink-700 ring-1 ring-pink-500/20">
                                            Perekaman Layar (Screen Recording)
                                        </span>
                                    @elseif(str_contains($vType, 'SCREENSHOT'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-500/20">
                                            Screenshot Attempt
                                        </span>
                                    @elseif(str_contains($vType, 'URL'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-50 text-red-700 ring-1 ring-red-500/20">
                                            Akses URL Terlarang / Browsing
                                        </span>
                                    @elseif(str_contains($vType, 'COPY'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-500/20">
                                            Salin Soal (Copy Attempt)
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
                            @php $vTypeMob = strtoupper($v->type ?? ''); @endphp
                            @if(str_contains($vTypeMob, 'SCREEN_OFF'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-500/20 shrink-0">
                                    Screen Off
                                </span>
                            @elseif(str_contains($vTypeMob, 'RESTART') || str_contains($vTypeMob, 'DEVICE_OFF') || str_contains($vTypeMob, 'REBOOT'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-500/20 shrink-0">
                                    Device Off / Restart
                                </span>
                            @elseif(str_contains($vTypeMob, 'SWITCH') || str_contains($vTypeMob, 'APP'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-500/20 shrink-0">
                                    App Switch
                                </span>
                            @elseif(str_contains($vTypeMob, 'FLOATING'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 ring-1 ring-sky-500/20 shrink-0">
                                    Floating Window
                                </span>
                            @elseif(str_contains($vTypeMob, 'SPLIT') || str_contains($vTypeMob, 'MULTI_WINDOW'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-500/20 shrink-0">
                                    Split Screen
                                </span>
                            @elseif(str_contains($vTypeMob, 'RECORDING'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-700 ring-1 ring-pink-500/20 shrink-0">
                                    Recording
                                </span>
                            @elseif(str_contains($vTypeMob, 'SCREENSHOT'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-500/20 shrink-0">
                                    Screenshot
                                </span>
                            @elseif(str_contains($vTypeMob, 'URL'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 ring-1 ring-red-500/20 shrink-0">
                                    Akses URL Ilegal
                                </span>
                            @elseif(str_contains($vTypeMob, 'COPY'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-500/20 shrink-0">
                                    Salin Soal (Copy)
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
    </div>
</div>

<script>
function switchShowTab(tab) {
    ['monitoring', 'info', 'violations'].forEach(function(name) {
        const panel = document.getElementById('tabPanel-' + name);
        const btn = document.getElementById('tabBtn-' + name);
        const isActive = (name === tab);
        if (panel) panel.classList.toggle('hidden', !isActive);
        if (btn) {
            btn.className = isActive
                ? 'py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer'
                : 'py-2.5 px-4 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer';
        }
    });
}
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

<!-- Modal QR Code Safe Exam Browser -->
<dialog id="sebQrModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-sm m-auto overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-white flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">QR Code Ujian (SEB iOS)</h3>
            <p class="text-xs text-slate-500 mt-0.5">Scan dengan kamera iPhone untuk membuka SEB.</p>
        </div>
        <button type="button" onclick="document.getElementById('sebQrModal').close()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
    <div class="p-6 bg-white text-center">
        <div class="inline-block p-3 bg-white border-2 border-dashed border-purple-300 rounded-2xl shadow-sm mb-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($sebSchemeUrl) }}" alt="QR Code Safe Exam Browser" class="w-48 h-48 block mx-auto">
        </div>
        <div class="text-xs font-bold text-slate-800">{{ $exam->title }}</div>
        <p class="text-[11px] text-slate-500 mt-1 mb-2">Arahkan kamera iPhone siswa langsung ke kode QR di atas untuk membuka aplikasi Safe Exam Browser.</p>
        <div class="text-[11px] text-indigo-700 bg-indigo-50 border border-indigo-200/80 px-2.5 py-1 rounded-lg mb-3">
            Host: <strong class="font-mono">{{ $serverHost }}</strong> (iPhone & Laptop wajib di Wi-Fi / Hotspot yang sama).
        </div>

        <div class="flex items-center gap-2 bg-purple-50/70 p-2 rounded-xl border border-purple-200 text-left">
            <span class="text-[11px] font-mono text-purple-900 truncate select-all flex-1 pl-1">{{ $sebSchemeUrl }}</span>
            <button type="button" data-copy-url="{{ $sebSchemeUrl }}" onclick="navigator.clipboard.writeText(this.dataset.copyUrl); alert('Link SEB berhasil disalin!');" class="px-2.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-semibold shrink-0 transition-colors shadow-2xs">
                Salin Link
            </button>
        </div>
    </div>
</dialog>
<script>
    function openSebQrModal() {
        document.getElementById('sebQrModal').showModal();
    }
</script>
@endsection
