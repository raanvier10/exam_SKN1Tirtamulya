@extends('layouts.admin')
@section('title', 'Pratinjau Import ' . $entityLabel)

@section('content')
<div class="w-full space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Pratinjau Validasi Import {{ $entityLabel }}</h2>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">
                File: <span class="font-semibold text-slate-800">{{ $fileName }}</span> — periksa status validasi setiap baris sebelum disimpan.
            </p>
        </div>
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold hover:border-[#2457E8] hover:text-[#2457E8] shadow-2xs transition self-start sm:self-auto whitespace-nowrap">
            &larr; Ganti File
        </a>
    </div>

    <!-- Main Validation Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-4 sm:p-5 space-y-4">
        <!-- Filter Tabs & Download Error Bar -->
        <div class="border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-5 sm:gap-6 flex-wrap text-xs sm:text-sm">
                <button type="button" onclick="filterPreviewTab('all', this)" class="preview-tab pb-3 -mb-px border-b-2 border-[#2457E8] text-[#142C5C] font-bold transition cursor-pointer whitespace-nowrap">
                    Semua ( {{ $counts['all'] }} )
                </button>
                <button type="button" onclick="filterPreviewTab('ready', this)" class="preview-tab pb-3 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-900 font-medium transition cursor-pointer whitespace-nowrap">
                    Siap ( {{ $counts['ready'] }} )
                </button>
                <button type="button" onclick="filterPreviewTab('duplicate', this)" class="preview-tab pb-3 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-900 font-medium transition cursor-pointer whitespace-nowrap">
                    Duplikat ( {{ $counts['duplicate'] }} )
                </button>
                <button type="button" onclick="filterPreviewTab('error', this)" class="preview-tab pb-3 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-900 font-medium transition cursor-pointer whitespace-nowrap">
                    Bermasalah ( {{ $counts['error'] }} )
                </button>
            </div>

            @if($counts['error'] + $counts['duplicate'] > 0)
                <button type="button" onclick="downloadPreviewErrorsCsv()" class="inline-flex items-center gap-2 px-3.5 py-2 mb-2 sm:mb-2.5 bg-white border border-slate-200 hover:border-[#2457E8] text-slate-700 hover:text-[#2457E8] rounded-xl text-xs font-semibold shadow-2xs transition self-start sm:self-auto whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh Laporan Error (.csv)
                </button>
            @endif
        </div>

        <!-- Preview Table -->
        <div class="rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm" id="importPreviewTable">
                    <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200">
                        <tr class="text-slate-600 text-xs font-semibold">
                            <th class="py-3 px-4 whitespace-nowrap w-12">No</th>
                            <th class="py-3 px-4 whitespace-nowrap">Baris File</th>
                            @foreach($columns as $colLabel)
                                <th class="py-3 px-4 whitespace-nowrap">{{ $colLabel }}</th>
                            @endforeach
                            <th class="py-3 px-4 whitespace-nowrap">Status</th>
                            <th class="py-3 px-4 min-w-[260px]">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($rows as $idx => $row)
                        <tr class="preview-row hover:bg-[#F5F8FF] transition-colors" data-status="{{ $row['status'] }}">
                            <td class="py-3.5 px-4 text-slate-500 font-medium whitespace-nowrap">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 whitespace-nowrap">Baris {{ $row['excel_row'] }}</td>
                            @foreach($columns as $colKey => $colLabel)
                                @php
                                    $cellText = (string) ($row['cells'][$colKey] ?? '-');
                                    $isMono = in_array($colKey, ['nis', 'nip'], true);
                                    $wrapClass = mb_strlen($cellText) <= 32 ? 'whitespace-nowrap' : 'min-w-[200px] leading-snug';
                                @endphp
                                <td class="py-3.5 px-4 text-slate-800 {{ $isMono ? 'tabular-nums text-xs' : 'font-medium' }} {{ $wrapClass }}">
                                    {{ $cellText }}
                                </td>
                            @endforeach
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($row['status'] === 'ready')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Siap
                                    </span>
                                @elseif($row['status'] === 'duplicate')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Duplikat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Error
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs leading-relaxed min-w-[260px] {{ $row['status'] === 'error' ? 'text-rose-600 font-medium' : ($row['status'] === 'duplicate' ? 'text-amber-700 font-medium' : 'text-slate-600') }}">
                                {{ $row['note'] }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($columns) + 4 }}" class="py-10 text-center text-slate-400">
                                Tidak ada baris data pada file ini.
                            </td>
                        </tr>
                        @endforelse
                        <tr id="previewEmptyFilterRow" class="hidden">
                            <td colspan="{{ count($columns) + 4 }}" class="py-10 text-center text-slate-400 text-xs sm:text-sm">
                                Tidak ada data pada kategori ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <a href="{{ $backRoute }}" class="inline-flex items-center justify-center sm:justify-start gap-1.5 text-xs sm:text-sm font-semibold text-slate-600 hover:text-[#2457E8] transition">
                &larr; Kembali / Ganti file
            </a>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <span class="text-xs sm:text-sm text-slate-600 text-center sm:text-right">
                    <strong class="text-slate-900">{{ $counts['ready'] }}</strong> {{ $unitLabel }} siap disimpan
                </span>

                <form action="{{ $actionRoute }}" method="POST" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-70', 'cursor-wait'); btn.textContent = 'Menyimpan...'; }">
                    @csrf
                    <input type="hidden" name="confirm_import" value="1">
                    <input type="hidden" name="import_token" value="{{ $token }}">
                    @if($counts['ready'] > 0)
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan {{ $counts['ready'] }} {{ \Illuminate\Support\Str::title($unitLabel) }}
                        </button>
                    @else
                        <button type="button" disabled class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#2457E8]/45 text-white rounded-xl text-xs sm:text-sm font-bold cursor-not-allowed">
                            Tidak Ada {{ \Illuminate\Support\Str::title($unitLabel) }} Valid
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const previewRowsData = @json($rows);
const previewColumns = @json($columns);

function filterPreviewTab(status, btn) {
    document.querySelectorAll('.preview-tab').forEach(function(tab) {
        tab.className = 'preview-tab pb-3 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-900 font-medium transition cursor-pointer whitespace-nowrap';
    });
    if (btn) {
        btn.className = 'preview-tab pb-3 -mb-px border-b-2 border-[#2457E8] text-[#142C5C] font-bold transition cursor-pointer whitespace-nowrap';
    }

    let visibleCount = 0;
    document.querySelectorAll('.preview-row').forEach(function(row) {
        const show = status === 'all' || row.getAttribute('data-status') === status;
        row.classList.toggle('hidden', !show);
        if (show) visibleCount++;
    });

    const emptyRow = document.getElementById('previewEmptyFilterRow');
    if (emptyRow) {
        emptyRow.classList.toggle('hidden', visibleCount > 0);
    }
}

function downloadPreviewErrorsCsv() {
    const problemRows = previewRowsData.filter(r => r.status === 'error' || r.status === 'duplicate');
    if (!problemRows.length) return;

    const colKeys = Object.keys(previewColumns);
    const header = ['Baris File', ...Object.values(previewColumns), 'Status', 'Keterangan'];
    const escapeCsv = val => {
        let s = String(val ?? '');
        if (/^[=+\-@\t\r]/.test(s)) s = "'" + s;
        return '"' + s.replace(/"/g, '""') + '"';
    };

    const lines = [header.map(escapeCsv).join(',')];
    problemRows.forEach(r => {
        const line = [
            'Baris ' + r.excel_row,
            ...colKeys.map(k => (r.cells && r.cells[k]) ? r.cells[k] : ''),
            r.status === 'duplicate' ? 'Duplikat' : 'Error',
            r.note || ''
        ];
        lines.push(line.map(escapeCsv).join(','));
    });

    const blob = new Blob(['\uFEFF' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'laporan_validasi_import.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}
</script>
@endsection
