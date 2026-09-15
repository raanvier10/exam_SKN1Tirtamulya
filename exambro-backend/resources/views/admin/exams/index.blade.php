@extends('layouts.admin')
@section('title', 'Manajemen Ujian')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Jadwal Ujian</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola sesi ujian online dan tautan Google Form.</p>
    </div>
    <div class="grid grid-cols-2 sm:flex items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
        <button onclick="importModal.showModal()" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200/80 text-slate-700 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 hover:border-slate-300 hover:text-indigo-600 shadow-xs transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span>Import Excel</span>
        </button>
        <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98] w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Ujian</span>
        </a>
    </div>
</div>

<dialog id="importModal" class="backdrop:bg-slate-900/40 backdrop:backdrop-blur-xs p-0 rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto m-auto">
    <div class="px-5 py-4 border-b border-slate-100 bg-white">
        <h3 class="text-lg font-bold text-slate-900">Import Jadwal Ujian</h3>
        <p class="text-sm text-slate-500 mt-0.5">Pastikan format kolom sesuai dengan template.</p>
    </div>
    
    <form action="{{ route('admin.exams.import') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-white space-y-4">
        @csrf
        
        <div class="flex items-start gap-3 p-4 bg-indigo-50/40 border border-indigo-100 rounded-xl">
            <div class="p-2 bg-white shadow-xs border border-indigo-100 text-indigo-600 rounded-lg shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-900">Format Wajib Sesuai!</h4>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Kolom <code>target_kelas</code> bisa diisi nama kelas (misal: <em>11 TJKT 1, 11 TJKT 2</em>) atau kosongkan untuk Semua Kelas. Data yang sudah ada akan otomatis dilewati.</p>
                <a href="{{ route('admin.exams.template') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    Download Template CSV &rarr;
                </a>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File Excel/CSV</label>
            <div class="mt-1 flex justify-center rounded-xl border-2 border-dashed border-slate-200 px-6 py-7 hover:border-indigo-400 hover:bg-indigo-50/20 transition-all cursor-pointer group" onclick="document.getElementById('file-upload-exams').click()">
                <div class="text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <div class="mt-3 flex text-sm leading-6 text-slate-600 justify-center">
                        <span class="relative font-semibold text-indigo-600 hover:text-indigo-500">
                            Click to upload
                            <input id="file-upload-exams" name="file" type="file" accept=".xlsx,.xls,.csv" required class="sr-only" onchange="document.getElementById('file-name-exams').textContent = this.files[0].name">
                        </span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs leading-5 text-slate-400 mt-0.5" id="file-name-exams">CSV, XLS, XLSX up to 10MB</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-2">
            <button type="button" onclick="importModal.close()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition-colors shadow-xs">Batal</button>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm shadow-indigo-500/20 transition-colors">Upload Data</button>
        </div>
    </form>
</dialog>

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs mb-6 p-3.5 sm:p-4">
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 sm:gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                id="searchExam" 
                placeholder="Cari judul ujian..." 
                value="{{ request('search') }}"
                class="w-full pl-10 pr-9 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
            >
            <button id="clearSearch" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1 sm:w-44">
                <select id="filterClass" class="w-full appearance-none pl-3 pr-7 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ strtolower($c->name) }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div class="relative flex-1 sm:w-36">
                <select id="filterStatus" class="w-full appearance-none pl-3 pr-7 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <button id="resetFilters" class="h-9 w-9 shrink-0 flex items-center justify-center text-slate-500 hover:text-indigo-600 bg-slate-50 hover:bg-indigo-50 rounded-xl border border-slate-200/80 hover:border-indigo-200 transition-all active:scale-95" title="Reset Filter">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Bulk Delete Floating Bar -->
<div id="bulkBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white rounded-2xl shadow-2xl px-5 py-3 flex items-center gap-4 transition-all">
    <span class="text-sm font-semibold"><span id="bulkCount">0</span> dipilih</span>
    <button type="button" id="bulkDeleteBtn" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all active:scale-[0.98]">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Hapus Terpilih
    </button>
    <button type="button" id="bulkCancelBtn" class="text-xs text-slate-400 hover:text-white transition-colors">Batal</button>
</div>

<!-- Hidden bulk delete form -->
<form id="bulkDeleteForm" action="{{ route('admin.exams.bulk-delete') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- Dual Mode: Desktop Table + Mobile Standalone Cards -->

<!-- 1. Desktop Table View (>= md) -->
<div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                <tr>
                    <th class="px-4 py-4 w-10">
                        <input type="checkbox" id="selectAllDesktop" class="rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer">
                    </th>
                    <th class="px-6 py-4 min-w-[200px]">Judul Ujian</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[170px]">Target Kelas</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[150px]">Waktu</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[90px]">Durasi</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[130px]">Toleransi</th>
                    <th class="px-6 py-4 whitespace-nowrap min-w-[100px]">Status</th>
                    <th class="px-6 py-4 whitespace-nowrap text-right min-w-[180px]">Aksi</th>
                </tr>
            </thead>
            <tbody id="examTableBody" class="divide-y divide-slate-100">
                @forelse($exams as $e)
                @php
                    $classList = $e->classes->pluck('name')->implode(', ');
                    $classCount = $e->classes->count();
                    $canManage = auth()->user()->isAdmin() || ($e->created_by === auth()->id());
                @endphp
                <tr class="exam-row hover:bg-indigo-50/20 transition-colors duration-150 group" 
                    data-title="{{ strtolower($e->title) }}" 
                    data-classes="{{ strtolower($classList ?: 'semua kelas') }}" 
                    data-status="{{ $e->status }}"
                    data-exam-id="{{ $e->id }}"
                    data-can-manage="{{ $canManage ? '1' : '0' }}">
                    <td class="px-4 py-4 align-middle">
                        @if($canManage)
                        <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer" value="{{ $e->id }}">
                        @endif
                    </td>
                    <td class="px-6 py-4 font-semibold text-slate-900 align-middle">
                        <div class="line-clamp-2 max-w-sm">{{ $e->title }}</div>
                        <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                            @if($e->created_by === auth()->id())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Ujian Anda
                                </span>
                            @elseif($e->creator)
                                <span class="text-slate-400">
                                    Oleh: <span class="text-slate-600 font-medium">{{ $e->creator->name }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-medium border border-slate-200">
                                    UAS Umum
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                        @if($classCount === 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                Semua Kelas
                            </span>
                        @elseif($classCount <= 2)
                            <div class="inline-flex items-center gap-1.5 flex-wrap">
                                @foreach($e->classes as $cls)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $cls->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $e->classes->first()->name }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-indigo-100 text-slate-700 hover:text-indigo-800 border border-slate-200 transition-colors cursor-help" title="{{ $classList }}">
                                    +{{ $classCount - 1 }} Kelas
                                </span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                        <div class="text-slate-900 font-semibold text-xs">{{ \Carbon\Carbon::parse($e->start_at)->format('d M Y') }}</div>
                        <div class="text-slate-400 text-xs mt-0.5 font-mono">
                            {{ \Carbon\Carbon::parse($e->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($e->end_at)->format('H:i') }} WIB
                        </div>
                    </td>
                    <td class="px-6 py-4 align-middle whitespace-nowrap font-medium text-slate-700">
                        {{ $e->duration }} mnt
                    </td>
                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Max {{ $e->max_violation }}x Keluar
                        </span>
                    </td>
                    <td class="px-6 py-4 align-middle whitespace-nowrap">
                        @if($e->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 align-middle whitespace-nowrap text-right">
                        <div class="inline-flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.exams.show', $e->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold hover:bg-indigo-600 hover:text-white shadow-2xs transition-all duration-150" title="Monitor & Buka Kunci Siswa">
                                Monitor
                            </a>
                            @if($canManage)
                                <a href="{{ route('admin.exams.edit', $e->id) }}" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 shadow-2xs transition-all duration-150">Edit</a>
                                <form action="{{ route('admin.exams.destroy', $e->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus ujian ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-2xs transition-all duration-150">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="emptyStaticRow">
                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                        Belum ada jadwal ujian terdaftar.
                    </td>
                </tr>
                @endforelse
                <tr id="noMatchRow" class="hidden">
                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                        Tidak ada jadwal ujian yang cocok dengan pencarian / filter.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Mobile Standalone Cards View (< md) -->
<div class="md:hidden space-y-3.5" id="examMobileList">
    @forelse($exams as $e)
    @php
        $classList = $e->classes->pluck('name')->implode(', ');
        $classCount = $e->classes->count();
        $canManage = auth()->user()->isAdmin() || ($e->created_by === auth()->id());
    @endphp
    <div class="exam-card bg-white rounded-2xl border-2 border-slate-200 hover:border-slate-300 shadow-xs p-4 sm:p-5 space-y-3 transition-all"
         data-title="{{ strtolower($e->title) }}" 
         data-classes="{{ strtolower($classList ?: 'semua kelas') }}" 
         data-status="{{ $e->status }}"
         data-exam-id="{{ $e->id }}"
         data-can-manage="{{ $canManage ? '1' : '0' }}">
        <!-- Card Top Bar -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2.5 min-w-0 flex-1">
                @if($canManage)
                <input type="checkbox" class="bulk-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300 w-4 h-4 cursor-pointer mt-1 shrink-0" value="{{ $e->id }}">
                @endif
                <div class="space-y-1.5 min-w-0">
                    <h4 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $e->title }}</h4>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">
                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $e->duration }} Menit
                        </span>
                        @if($e->created_by === auth()->id())
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-lg">
                                <svg class="w-3 h-3 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Ujian Anda
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="shrink-0 pt-0.5">
                @if($e->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/30 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-400/30 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                    </span>
                @endif
            </div>
        </div>

        <!-- Card Body -->
        <div class="space-y-2 py-2.5 px-3.5 bg-slate-50/90 rounded-xl border border-slate-200">
            <div class="flex items-start justify-between gap-3 text-xs">
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-0.5">Target Kelas</span>
                    <span class="font-semibold text-slate-700 text-xs block truncate" title="{{ $classList ?: 'Semua Kelas' }}">
                        {{ $classCount === 0 ? 'Semua Kelas' : ($classCount <= 2 ? $classList : $e->classes->first()->name . ' +' . ($classCount - 1) . ' kelas') }}
                    </span>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-0.5">Toleransi</span>
                    <span class="font-bold text-rose-600 text-xs">Max {{ $e->max_violation }}x Keluar</span>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                <span>{{ \Carbon\Carbon::parse($e->start_at)->format('d M Y') }}</span>
                <span class="font-mono font-medium text-slate-700">{{ \Carbon\Carbon::parse($e->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($e->end_at)->format('H:i') }} WIB</span>
            </div>
        </div>

        <!-- Mobile Action Buttons -->
        <div class="flex items-center gap-2 pt-1">
            <a href="{{ route('admin.exams.show', $e->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all active:scale-[0.98]">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Pantau Live</span>
            </a>
            @if($canManage)
                <a href="{{ route('admin.exams.edit', $e->id) }}" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-all active:scale-[0.98]">
                    Edit
                </a>
                <form action="{{ route('admin.exams.destroy', $e->id) }}" method="POST" class="inline-flex m-0" onsubmit="return confirm('Hapus ujian ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 text-slate-700 rounded-xl text-xs font-semibold transition-all active:scale-[0.98]">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="p-8 text-center text-slate-400 text-xs bg-white rounded-2xl border-2 border-slate-200">
        Belum ada jadwal ujian terdaftar.
    </div>
    @endforelse
    <div id="noMatchMobile" class="hidden p-8 text-center text-slate-400 text-xs bg-white rounded-2xl border-2 border-slate-200">
        Tidak ada jadwal ujian yang cocok dengan pencarian / filter.
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchExam');
        const clearBtn = document.getElementById('clearSearch');
        const classFilter = document.getElementById('filterClass');
        const statusFilter = document.getElementById('filterStatus');
        const resetBtn = document.getElementById('resetFilters');
        const rows = document.querySelectorAll('.exam-row');
        const cards = document.querySelectorAll('.exam-card');
        const noMatchRow = document.getElementById('noMatchRow');
        const noMatchMobile = document.getElementById('noMatchMobile');

        function filterExams() {
            const query = searchInput.value.toLowerCase().trim();
            const selectedClass = classFilter.value.toLowerCase().trim();
            const selectedStatus = statusFilter.value.toLowerCase().trim();

            clearBtn.classList.toggle('hidden', query === '');

            let visibleCount = 0;

            function checkItem(el) {
                const title = el.getAttribute('data-title') || '';
                const classes = el.getAttribute('data-classes') || '';
                const status = el.getAttribute('data-status') || '';

                const matchesQuery = query === '' || title.includes(query) || classes.includes(query);
                const matchesClass = selectedClass === '' || classes.includes(selectedClass) || classes.includes('semua kelas');
                const matchesStatus = selectedStatus === '' || status === selectedStatus;

                if (matchesQuery && matchesClass && matchesStatus) {
                    el.style.display = '';
                    return true;
                } else {
                    el.style.display = 'none';
                    return false;
                }
            }

            rows.forEach(row => {
                if (checkItem(row)) visibleCount++;
            });

            cards.forEach(card => {
                checkItem(card);
            });

            const hasItems = rows.length > 0 || cards.length > 0;
            if (noMatchRow) {
                noMatchRow.classList.toggle('hidden', !hasItems || visibleCount > 0);
            }
            if (noMatchMobile) {
                noMatchMobile.classList.toggle('hidden', !hasItems || visibleCount > 0);
            }
        }

        searchInput.addEventListener('input', filterExams);
        classFilter.addEventListener('change', filterExams);
        statusFilter.addEventListener('change', filterExams);

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterExams();
            searchInput.focus();
        });

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            classFilter.value = '';
            statusFilter.value = '';
            filterExams();
        });

        filterExams();

        // === Bulk Select & Delete ===
        const bulkBar = document.getElementById('bulkBar');
        const bulkCount = document.getElementById('bulkCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkCancelBtn = document.getElementById('bulkCancelBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const selectAllDesktop = document.getElementById('selectAllDesktop');
        const allCheckboxes = document.querySelectorAll('.bulk-checkbox');

        function updateBulkBar() {
            const checked = document.querySelectorAll('.bulk-checkbox:checked');
            const count = checked.length;
            bulkCount.textContent = count;
            if (count > 0) {
                bulkBar.classList.remove('hidden');
                bulkBar.classList.add('flex');
            } else {
                bulkBar.classList.add('hidden');
                bulkBar.classList.remove('flex');
            }
        }

        allCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

        if (selectAllDesktop) {
            selectAllDesktop.addEventListener('change', function() {
                // Only toggle visible rows
                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const cb = row.querySelector('.bulk-checkbox');
                        if (cb) cb.checked = selectAllDesktop.checked;
                    }
                });
                updateBulkBar();
            });
        }

        bulkCancelBtn.addEventListener('click', function() {
            allCheckboxes.forEach(cb => cb.checked = false);
            if (selectAllDesktop) selectAllDesktop.checked = false;
            updateBulkBar();
        });

        bulkDeleteBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.bulk-checkbox:checked');
            if (checked.length === 0) return;

            if (!confirm(`Yakin ingin menghapus ${checked.length} jadwal ujian yang dipilih?`)) return;

            // Clear old hidden inputs
            bulkDeleteForm.querySelectorAll('input[name="exam_ids[]"]').forEach(el => el.remove());

            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'exam_ids[]';
                input.value = cb.value;
                bulkDeleteForm.appendChild(input);
            });

            bulkDeleteForm.submit();
        });
    });

</script>
@endsection
