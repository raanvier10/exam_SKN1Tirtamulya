@extends('layouts.admin')
@section('title', 'Buka Kunci Ujian')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Buka Kunci Ujian</h2>
        <p class="text-xs sm:text-sm text-slate-600 mt-1">Cari nama atau NIS siswa yang terkunci untuk membuka akses ujian sekaligus mencatat arsip pelanggaran.</p>
    </div>

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
                       class="w-full h-[44px] bg-white border border-slate-200 rounded-xl pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15"
                       oninput="filterUnlockRows(this.value)">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="px-5 h-[44px] bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer">
                    Cari Siswa
                </button>
                @if($search !== '')
                    <a href="{{ route('admin.unlock.index') }}" class="px-4 h-[44px] inline-flex items-center bg-white border border-slate-200 text-slate-600 hover:text-rose-600 rounded-xl text-xs sm:text-sm font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Segmented Tabs -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-1 grid grid-cols-2 gap-1">
        <button type="button" id="unlockTabBtn-locked" onclick="switchUnlockTab('locked')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 bg-[#1E40AF] text-white shadow-2xs cursor-pointer">
            <span>Siswa Terkunci ({{ $lockedSessions->count() }})</span>
        </button>
        <button type="button" id="unlockTabBtn-archive" onclick="switchUnlockTab('archive')" class="py-2.5 px-4 rounded-lg text-xs sm:text-sm font-medium transition flex items-center justify-center gap-2 text-slate-600 hover:text-[#142C5C] hover:bg-slate-50 cursor-pointer">
            <span>Arsip Buka Kunci ({{ $unlockArchive->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: Siswa Terkunci -->
    <div id="unlockPanel-locked" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Daftar Sesi Terkunci</h3>
            <p class="text-xs text-slate-500 mt-0.5">Klik tombol Buka Kunci untuk mengizinkan siswa melanjutkan ujian. Riwayat pelanggaran tetap tersimpan di arsip.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#0D2149] text-white text-[11px] uppercase tracking-wider font-bold">
                        <th class="py-3.5 px-6">Siswa &amp; NIS</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Kelas</th>
                        <th class="py-3.5 px-6">Ujian</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Pelanggaran</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Terkunci Sejak</th>
                        <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($lockedSessions as $session)
                    <tr class="unlock-row hover:bg-[#F5F8FF] transition-colors" data-search="{{ strtolower(($session->user->name ?? '') . ' ' . ($session->user->username ?? '') . ' ' . ($session->exam->title ?? '')) }}">
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-900">{{ $session->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 font-mono mt-0.5">NIS: {{ $session->user->username ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-slate-700 font-medium">
                            {{ $session->user->class->name ?? '-' }}
                        </td>
                        <td class="py-4 px-6 text-slate-800 font-medium">
                            {{ $session->exam->title ?? '-' }}
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap font-semibold text-rose-700">
                            {{ $session->violations_count }}x Pelanggaran
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-500">
                            {{ $session->updated_at ? $session->updated_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            @if($session->exam && $session->user)
                            <form action="{{ route('admin.exams.students.unlock', [$session->exam_id, $session->user_id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Buka kunci ujian untuk {{ $session->user->name }}? Nama siswa tetap tercatat di arsip pelanggaran.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-2xs transition cursor-pointer">
                                    Buka Kunci
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6 text-center text-slate-400 text-sm">
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
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm sm:text-base font-bold text-[#142C5C]">Arsip Siswa Pernah Dibuka Kuncinya</h3>
            <p class="text-xs text-slate-500 mt-0.5">Catatan siswa yang pernah terkunci akibat pelanggaran dan telah dibuka kembali.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#0D2149] text-white text-[11px] uppercase tracking-wider font-bold">
                        <th class="py-3.5 px-6">Siswa &amp; NIS</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Kelas</th>
                        <th class="py-3.5 px-6">Ujian</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Total Pelanggaran</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Waktu Buka Kunci</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">Status Sesi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($unlockArchive as $item)
                    <tr class="unlock-row hover:bg-[#F5F8FF] transition-colors" data-search="{{ strtolower(($item->user->name ?? '') . ' ' . ($item->user->username ?? '') . ' ' . ($item->exam->title ?? '')) }}">
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-900">{{ $item->user->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 font-mono mt-0.5">NIS: {{ $item->user->username ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-slate-700 font-medium">
                            {{ $item->user->class->name ?? '-' }}
                        </td>
                        <td class="py-4 px-6 text-slate-800 font-medium">
                            {{ $item->exam->title ?? '-' }}
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap font-semibold text-amber-700">
                            {{ $item->violations_count }}x Pelanggaran
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-600 font-medium">
                            {{ $item->unlocked_at ? $item->unlocked_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-xs font-semibold">
                            @if($item->status === 'LOCKED')
                                <span class="text-rose-700">Terkunci Lagi</span>
                            @elseif($item->status === 'FINISHED')
                                <span class="text-[#2457E8]">Selesai</span>
                            @else
                                <span class="text-emerald-700">Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6 text-center text-slate-400 text-sm">
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
}
</script>
@endsection
