@extends('layouts.admin')
@section('title', 'Edit Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Jadwal Ujian</h2>
            <p class="text-sm text-slate-500 mt-1">Ubah formulir di bawah untuk memperbarui jadwal ujian.</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.exams.update', $exam->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Judul Ujian</label>
            <input type="text" name="title" value="{{ old('title', $exam->title) }}" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" rows="2" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">{{ old('description', $exam->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Google Form URL</label>
            <input type="url" name="google_form_url" value="{{ old('google_form_url', $exam->google_form_url) }}" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all font-mono">
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Waktu Mulai</label>
                <input type="datetime-local" name="start_at" value="{{ \Carbon\Carbon::parse($exam->start_at)->format('Y-m-d\TH:i') }}" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Durasi (Menit)</label>
                <input type="number" name="duration" value="{{ old('duration', $exam->duration) }}" required min="1" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            </div>
        </div>
        
        <p class="text-xs text-slate-400 -mt-3 italic">* Waktu selesai otomatis dihitung ulang berdasarkan waktu mulai dan durasi.</p>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Batas Toleransi Keluar (Max Violation)</label>
            <input type="number" name="max_violation" value="{{ old('max_violation', $exam->max_violation) }}" required min="1" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            <p class="text-xs text-slate-400 mt-1.5">Jumlah maksimal siswa boleh meminimize/keluar dari aplikasi sebelum diblokir.</p>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-800">Target Kelas / Peserta</label>
                    <p class="text-xs text-slate-500">Pilih kelas yang wajib mengikuti ujian ini. Jika tidak ada yang dipilih, ujian berlaku untuk <strong>Semua Kelas</strong>.</p>
                </div>
                <button type="button" id="toggle-all-classes" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100/80 px-2.5 py-1 rounded-lg transition-colors">
                    Pilih Semua
                </button>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl max-h-48 overflow-y-auto">
                @forelse($classes as $c)
                <label class="flex items-center gap-2.5 p-2 bg-white border border-slate-200/70 rounded-lg hover:border-indigo-300 hover:bg-indigo-50/20 cursor-pointer transition-all">
                    <input type="checkbox" name="classes[]" value="{{ $c->id }}" {{ in_array($c->id, old('classes', $selectedClasses ?? [])) ? 'checked' : '' }} class="class-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300">
                    <span class="text-xs font-medium text-slate-700 select-none">{{ $c->name }}</span>
                </label>
                @empty
                <div class="col-span-full text-xs text-slate-400 py-2 text-center">Belum ada data kelas yang terdaftar.</div>
                @endforelse
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggleBtn = document.getElementById('toggle-all-classes');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function() {
                        const checkboxes = document.querySelectorAll('.class-checkbox');
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        checkboxes.forEach(cb => cb.checked = !allChecked);
                        toggleBtn.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih Semua';
                    });
                }
            });
        </script>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Filter Keikutsertaan Siswa PKL</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="flex items-center gap-3 p-3.5 bg-white border border-slate-200/80 rounded-xl hover:border-indigo-300 hover:bg-indigo-50/10 cursor-pointer transition-all">
                    <input type="radio" name="pkl_filter" value="all" {{ old('pkl_filter', $exam->pkl_filter ?? 'all') === 'all' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500/20 border-slate-300">
                    <div>
                        <div class="text-xs font-semibold text-slate-900">Semua Siswa</div>
                        <div class="text-[11px] text-slate-400">Reguler & PKL ikut</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3.5 bg-white border border-slate-200/80 rounded-xl hover:border-indigo-300 hover:bg-indigo-50/10 cursor-pointer transition-all">
                    <input type="radio" name="pkl_filter" value="regular_only" {{ old('pkl_filter', $exam->pkl_filter ?? 'all') === 'regular_only' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500/20 border-slate-300">
                    <div>
                        <div class="text-xs font-semibold text-slate-900">Reguler Saja</div>
                        <div class="text-[11px] text-slate-400">Kecualikan siswa PKL</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3.5 bg-white border border-slate-200/80 rounded-xl hover:border-indigo-300 hover:bg-indigo-50/10 cursor-pointer transition-all">
                    <input type="radio" name="pkl_filter" value="pkl_only" {{ old('pkl_filter', $exam->pkl_filter ?? 'all') === 'pkl_only' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500/20 border-slate-300">
                    <div>
                        <div class="text-xs font-semibold text-slate-900">Khusus Siswa PKL</div>
                        <div class="text-[11px] text-slate-400">Hanya siswa status PKL</div>
                    </div>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Status Ujian</label>
            <div class="relative custom-select">
                <input type="hidden" name="status" value="{{ old('status', $exam->status) }}">
                <button type="button" class="select-trigger w-full flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all text-left cursor-pointer hover:border-slate-300">
                    <span class="selected-text font-medium text-slate-900">{{ $exam->status == 'active' ? 'Aktif (Tersedia untuk Siswa)' : 'Nonaktif (Draft)' }}</span>
                    <svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="select-options hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/50 p-1.5 z-50 max-h-56 overflow-y-auto space-y-0.5">
                    <div data-value="inactive" class="option-item {{ $exam->status == 'inactive' ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-700' }} px-3.5 py-2.5 rounded-xl text-sm hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer transition-colors flex items-center justify-between">
                        <span>Nonaktif (Draft)</span>
                    </div>
                    <div data-value="active" class="option-item {{ $exam->status == 'active' ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-700' }} px-3.5 py-2.5 rounded-xl text-sm hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer transition-colors flex items-center justify-between">
                        <span>Aktif (Tersedia untuk Siswa)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98]">
                Perbarui Ujian
            </button>
        </div>
    </form>
</div>
@endsection
