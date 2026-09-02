@extends('layouts.admin')
@section('title', 'Tambah Siswa')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Siswa Baru</h2>
            <p class="text-sm text-slate-500 mt-1">Buat akun siswa baru untuk mengikuti ujian.</p>
        </div>
        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.students.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 space-y-6">
        @csrf
        
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Contoh: Budi Santoso">
                @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">NIS / Username</label>
                <input type="text" name="username" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all font-mono" placeholder="Contoh: 12345678 (Gunakan NIS)">
                @error('username') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
                @error('password') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <!-- Pilih Kelas -->
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Pilih Kelas</label>
                <div class="relative custom-select">
                    <input type="hidden" name="student_class_id" value="{{ old('student_class_id') }}" required>
                    <button type="button" class="select-trigger w-full flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all text-left cursor-pointer hover:border-slate-300">
                        <span class="selected-text text-slate-400">-- Pilih Kelas --</span>
                        <svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="select-options hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/50 p-1.5 z-50 max-h-56 overflow-y-auto space-y-0.5">
                        @foreach(\App\Models\StudentClass::all() as $c)
                            <div data-value="{{ $c->id }}" class="option-item px-3.5 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 hover:font-medium cursor-pointer transition-colors flex items-center justify-between">
                                <span>{{ $c->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('student_class_id') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Status</label>
                <div class="relative custom-select">
                    <input type="hidden" name="status" value="{{ old('status', 'active') }}">
                    <button type="button" class="select-trigger w-full flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all text-left cursor-pointer hover:border-slate-300">
                        <span class="selected-text font-medium text-slate-900">Aktif</span>
                        <svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="select-options hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/50 p-1.5 z-50 max-h-56 overflow-y-auto space-y-0.5">
                        <div data-value="active" class="option-item bg-indigo-50 text-indigo-600 font-semibold px-3.5 py-2.5 rounded-xl text-sm hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer transition-colors flex items-center justify-between">
                            <span>Aktif</span>
                        </div>
                        <div data-value="inactive" class="option-item px-3.5 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 hover:font-medium cursor-pointer transition-colors flex items-center justify-between">
                            <span>Nonaktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status PKL / Lokasi GPS -->
        <div class="p-5 bg-slate-50 border border-slate-200/80 rounded-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-sm font-semibold text-slate-900">Siswa Sedang PKL / Magang Luar Sekolah</span>
                    <p class="text-xs text-slate-500 mt-0.5">Jika diaktifkan, pengecekan radius lokasi & GPS di HP siswa akan otomatis dilewati saat ujian.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_pkl" value="1" {{ old('is_pkl') ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98]">
                Simpan Siswa
            </button>
        </div>
    </form>
</div>
@endsection
