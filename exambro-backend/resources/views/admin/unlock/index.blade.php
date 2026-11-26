@extends('layouts.admin')
@section('title', 'Buka Kunci Ujian')

@section('content')
<div class="w-full space-y-5">
    <!-- Page Header (match Master Kelas / Siswa / Guru) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#142C5C] tracking-tight">Buka Kunci Ujian</h2>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">Cari nama atau NIS siswa yang terkunci untuk membuka akses ujian sekaligus mencatat arsip pelanggaran.</p>
        </div>
        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <a id="unlockExportLink"
               data-base-href="{{ route('admin.unlock.export') }}"
               href="{{ route('admin.unlock.export', array_filter(['search' => $search])) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold hover:bg-slate-50 hover:border-[#2457E8] hover:text-[#2457E8] shadow-2xs transition active:scale-[0.98] whitespace-nowrap">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Excel (.xlsx)
            </a>
        </div>
    </div>

    <!-- Mode Web Ujian Darurat (Admin / PIC Jurusan) -->
    @if(auth()->user()->isAdmin())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5 min-w-0">
            <div class="w-10 h-10 rounded-xl {{ $webEmergencyEnabled ? 'bg-[#DCE9FF] text-[#2457E8]' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                </svg>
            </div>
            <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Mode Web Ujian Darurat</h3>
                    @if($webEmergencyEnabled)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            Nonaktif
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Akses ujian darurat melalui browser (<span class="px-1.5 py-0.5 rounded bg-slate-100 text-[#142C5C] font-semibold text-xs">/darurat</span>) khusus siswa yang perangkatnya gagal memasang APK.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($webEmergencyEnabled && $webEmergencyPin)
                <div class="px-3.5 py-2 rounded-xl bg-[#F5F8FF] border border-[#DCE9FF] flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500">PIN:</span>
                    <span class="text-sm font-bold tracking-[0.16em] text-[#142C5C] tabular-nums">{{ $webEmergencyPin }}</span>
                </div>

                <button type="button" onclick="navigator.clipboard?.writeText('{{ route('web-exam.index') }}'); const old = this.textContent; this.textContent='Tersalin'; setTimeout(() => this.textContent = old, 1500);" class="px-3.5 py-2 bg-white border border-slate-200 hover:border-[#2457E8] text-slate-700 hover:text-[#2457E8] rounded-xl text-xs font-semibold transition cursor-pointer">
                    Salin Link
                </button>

                <form action="{{ route('admin.unlock.web-emergency.rotate-pin') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-white border border-slate-200 hover:border-[#2457E8] text-slate-700 hover:text-[#2457E8] rounded-xl text-xs font-semibold transition cursor-pointer">
                        Acak PIN
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.unlock.web-emergency.toggle') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 {{ $webEmergencyEnabled ? 'bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 hover:border-rose-600' : 'bg-[#2457E8] hover:bg-blue-700 text-white border border-transparent' }} rounded-xl text-xs font-bold transition cursor-pointer">
                    {{ $webEmergencyEnabled ? 'Nonaktifkan' : 'Aktifkan Mode Darurat' }}
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Search Bar Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5">
        <form method="GET" action="{{ route('admin.unlock.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="unlockSearchInput" name="search" value="{{ $search }}" autofocus autocomplete="off"
                       placeholder="Ketik nama atau NIS siswa yang terkunci..."
                       class="w-full h-[42px] bg-slate-50 focus:bg-white border border-slate-200 rounded-xl pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15"
                       oninput="filterUnlockRows(this.value)">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="px-5 h-[42px] bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer">
                    Cari Siswa
                </button>
                @if($search !== '')
                    <a href="{{ route('admin.unlock.index') }}" class="px-4 h-[42px] inline-flex items-center bg-white border border-slate-200 text-slate-600 hover:text-rose-600 rounded-xl text-xs sm:text-sm font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Segmented Tabs (matching exams/show.blade.php) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-1 grid grid-cols-2 gap-1">
        <button type="button" id="unlockTabBtn-locked" onclick="switchUnlockTab('locked')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer">
            <span>Siswa Terkunci ({{ $lockedSessions->count() }})</span>
        </button>
        <button type="button" id="unlockTabBtn-archive" onclick="switchUnlockTab('archive')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer">
            <span>Arsip Buka Kunci ({{ $unlockArchive->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: Siswa Terkunci (matching Master Kelas / Siswa / Guru card + #0D2149 header + bg-slate-50 thead) -->
    <div id="unlockPanel-locked" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="bg-[#0D2149] px-5 py-3.5 text-white flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-bold tracking-tight">Daftar Sesi Terkunci ({{ $lockedSessions->count() }})</h3>
            <span class="text-xs text-blue-200">Klik Buka Kunci untuk mengizinkan siswa melanjutkan ujian</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                    <tr>
                        <th class="px-6 py-4">Siswa &amp; NIS</th>
                        <th class="px-6 py-4 whitespace-nowrap">Kelas</th>
                        <th class="px-6 py-4">Ujian</th>
                        <th class="px-6 py-4 whitespace-nowrap">Pelanggaran</th>
                        <th class="px-6 py-4 whitespace-nowrap">Terkunci Sejak</th>
                        <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lockedSessions as $session)
                    <tr class="unlock-row hover:bg-[#F5F8FF] transition-colors duration-150" data-search="{{ strtolower(($session->user->name ?? '') . ' ' . ($session->user->username ?? '') . ' ' . ($session->exam->title ?? '')) }}">
                        <td class="px-6 py-4">
                            <div class="text-slate-900 font-bold">{{ $session->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 tabular-nums mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span>NIS: {{ $session->user->username ?? '-' }}</span>
                                @if(str_starts_with((string) ($session->device_id ?? ''), 'WEB_DARURAT'))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#DCE9FF] text-[#142C5C]">WEB DARURAT</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-700 font-medium">
                            {{ $session->user->class->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-800 font-medium">
                            {{ $session->exam->title ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                {{ $session->violations_count }}x Pelanggaran
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 tabular-nums">
                            {{ $session->updated_at ? $session->updated_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($session->exam && $session->user)
                            <form action="{{ route('admin.exams.students.unlock', [$session->exam_id, $session->user_id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Buka kunci ujian untuk {{ $session->user->name }}? Nama siswa tetap tercatat di arsip pelanggaran.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-2xs transition cursor-pointer">
                                    Buka Kunci
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            Tidak ada siswa yang sedang terkunci saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: Arsip Buka Kunci -->
    <div id="unlockPanel-archive" class="hidden bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="bg-[#0D2149] px-5 py-3.5 text-white flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-bold tracking-tight">Arsip Buka Kunci ({{ $unlockArchive->count() }})</h3>
            <span class="text-xs text-blue-200">Riwayat siswa yang pernah terkunci dan telah dibuka kembali</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                    <tr>
                        <th class="px-6 py-4">Siswa &amp; NIS</th>
                        <th class="px-6 py-4 whitespace-nowrap">Kelas</th>
                        <th class="px-6 py-4">Ujian</th>
                        <th class="px-6 py-4 whitespace-nowrap">Total Pelanggaran</th>
                        <th class="px-6 py-4 whitespace-nowrap">Waktu Buka Kunci</th>
                        <th class="px-6 py-4 whitespace-nowrap">Status Sesi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($unlockArchive as $item)
                    <tr class="unlock-row hover:bg-[#F5F8FF] transition-colors duration-150" data-search="{{ strtolower(($item->user->name ?? '') . ' ' . ($item->user->username ?? '') . ' ' . ($item->exam->title ?? '')) }}">
                        <td class="px-6 py-4">
                            <div class="text-slate-900 font-bold">{{ $item->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 tabular-nums mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span>NIS: {{ $item->user->username ?? '-' }}</span>
                                @if(str_starts_with((string) ($item->device_id ?? ''), 'WEB_DARURAT'))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#DCE9FF] text-[#142C5C]">WEB DARURAT</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-700 font-medium">
                            {{ $item->user->class->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-800 font-medium">
                            {{ $item->exam->title ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                {{ $item->violations_count }}x Pelanggaran
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 font-medium tabular-nums">
                            {{ $item->unlocked_at ? $item->unlocked_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold">
                            @if($item->status === 'LOCKED')
                                <span class="text-rose-700">Terkunci Lagi</span>
                            @elseif($item->status === 'FINISHED' || $item->status === 'COMPLETED')
                                <span class="text-[#2457E8]">Selesai</span>
                            @else
                                <span class="text-emerald-700">Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            Belum ada riwayat buka kunci ujian yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function switchUnlockTab(tab) {
    ['locked', 'archive'].forEach(function(name) {
        const panel = document.getElementById('unlockPanel-' + name);
        const btn = document.getElementById('unlockTabBtn-' + name);
        const isActive = (name === tab);
        if (panel) panel.classList.toggle('hidden', !isActive);
        if (btn) {
            btn.className = isActive
                ? 'py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer'
                : 'py-2.5 px-4 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer';
        }
    });
}

function filterUnlockRows(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.unlock-row').forEach(function(row) {
        const text = row.getAttribute('data-search') || '';
        row.classList.toggle('hidden', q !== '' && !text.includes(q));
    });
    const exportLink = document.getElementById('unlockExportLink');
    if (exportLink) {
        const base = exportLink.getAttribute('data-base-href');
        const trimmed = (query || '').trim();
        exportLink.href = trimmed ? base + '?search=' + encodeURIComponent(trimmed) : base;
    }
}
</script>
@endsection
