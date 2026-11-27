@props(['exam'])

@php
    $isExpired = $exam->end_at && now()->gt($exam->end_at);
    $notStarted = now()->lt($exam->start_at);
@endphp

@if($exam->participant_status === 'locked')
    <span class="text-xs font-medium text-rose-700/80 whitespace-nowrap">Terkunci — Hubungi PIC</span>
@elseif($exam->participant_status === 'finished')
    <span class="text-xs font-medium text-emerald-700/85 whitespace-nowrap">Sudah Selesai</span>
@elseif($isExpired)
    <span class="text-xs font-medium text-rose-700/80 whitespace-nowrap">Waktu Berakhir</span>
@elseif($notStarted)
    <span class="text-xs font-medium text-blue-700 whitespace-nowrap">Belum Dimulai</span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11.5px] font-medium whitespace-nowrap">
        Berlangsung
    </span>
@endif
