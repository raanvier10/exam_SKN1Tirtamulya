@extends('layouts.admin')
@section('title', 'Dashboard')
@section('header_title', 'Dashboard Overview')

@section('content')
<!-- Hero Welcome Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0D2149] via-[#142C5C] to-[#2457E8] p-6 sm:p-8 text-white mb-6 shadow-sm">
    <!-- Subtle Geometric Concentric Circles -->
    <svg class="hidden sm:block absolute -right-10 -top-12 w-72 h-72 pointer-events-none" viewBox="0 0 300 300" fill="none" aria-hidden="true">
        <circle cx="190" cy="120" r="120" stroke="#DCE9FF" stroke-opacity="0.14" stroke-width="1.5"/>
        <circle cx="190" cy="120" r="85" stroke="#DCE9FF" stroke-opacity="0.18" stroke-width="1.5"/>
        <circle cx="190" cy="120" r="50" fill="#DCE9FF" fill-opacity="0.08"/>
    </svg>

    <div class="relative z-10 max-w-2xl">
        <div class="text-xs font-bold text-amber-300">
            Selamat Bertugas, {{ auth()->user()->isDepartmentPic() ? 'PIC Jurusan ' . auth()->user()->departmentCode() : (auth()->user()->isAdmin() ? 'Administrator' : 'Guru Pengampu') }}
        </div>
        <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">
            {{ auth()->user()->name }}
        </h3>
        <p class="text-xs sm:text-sm text-blue-100/85 mt-2 leading-relaxed">
            Pantau pelaksanaan ujian secara real-time, kelola jadwal per kelas, dan awasi integritas ujian SMKN 1 Tirtamulya secara terpusat.
        </p>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-[#DCE9FF] text-[#142C5C] rounded-xl text-xs font-bold transition shadow-xs">
                <svg class="w-4 h-4 text-[#2457E8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Kelola Jadwal Ujian
            </a>
            <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl text-xs font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Jadwal Baru
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
@if(auth()->user()->isAdmin())
    @php
        $dashDeptClassIds = auth()->user()->departmentClassIds();
        $dashTotalSiswa = \App\Models\User::where('role', 'siswa')
            ->when($dashDeptClassIds !== null, fn ($q) => $q->whereIn('class_id', $dashDeptClassIds))
            ->count();
        $dashTotalKelas = $dashDeptClassIds !== null ? count($dashDeptClassIds) : \App\Models\StudentClass::count();
        $dashTotalUjian = \App\Models\Exam::when($dashDeptClassIds !== null, fn ($q) => $q->whereHas('classes', fn ($cq) => $cq->whereIn('classes.id', $dashDeptClassIds)))->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <!-- Total Siswa -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-[#2457E8]/40 transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Siswa</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#142C5C] tracking-tight">{{ $dashTotalSiswa }}</span>
                        <span class="text-xs text-slate-500 font-medium">Siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#DCE9FF] text-[#2457E8] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Terdaftar aktif</span>
                <a href="{{ route('admin.students.index') }}" class="font-semibold text-[#2457E8] hover:underline">Kelola</a>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-[#2457E8]/40 transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Kelas</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#142C5C] tracking-tight">{{ $dashTotalKelas }}</span>
                        <span class="text-xs text-slate-500 font-medium">Kelas</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Ruang kelas aktif</span>
                <a href="{{ route('admin.classes.index') }}" class="font-semibold text-[#2457E8] hover:underline">Kelola</a>
            </div>
        </div>

        <!-- Total Guru -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-[#2457E8]/40 transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Guru</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#142C5C] tracking-tight">{{ \App\Models\User::where('role', 'guru')->count() }}</span>
                        <span class="text-xs text-slate-500 font-medium">Guru</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Pengampu &amp; pengawas</span>
                <a href="{{ route('admin.teachers.index') }}" class="font-semibold text-[#2457E8] hover:underline">Kelola</a>
            </div>
        </div>

        <!-- Total Jadwal Ujian -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-[#2457E8]/40 transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal Ujian</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#142C5C] tracking-tight">{{ $dashTotalUjian }}</span>
                        <span class="text-xs text-slate-500 font-medium">Jadwal</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">{{ $ongoingExams->count() }} sedang berlangsung</span>
                <a href="{{ route('admin.exams.index') }}" class="font-semibold text-[#2457E8] hover:underline">Lihat</a>
            </div>
        </div>
    </div>
@else
    @php
        $myExamsCount = \App\Models\Exam::where('created_by', auth()->id())->count();
        $totalWorking = $ongoingExams->sum('working_count');
        $totalLocked = $ongoingExams->sum('locked_count');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Ujian Saya (Guru) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-[#2457E8]/40 transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Ujian Buatan Saya</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#142C5C] tracking-tight">{{ $myExamsCount }}</span>
                        <span class="text-xs text-slate-500 font-medium">Ujian</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#DCE9FF] text-[#2457E8] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Ujian mapel mandiri</span>
                <a href="{{ route('admin.exams.index') }}" class="font-semibold text-[#2457E8] hover:underline">Kelola</a>
            </div>
        </div>

        <!-- Sedang Mengerjakan (Guru) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-emerald-300 transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sedang Mengerjakan</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tracking-tight">{{ $totalWorking }}</span>
                        <span class="text-xs text-slate-500 font-medium">Siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Sesi aktif saat ini</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
        </div>

        <!-- Siswa Terkunci (Guru) -->
        <div class="bg-white rounded-2xl p-5 border {{ $totalLocked > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-2xs transition">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ $totalLocked > 0 ? 'text-rose-600' : 'text-slate-500' }}">Siswa Terkunci</div>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold {{ $totalLocked > 0 ? 'text-rose-600' : 'text-[#142C5C]' }} tracking-tight">{{ $totalLocked }}</span>
                        <span class="text-xs text-slate-500 font-medium">Siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $totalLocked > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs {{ $totalLocked > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">
                {{ $totalLocked > 0 ? 'Perlu tindakan buka kunci' : 'Tidak ada sesi terkunci' }}
            </div>
        </div>
    </div>
@endif

<!-- Ujian Sedang Berlangsung -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
    <div class="px-5 py-4 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Ujian Sedang Berlangsung</h3>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-[#DCE9FF]/60 hover:bg-[#DCE9FF] text-[#2457E8] transition">
            Lihat Semua
        </a>
    </div>

    @if($ongoingExams->isNotEmpty())
        <div class="divide-y divide-slate-100">
            @foreach($ongoingExams as $exam)
            <div class="p-5 sm:p-6 hover:bg-[#F5F8FF]/60 transition-colors flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1.5 max-w-xl">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-base font-bold text-slate-900">{{ $exam->title }}</h4>
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-[#DCE9FF] text-[#142C5C]">
                            {{ $exam->duration }} Menit
                        </span>
                    </div>
                    @if($exam->description)
                        <p class="text-xs text-slate-500 line-clamp-2 sm:line-clamp-1">{{ $exam->description }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400 pt-1">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mulai: {{ \Carbon\Carbon::parse($exam->start_at)->format('H:i, d M Y') }}
                        </span>
                        <span>&bull;</span>
                        <span>Selesai: {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i, d M Y') }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
                    <div class="grid {{ $exam->locked_count > 0 ? 'grid-cols-3' : 'grid-cols-2' }} sm:flex items-center gap-1.5 p-1.5 bg-slate-50 rounded-xl border border-slate-200/70 text-xs w-full sm:w-auto">
                        <span class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg font-semibold bg-white border border-amber-200 text-amber-700" title="Siswa Sedang Mengerjakan">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="font-bold text-slate-900">{{ $exam->working_count }}</span>
                            <span class="truncate">Mengerjakan</span>
                        </span>

                        @if($exam->locked_count > 0)
                        <span class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg font-semibold bg-rose-50 border border-rose-200 text-rose-700" title="Sesi Terkunci">
                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="font-bold">{{ $exam->locked_count }}</span>
                            <span class="truncate">Dikunci</span>
                        </span>
                        @endif

                        <span class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg font-semibold bg-white border border-emerald-200 text-emerald-700" title="Selesai Mengerjakan">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-bold text-slate-900">{{ $exam->finished_count }}</span>
                            <span class="truncate">Selesai</span>
                        </span>
                    </div>

                    <a href="{{ route('admin.exams.show', $exam->id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all w-full sm:w-auto shrink-0 active:scale-[0.98]">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Pantau Live</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="p-12 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#F5F8FF] text-[#2457E8] flex items-center justify-center mb-4 border border-[#DCE9FF]">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h4 class="text-sm font-bold text-[#142C5C]">Tidak ada ujian aktif saat ini</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-sm">Jadwal ujian yang sedang berjalan pada jam ini akan tampil secara otomatis di sini.</p>
        </div>
    @endif
</div>
@endsection
