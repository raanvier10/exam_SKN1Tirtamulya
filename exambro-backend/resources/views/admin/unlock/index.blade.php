@extends('layouts.admin')
@section('title', 'Buka Kunci Ujian')

@section('content')
<div class="w-full">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-[22px]">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#142C5C] tracking-tight">Buka Kunci Ujian</h2>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">Cari nama atau NIS siswa yang terkunci untuk membuka akses ujian sekaligus mencatat arsip pelanggaran.</p>
        </div>
        <div class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <a id="unlockExportLink"
               data-base-href="{{ route('admin.unlock.export') }}"
               href="{{ route('admin.unlock.export', array_filter(['search' => $search])) }}"
               class="inline-flex items-center gap-[6px] px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-[8px] text-xs sm:text-sm font-semibold hover:bg-slate-50 hover:border-[#2457E8] hover:text-[#2457E8] shadow-2xs transition active:scale-[0.98] whitespace-nowrap">
                <svg class="w-[15px] h-[15px] text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Mode Web Ujian Darurat (Admin / PIC Jurusan) -->
    @if(auth()->user()->isAdmin())
    <div class="card card-body flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0 flex-1">
            <div class="w-10 h-10 rounded-[8px] {{ $webEmergencyEnabled ? 'bg-[#DCE9FF] text-[#2457E8]' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h3 class="card-title text-[#142C5C]">Mode Web Ujian Darurat</h3>
                    @if($webEmergencyEnabled)
                        <span class="inline-flex items-center gap-[6px] text-xs font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span>Aktif</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-[6px] text-xs font-medium text-slate-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                            <span>Nonaktif</span>
                        </span>
                    @endif
                </div>
                <p class="text-[13px] text-slate-500 mt-0.5 leading-snug">
                    Akses ujian browser via <a href="{{ route('web-exam.index') }}" target="_blank" class="font-semibold text-[#2457E8] hover:underline">/darurat</a> bagi siswa yang terkendala APK.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($webEmergencyEnabled && $webEmergencyPin)
                <div class="h-9 px-3 rounded-[8px] bg-[#F5F8FF] border border-[#DCE9FF] inline-flex items-center gap-1.5 whitespace-nowrap">
                    <span class="text-xs font-medium text-slate-500">PIN</span>
                    <span class="text-[13px] font-bold tracking-[0.08em] text-[#142C5C] tabular-nums">{{ $webEmergencyPin }}</span>
                </div>

                <button type="button" onclick="navigator.clipboard?.writeText('{{ route('web-exam.index') }}'); const old = this.textContent; this.textContent='Tersalin'; setTimeout(() => this.textContent = old, 1500);" class="h-9 px-3.5 inline-flex items-center justify-center bg-white border border-slate-200 hover:border-[#2457E8] text-slate-700 hover:text-[#2457E8] rounded-[8px] text-xs font-semibold transition cursor-pointer whitespace-nowrap">
                    Salin Link
                </button>

                <form action="{{ route('admin.unlock.web-emergency.rotate-pin') }}" method="POST" class="m-0 inline-flex">
                    @csrf
                    <button type="submit" class="h-9 px-3.5 inline-flex items-center justify-center bg-white border border-slate-200 hover:border-[#2457E8] text-slate-700 hover:text-[#2457E8] rounded-[8px] text-xs font-semibold transition cursor-pointer whitespace-nowrap">
                        Acak PIN
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.unlock.web-emergency.toggle') }}" method="POST" class="m-0 inline-flex">
                @csrf
                <button type="submit" class="h-9 px-4 inline-flex items-center justify-center {{ $webEmergencyEnabled ? 'bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 hover:border-rose-600' : 'bg-[#2457E8] hover:bg-blue-700 text-white border border-transparent' }} rounded-[8px] text-xs font-bold transition cursor-pointer whitespace-nowrap">
                    {{ $webEmergencyEnabled ? 'Nonaktifkan' : 'Aktifkan Mode Darurat' }}
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Search Bar Card -->
    <div class="card card-body">
        <form method="GET" action="{{ route('admin.unlock.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1 min-w-0">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="unlockSearchInput" name="search" value="{{ $search }}" autofocus autocomplete="off"
                       placeholder="Ketik nama atau NIS siswa yang terkunci..."
                       class="w-full h-[42px] bg-slate-50 focus:bg-white border border-slate-200 rounded-[8px] pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15"
                       oninput="filterUnlockRows(this.value)">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="px-5 h-[42px] bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer">
                    Cari Siswa
                </button>
                @if($search !== '')
                    <a href="{{ route('admin.unlock.index') }}" class="px-4 h-[42px] inline-flex items-center bg-white border border-slate-200 text-slate-600 hover:text-rose-600 rounded-[8px] text-xs sm:text-sm font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Segmented Tabs -->
    <div class="card p-1 grid grid-cols-2 gap-1">
        <button type="button" id="unlockTabBtn-locked" onclick="switchUnlockTab('locked')" class="py-2.5 px-4 rounded-[8px] text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer">
            <span>Siswa Terkunci ({{ $lockedSessions->count() }})</span>
        </button>
        <button type="button" id="unlockTabBtn-archive" onclick="switchUnlockTab('archive')" class="py-2.5 px-4 rounded-[8px] text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer">
            <span>Arsip Buka Kunci ({{ $unlockArchive->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: Siswa Terkunci -->
    <div id="unlockPanel-locked" class="card overflow-hidden">
        <div class="card-header bg-[#0D2149] border-[#142C5C] text-white flex-wrap">
            <h3 class="card-title text-white">Daftar Sesi Terkunci ({{ $lockedSessions->count() }})</h3>
            <span class="text-xs text-blue-200">Klik Buka Kunci untuk mengizinkan siswa melanjutkan ujian</span>
        </div>
        <div class="table-wrap card-body-flush">
            <table class="text-slate-700">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th>Siswa &amp; NIS</th>
                        <th>Kelas</th>
                        <th>Ujian</th>
                        <th class="text-center">Pelanggaran</th>
                        <th>Terkunci Sejak</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lockedSessions as $session)
                    <tr class="unlock-row hover:bg-[#F5F8FF] transition-colors duration-150" data-search="{{ strtolower(($session->user->name ?? '') . ' ' . ($session->user->username ?? '') . ' ' . ($session->exam->title ?? '')) }}">
                        <td>
                            <div class="text-slate-900 font-bold">{{ $session->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 tabular-nums mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span>NIS: {{ $session->user->username ?? '-' }}</span>
                                @if(str_starts_with((string) ($session->device_id ?? ''), 'WEB_DARURAT'))
                                    <span class="px-1.5 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#DCE9FF] text-[#142C5C]">WEB DARURAT</span>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap text-slate-700 font-medium">
                            {{ $session->user->class->name ?? '-' }}
                        </td>
                        <td class="text-slate-800 font-medium">
                            {{ $session->exam->title ?? '-' }}
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-[6px] text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                {{ $session->violations_count }}x Pelanggaran
                            </span>
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-500 tabular-nums">
                            {{ $session->updated_at ? $session->updated_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="text-right whitespace-nowrap">
                            @if($session->exam && $session->user)
                            <div class="inline-flex items-center justify-end gap-2">
                                <form action="{{ route('admin.exams.students.unlock', [$session->exam_id, $session->user_id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Buka kunci ujian untuk {{ $session->user->name }}? Nama siswa tetap tercatat di arsip pelanggaran.');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-[6px] px-3.5 py-2 bg-[#2457E8] hover:bg-blue-700 text-white rounded-[8px] text-xs font-bold shadow-2xs transition cursor-pointer">
                                        Buka Kunci
                                    </button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            Tidak ada siswa yang sedang terkunci saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: Arsip Buka Kunci -->
    <div id="unlockPanel-archive" class="hidden card overflow-hidden">
        <div class="card-header bg-[#0D2149] border-[#142C5C] text-white flex-wrap">
            <h3 class="card-title text-white">Arsip Buka Kunci ({{ $unlockArchive->count() }})</h3>
            <span class="text-xs text-blue-200">Riwayat siswa yang pernah terkunci dan telah dibuka kembali</span>
        </div>
        <div class="table-wrap card-body-flush">
            <table class="text-slate-700">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th>Siswa &amp; NIS</th>
                        <th>Kelas</th>
                        <th>Ujian</th>
                        <th class="text-center">Total Pelanggaran</th>
                        <th>Waktu Buka Kunci</th>
                        <th class="text-center">Status Sesi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($unlockArchive as $item)
                    <tr class="unlock-row hover:bg-[#F5F8FF] transition-colors duration-150" data-search="{{ strtolower(($item->user->name ?? '') . ' ' . ($item->user->username ?? '') . ' ' . ($item->exam->title ?? '')) }}">
                        <td>
                            <div class="text-slate-900 font-bold">{{ $item->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 tabular-nums mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span>NIS: {{ $item->user->username ?? '-' }}</span>
                                @if(str_starts_with((string) ($item->device_id ?? ''), 'WEB_DARURAT'))
                                    <span class="px-1.5 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#DCE9FF] text-[#142C5C]">WEB DARURAT</span>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap text-slate-700 font-medium">
                            {{ $item->user->class->name ?? '-' }}
                        </td>
                        <td class="text-slate-800 font-medium">
                            {{ $item->exam->title ?? '-' }}
                        </td>
                        <td class="text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-[6px] text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                {{ $item->violations_count }}x Pelanggaran
                            </span>
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-600 font-medium tabular-nums">
                            {{ $item->unlocked_at ? $item->unlocked_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="text-center whitespace-nowrap text-xs font-semibold">
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
                        <td colspan="6" class="py-12 text-center text-slate-400">
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
                ? 'py-2.5 px-4 rounded-[8px] text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer'
                : 'py-2.5 px-4 rounded-[8px] text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer';
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
