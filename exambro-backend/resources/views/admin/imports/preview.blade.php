@extends('layouts.admin')
@section('title', 'Pratinjau Import ' . $entityLabel)

@section('content')
<div class="w-full max-w-[1200px]">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[24px] font-bold text-[#0F172A] tracking-[-0.01em] leading-[1.3]">
                Pratinjau Validasi Import {{ $entityLabel }}
            </h1>
            <p class="text-[14px] font-normal text-[#64748B] leading-[1.5] mt-1">
                File: <span class="font-medium text-[#0F172A]">{{ $fileName }}</span> — periksa status validasi setiap baris sebelum disimpan.
            </p>
        </div>

        <a href="{{ $backRoute }}"
           class="inline-flex items-center justify-center h-[40px] px-[16px] bg-white border border-[#E2E8F0] hover:bg-[#F8FAFC] text-[#0F172A] rounded-[8px] text-[14px] font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#142C5C]/10 self-start sm:self-auto whitespace-nowrap">
            Ganti File
        </a>
    </div>

    <!-- Card Utama -->
    <div class="bg-white border border-[#E2E8F0] rounded-[12px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] p-6">
        <!-- Baris Tab & Unduh Laporan Error -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E2E8F0] pb-3 mb-6">
            <div class="flex items-center gap-6 flex-wrap" role="tablist">
                <button type="button" onclick="filterPreviewTab('all', this)"
                        class="preview-tab inline-flex items-center gap-2 pb-[13px] -mb-[13px] border-b-2 border-[#142C5C] text-[14px] font-semibold text-[#0F172A] transition-colors duration-150 cursor-pointer whitespace-nowrap focus:outline-none">
                    <span>Semua</span>
                    <span class="preview-tab-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#0F172A] tabular-nums">{{ $counts['all'] }}</span>
                </button>
                <button type="button" onclick="filterPreviewTab('ready', this)"
                        class="preview-tab inline-flex items-center gap-2 pb-[13px] -mb-[13px] border-b-2 border-transparent text-[14px] font-medium text-[#64748B] hover:text-[#0F172A] transition-colors duration-150 cursor-pointer whitespace-nowrap focus:outline-none">
                    <span>Siap</span>
                    <span class="preview-tab-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#64748B] tabular-nums">{{ $counts['ready'] }}</span>
                </button>
                <button type="button" onclick="filterPreviewTab('duplicate', this)"
                        class="preview-tab inline-flex items-center gap-2 pb-[13px] -mb-[13px] border-b-2 border-transparent text-[14px] font-medium text-[#64748B] hover:text-[#0F172A] transition-colors duration-150 cursor-pointer whitespace-nowrap focus:outline-none">
                    <span>Duplikat</span>
                    <span class="preview-tab-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#64748B] tabular-nums">{{ $counts['duplicate'] }}</span>
                </button>
                <button type="button" onclick="filterPreviewTab('error', this)"
                        class="preview-tab inline-flex items-center gap-2 pb-[13px] -mb-[13px] border-b-2 border-transparent text-[14px] font-medium text-[#64748B] hover:text-[#0F172A] transition-colors duration-150 cursor-pointer whitespace-nowrap focus:outline-none">
                    <span>Bermasalah</span>
                    <span class="preview-tab-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#64748B] tabular-nums">{{ $counts['error'] }}</span>
                </button>
            </div>

            @if($counts['error'] + $counts['duplicate'] > 0)
                <button type="button" onclick="downloadPreviewErrorsCsv()"
                        class="inline-flex items-center justify-center gap-[8px] h-[40px] px-[16px] bg-white border border-[#E2E8F0] hover:bg-[#F8FAFC] text-[#0F172A] rounded-[8px] text-[14px] font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#142C5C]/10 whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4 text-[#64748B] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Laporan Error (.csv)</span>
                </button>
            @endif
        </div>

        <!-- Tabel Data (satu container border tipis, tanpa card di dalam card) -->
        <div class="overflow-x-auto border border-[#E2E8F0]">
            <table class="w-full text-left border-collapse" id="importPreviewTable">
                <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                    <tr>
                        <th class="py-[12px] px-[16px] w-[56px] text-center text-[12px] font-semibold uppercase tracking-[0.04em] text-[#64748B] whitespace-nowrap">No</th>
                        <th class="py-[12px] px-[16px] w-[100px] text-center text-[12px] font-semibold uppercase tracking-[0.04em] text-[#64748B] whitespace-nowrap">Baris File</th>
                        @foreach($columns as $colKey => $colLabel)
                            @php
                                $colWidth = match ($colKey) {
                                    'schedule' => 'w-[220px]',
                                    'classes' => 'w-[180px]',
                                    default => '',
                                };
                            @endphp
                            <th class="py-[12px] px-[16px] {{ $colWidth }} text-left text-[12px] font-semibold uppercase tracking-[0.04em] text-[#64748B] whitespace-nowrap">{{ $colLabel }}</th>
                        @endforeach
                        <th class="py-[12px] px-[16px] w-[100px] text-left text-[12px] font-semibold uppercase tracking-[0.04em] text-[#64748B] whitespace-nowrap">Status</th>
                        <th class="py-[12px] px-[16px] min-w-[260px] text-left text-[12px] font-semibold uppercase tracking-[0.04em] text-[#64748B]">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9] bg-white">
                    @forelse($rows as $idx => $row)
                    @php
                        $missingClassesMap = [];
                        if ($row['status'] === 'error' && preg_match("/Kelas '([^']+)' tidak ditemukan/i", (string) ($row['note'] ?? ''), $missingMatch)) {
                            foreach (array_filter(array_map('trim', explode(',', $missingMatch[1]))) as $mc) {
                                $missingClassesMap[mb_strtolower($mc)] = true;
                            }
                        }
                        $noteText = trim((string) ($row['note'] ?? ''));
                        $noteText = str_replace([' (akan dilewati)', '(akan dilewati)'], [' Baris ini akan dilewati.', 'Baris ini akan dilewati.'], $noteText);
                    @endphp
                    <tr class="preview-row min-h-[56px] hover:bg-[#F8FAFC] transition-colors duration-150" data-status="{{ $row['status'] }}">
                        <td class="py-[12px] px-[16px] text-center text-[14px] font-normal text-[#64748B] tabular-nums whitespace-nowrap">{{ $idx + 1 }}</td>
                        <td class="py-[12px] px-[16px] text-center text-[14px] font-normal text-[#0F172A] tabular-nums whitespace-nowrap">Baris {{ $row['excel_row'] }}</td>
                        @foreach($columns as $colKey => $colLabel)
                            @php
                                $cellText = (string) ($row['cells'][$colKey] ?? '-');
                            @endphp
                            <td class="py-[12px] px-[16px] text-left text-[14px] leading-[1.5] text-[#0F172A] {{ $colKey === 'title' ? 'font-medium' : 'font-normal' }}">
                                @if($colKey === 'classes' && $cellText !== '-' && !str_starts_with($cellText, 'Semua Kelas'))
                                    @php $classParts = array_values(array_filter(array_map('trim', explode(',', $cellText)))); @endphp
                                    <span class="leading-[1.5]">
                                        @foreach($classParts as $clsPart)
                                            @php $isMissingClass = isset($missingClassesMap[mb_strtolower($clsPart)]); @endphp
                                            <span class="{{ $isMissingClass ? 'text-[#B91C1C] font-medium' : 'text-[#0F172A]' }}">{{ $clsPart }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                    </span>
                                @elseif($colKey === 'schedule')
                                    <span class="whitespace-nowrap tabular-nums">{{ $cellText }}</span>
                                @else
                                    <span class="{{ in_array($colKey, ['nis', 'nip'], true) ? 'tabular-nums whitespace-nowrap' : '' }}">{{ $cellText }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="py-[12px] px-[16px] whitespace-nowrap">
                            @if($row['status'] === 'ready')
                                <span class="inline-flex items-center gap-1.5 px-[10px] py-[2px] rounded-full text-[12px] font-medium bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]">
                                    <span class="w-[6px] h-[6px] rounded-full bg-[#047857] shrink-0"></span>
                                    <span>Siap</span>
                                </span>
                            @elseif($row['status'] === 'duplicate')
                                <span class="inline-flex items-center gap-1.5 px-[10px] py-[2px] rounded-full text-[12px] font-medium bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A]">
                                    <span class="w-[6px] h-[6px] rounded-full bg-[#B45309] shrink-0"></span>
                                    <span>Duplikat</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-[10px] py-[2px] rounded-full text-[12px] font-medium bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]">
                                    <span class="w-[6px] h-[6px] rounded-full bg-[#B91C1C] shrink-0"></span>
                                    <span>Error</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-[12px] px-[16px] text-[13px] font-normal leading-[1.5] min-w-[260px] {{ $row['status'] === 'error' ? 'text-[#B91C1C]' : ($row['status'] === 'duplicate' ? 'text-[#B45309]' : 'text-[#64748B]') }}">
                            {{ $noteText }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($columns) + 4 }}" class="py-10 px-4 text-center text-[14px] font-normal text-[#64748B]">
                            Tidak ada baris data pada file ini.
                        </td>
                    </tr>
                    @endforelse
                    <tr id="previewEmptyFilterRow" class="hidden">
                        <td colspan="{{ count($columns) + 4 }}" class="py-10 px-4 text-center text-[14px] font-normal text-[#64748B]">
                            Tidak ada data pada kategori ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Aksi -->
        <div class="border-t border-[#E2E8F0] pt-4 mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ $backRoute }}"
               class="inline-flex items-center justify-center h-[40px] px-[16px] bg-white border border-[#E2E8F0] hover:bg-[#F8FAFC] text-[#0F172A] rounded-[8px] text-[14px] font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#142C5C]/10 self-start sm:self-auto whitespace-nowrap">
                Kembali
            </a>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <div class="text-left sm:text-right">
                    <span class="text-[14px] font-normal text-[#64748B]">
                        <strong class="font-semibold text-[#0F172A] tabular-nums">{{ $counts['ready'] }}</strong> {{ $unitLabel }} siap disimpan
                    </span>
                    @if($counts['ready'] === 0)
                        <p class="text-[12px] font-normal text-[#64748B] mt-0.5">Perbaiki data bermasalah untuk melanjutkan</p>
                    @endif
                </div>

                <form action="{{ $actionRoute }}" method="POST" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-75', 'cursor-wait'); btn.textContent = 'Menyimpan...'; }">
                    @csrf
                    <input type="hidden" name="confirm_import" value="1">
                    <input type="hidden" name="import_token" value="{{ $token }}">
                    @if($counts['ready'] > 0)
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center h-[40px] px-[20px] bg-[#142C5C] hover:bg-[#1D4ED8] text-white rounded-[8px] text-[14px] font-semibold transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#142C5C]/10 cursor-pointer whitespace-nowrap">
                            Simpan {{ \Illuminate\Support\Str::title($unitLabel) }}
                        </button>
                    @else
                        <button type="button" disabled
                                title="Perbaiki data bermasalah untuk melanjutkan"
                                class="w-full sm:w-auto inline-flex items-center justify-center h-[40px] px-[20px] bg-[#E5E7EB] text-[#9CA3AF] rounded-[8px] text-[14px] font-medium cursor-not-allowed whitespace-nowrap">
                            Simpan {{ \Illuminate\Support\Str::title($unitLabel) }}
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
        tab.className = 'preview-tab inline-flex items-center gap-2 pb-[13px] -mb-[13px] border-b-2 border-transparent text-[14px] font-medium text-[#64748B] hover:text-[#0F172A] transition-colors duration-150 cursor-pointer whitespace-nowrap focus:outline-none';
        const badge = tab.querySelector('.preview-tab-badge');
        if (badge) {
            badge.className = 'preview-tab-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#64748B] tabular-nums';
        }
    });
    if (btn) {
        btn.className = 'preview-tab inline-flex items-center gap-2 pb-[13px] -mb-[13px] border-b-2 border-[#142C5C] text-[14px] font-semibold text-[#0F172A] transition-colors duration-150 cursor-pointer whitespace-nowrap focus:outline-none';
        const activeBadge = btn.querySelector('.preview-tab-badge');
        if (activeBadge) {
            activeBadge.className = 'preview-tab-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#0F172A] tabular-nums';
        }
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
