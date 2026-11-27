@props([
    'exam',
    'timeOnly' => false,
    'padded' => false,
])

@php
    $start = \Carbon\Carbon::parse($exam->start_at);
    $end = $exam->end_at ? \Carbon\Carbon::parse($exam->end_at) : null;
    $isExpired = $exam->end_at && now()->gt($exam->end_at);
    $notStarted = now()->lt($exam->start_at);
    $isLocked = $exam->participant_status === 'locked';
    $isFinished = $exam->participant_status === 'finished';
    $isDone = $isLocked || $isFinished || $isExpired;
    $isUpcoming = !$isDone && $notStarted;
    $isRunning = !$isDone && !$isUpcoming;
    $timeRange = $timeOnly
        ? $start->format('H:i') . ($end ? ' – ' . $end->format('H:i') : '') . ' WIB'
        : $start->format('d M Y, H:i') . ' WIB' . ($end ? ' – ' . $end->format('H:i') . ' WIB' : '');
@endphp

<div class="{{ $padded ? 'p-4 sm:px-6' : '' }}">
    <div class="rounded-[12px] bg-white border border-slate-200 p-4 space-y-3">
        <div class="flex items-baseline justify-between gap-3">
            <h4 class="text-[15.5px] sm:text-[16px] font-bold leading-snug min-w-0 {{ $isDone ? 'text-slate-500' : 'text-[#142C5C]' }}">
                {{ $exam->title }}
            </h4>
            <span class="text-xs font-medium text-slate-500 tabular-nums whitespace-nowrap shrink-0">
                {{ $exam->duration }} menit
            </span>
        </div>

        <div class="inline-flex items-center gap-1.5 text-[13px] font-normal text-slate-500 tabular-nums">
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>{{ $timeRange }}</span>
        </div>

        @if($isRunning)
            <a href="{{ route('portal.exam', $exam) }}" class="w-full h-[44px] px-4 inline-flex items-center justify-center gap-2 bg-[#142C5C] hover:bg-[#0D2149] text-white rounded-[10px] text-sm font-bold transition active:scale-[0.98]">
                <span>{{ $exam->participant_status === 'working' ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}</span>
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        @elseif($isLocked)
            <div class="w-full h-[40px] px-4 inline-flex items-center justify-center rounded-[10px] bg-slate-100 text-rose-700 text-xs font-semibold">
                Terkunci — Hubungi PIC
            </div>
        @elseif($isFinished)
            <div class="w-full h-[40px] px-4 inline-flex items-center justify-center rounded-[10px] bg-slate-100 text-slate-700 text-xs font-semibold">
                Sudah Selesai
            </div>
        @elseif($isExpired)
            <div class="w-full h-[40px] px-4 inline-flex items-center justify-center rounded-[10px] bg-slate-100 text-slate-500 text-xs font-medium">
                Waktu Berakhir
            </div>
        @else
            <div class="w-full h-[40px] px-4 inline-flex items-center justify-center rounded-[10px] bg-slate-100 text-slate-600 text-xs font-semibold">
                Belum Dimulai
            </div>
        @endif
    </div>
</div>
