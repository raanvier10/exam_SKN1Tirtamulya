@props([
    'label',
    'value' => 0,
    'tone' => 'blue',
    'icon' => 'clock',
])

@php
    $toneClasses = match ($tone) {
        'amber' => 'bg-amber-50 text-amber-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        default => 'bg-blue-50 text-blue-700',
    };
@endphp

<div class="stat-card bg-white h-full flex flex-col items-center justify-center text-center">
    <div class="w-10 h-10 rounded-full {{ $toneClasses }} flex items-center justify-center shrink-0" aria-hidden="true">
        @if($icon === 'monitor')
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        @elseif($icon === 'check')
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        @else
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        @endif
    </div>

    <div class="mt-2 text-2xl font-extrabold text-[#142C5C] tabular-nums leading-none">
        {{ $value }}
    </div>

    <div class="mt-1.5 text-[10.5px] sm:text-xs font-bold uppercase tracking-[0.04em] text-slate-600 leading-tight">
        {{ $label }}
    </div>
</div>
