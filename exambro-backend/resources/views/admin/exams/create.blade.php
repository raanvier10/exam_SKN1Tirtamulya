@extends('layouts.admin')
@section('title', 'Tambah Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Buat Jadwal Ujian Baru</h2>
            <p class="text-sm text-slate-500 mt-1">Isi formulir di bawah untuk menjadwalkan ujian.</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.exams.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Judul Ujian</label>
            <input type="text" name="title" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Contoh: Ujian Tengah Semester Matematika">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" rows="2" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Contoh: Ujian ini bersifat close-book..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Google Form URL</label>
            <input type="url" name="google_form_url" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all font-mono" placeholder="https://docs.google.com/forms/...">
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Waktu Mulai</label>
                <input type="datetime-local" name="start_at" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Durasi (Menit)</label>
                <input type="number" name="duration" required min="1" value="90" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            </div>
        </div>
        
        <p class="text-xs text-slate-400 -mt-3 italic">* Waktu selesai otomatis dihitung berdasarkan waktu mulai dan durasi.</p>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Batas Toleransi Keluar (Max Violation)</label>
            <input type="number" name="max_violation" required min="1" value="3" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
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
                    <input type="checkbox" name="classes[]" value="{{ $c->id }}" class="class-checkbox rounded text-indigo-600 focus:ring-indigo-500/20 border-slate-300">
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
            <label class="block text-sm font-semibold text-slate-800 mb-2">Status Ujian</label>
            <div class="relative custom-select">
                <input type="hidden" name="status" value="{{ old('status', 'inactive') }}">
                <button type="button" class="select-trigger w-full flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all text-left cursor-pointer hover:border-slate-300">
                    <span class="selected-text font-medium text-slate-900">Nonaktif (Draft)</span>
                    <svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="select-options hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/50 p-1.5 z-50 max-h-56 overflow-y-auto space-y-0.5">
                    <div data-value="inactive" class="option-item bg-indigo-50 text-indigo-600 font-semibold px-3.5 py-2.5 rounded-xl text-sm hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer transition-colors flex items-center justify-between">
                        <span>Nonaktif (Draft)</span>
                    </div>
                    <div data-value="active" class="option-item px-3.5 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 hover:font-medium cursor-pointer transition-colors flex items-center justify-between">
                        <span>Aktif (Tersedia untuk Siswa)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98]">
                Buat Ujian
            </button>
        </div>
    </form>
</div>
@endsection
