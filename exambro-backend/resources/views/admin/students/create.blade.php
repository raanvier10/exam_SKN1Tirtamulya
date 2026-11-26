@extends('layouts.admin')
@section('title', 'Tambah Siswa')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-[#142C5C] tracking-tight">Tambah Siswa Baru</h2>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">Buat akun siswa baru untuk mengikuti ujian.</p>
        </div>
        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 hover:border-[#2457E8] hover:text-[#2457E8] transition-colors self-start sm:self-auto">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.students.store') }}" method="POST" autocomplete="off" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        @csrf

        <div class="bg-[#0D2149] px-6 py-4 text-white">
            <h3 class="text-sm font-bold tracking-tight">Informasi Akun Siswa</h3>
            <p class="text-xs text-blue-200 mt-0.5">Lengkapi identitas, kredensial login, kelas, dan mode GPS siswa.</p>
        </div>

        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 transition-all" placeholder="Contoh: Budi Santoso">
                    @error('name') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">NIS / Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autocomplete="off" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 transition-all tabular-nums" placeholder="Contoh: 12345678 (Gunakan NIS)">
                    @error('username') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required autocomplete="new-password" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 transition-all" placeholder="Minimal 6 karakter">
                    @error('password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 transition-all" placeholder="Ulangi password">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Pilih Kelas -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Kelas</label>
                    <div class="relative custom-select">
                        <input type="hidden" name="student_class_id" value="{{ old('student_class_id') }}" required>
                        <button type="button" class="select-trigger w-full flex items-center justify-between bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 transition-all text-left cursor-pointer hover:border-slate-300">
                            <span class="selected-text text-slate-400">-- Pilih Kelas --</span>
                            <svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="select-options hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl p-1.5 z-50 max-h-56 overflow-y-auto space-y-0.5">
                            @foreach(\App\Models\StudentClass::all() as $c)
                                <div data-value="{{ $c->id }}" class="option-item px-3.5 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-[#F5F8FF] hover:text-[#2457E8] hover:font-medium cursor-pointer transition-colors flex items-center justify-between">
                                    <span>{{ $c->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('student_class_id') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                    <div class="relative custom-select">
                        <input type="hidden" name="status" value="{{ old('status', 'active') }}">
                        <button type="button" class="select-trigger w-full flex items-center justify-between bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#2457E8] focus:ring-2 focus:ring-[#2457E8]/15 transition-all text-left cursor-pointer hover:border-slate-300">
                            <span class="selected-text font-medium text-slate-900">Aktif</span>
                            <svg class="w-4 h-4 text-slate-400 chevron transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="select-options hidden absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl p-1.5 z-50 max-h-56 overflow-y-auto space-y-0.5">
                            <div data-value="active" class="option-item bg-[#F5F8FF] text-[#2457E8] font-semibold px-3.5 py-2.5 rounded-xl text-sm hover:bg-[#F5F8FF] hover:text-[#2457E8] cursor-pointer transition-colors flex items-center justify-between">
                                <span>Aktif</span>
                            </div>
                            <div data-value="inactive" class="option-item px-3.5 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-[#F5F8FF] hover:text-[#2457E8] hover:font-medium cursor-pointer transition-colors flex items-center justify-between">
                                <span>Nonaktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status PKL / Lokasi GPS -->
            <div class="p-4 bg-[#F5F8FF] border border-blue-100 rounded-xl">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <span class="block text-sm font-bold text-[#142C5C]">Siswa Sedang PKL / Magang Luar Sekolah</span>
                        <p class="text-xs text-slate-600 mt-0.5">Jika diaktifkan, pengecekan radius lokasi & GPS di HP siswa akan otomatis dilewati saat ujian.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="is_pkl" value="1" {{ old('is_pkl') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2457E8]"></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-end gap-2.5">
            <a href="{{ route('admin.students.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs sm:text-sm font-semibold transition">Batal</a>
            <button type="submit" class="px-5 py-2.5 bg-[#2457E8] hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition active:scale-[0.98]">
                Simpan Siswa
            </button>
        </div>
    </form>
</div>
@endsection
