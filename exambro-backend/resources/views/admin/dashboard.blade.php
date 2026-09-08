@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Overview</h2>
    <p class="text-sm text-slate-500 mt-1">Pantau ringkasan data dan aktivitas ujian terkini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Siswa Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg hover:shadow-indigo-500/5 hover:border-indigo-300/80 transition-all duration-200 group">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-semibold text-slate-500 group-hover:text-indigo-600 transition-colors">Total Siswa</span>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center ring-1 ring-indigo-500/10 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ \App\Models\User::where('role', 'siswa')->count() }}</div>
        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Terdaftar di sistem
        </div>
    </div>
    
    <!-- Kelas Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg hover:shadow-sky-500/5 hover:border-sky-300/80 transition-all duration-200 group">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-semibold text-slate-500 group-hover:text-sky-600 transition-colors">Total Kelas</span>
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center ring-1 ring-sky-500/10 group-hover:bg-sky-600 group-hover:text-white transition-all duration-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ \App\Models\StudentClass::count() }}</div>
        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-sky-500"></span> Ruang kelas aktif
        </div>
    </div>
    
    <!-- Ujian Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:shadow-lg hover:shadow-violet-500/5 hover:border-violet-300/80 transition-all duration-200 group">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-semibold text-slate-500 group-hover:text-violet-600 transition-colors">Total Jadwal Ujian</span>
            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center ring-1 ring-violet-500/10 group-hover:bg-violet-600 group-hover:text-white transition-all duration-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ \App\Models\Exam::count() }}</div>
        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-violet-500"></span> Jadwal terdaftar
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            </div>
            <h3 class="text-base font-semibold text-slate-900">Ujian Sedang Berlangsung</h3>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
            Live Monitor ({{ $ongoingExams->count() }} Aktif)
        </span>
    </div>

    @if($ongoingExams->isNotEmpty())
        <div class="divide-y divide-slate-100">
            @foreach($ongoingExams as $exam)
            <div class="p-6 hover:bg-slate-50/50 transition-colors flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1.5 max-w-xl">
                    <div class="flex items-center gap-2">
                        <h4 class="text-base font-bold text-slate-900">{{ $exam->title }}</h4>
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $exam->duration }} Menit
                        </span>
                    </div>
                    @if($exam->description)
                        <p class="text-xs text-slate-500 line-clamp-1">{{ $exam->description }}</p>
                    @endif
                    <div class="flex items-center gap-4 text-xs text-slate-400 pt-1">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Mulai: {{ \Carbon\Carbon::parse($exam->start_at)->format('H:i, d M Y') }}
                        </span>
                        <span>•</span>
                        <span>Selesai: {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i, d M Y') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100 text-xs">
                        <span class="px-2 py-1 bg-amber-50 text-amber-700 font-semibold rounded-lg" title="Sedang Mengerjakan">
                            ✍️ {{ $exam->working_count }} Mengerjakan
                        </span>
                        @if($exam->locked_count > 0)
                        <span class="px-2 py-1 bg-rose-50 text-rose-700 font-semibold rounded-lg animate-pulse" title="Terkunci">
                            🔒 {{ $exam->locked_count }} Dikunci
                        </span>
                        @endif
                        <span class="px-2 py-1 bg-emerald-50 text-emerald-700 font-semibold rounded-lg" title="Selesai">
                            ✅ {{ $exam->finished_count }} Selesai
                        </span>
                    </div>

                    <a href="{{ route('admin.exams.show', $exam->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Pantau Live
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="p-12 flex flex-col items-center justify-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-4">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-slate-800">Tidak ada ujian aktif saat ini</h4>
            <p class="text-xs text-slate-400 mt-1 max-w-sm">Jadwal ujian yang sedang berjalan pada jam ini akan tampil secara otomatis di sini.</p>
        </div>
    @endif
</div>
@endsection
