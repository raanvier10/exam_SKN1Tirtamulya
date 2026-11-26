@extends('layouts.admin')
@section('title', 'Edit Guru')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-[22px] flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Akun Guru</h2>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi profil atau ganti password akun guru.</p>
        </div>
        <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center gap-[6px] text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors whitespace-nowrap">
            &larr; Kembali ke Daftar Guru
        </a>
    </div>

    <form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        
        <div class="card-body space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-[22px]">
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Contoh: Budi Santoso, S.Pd.">
                    @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Username / NIP</label>
                    <input type="text" name="username" value="{{ old('username', $teacher->username) }}" required class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all tabular-nums" placeholder="Contoh: 19850720... atau guru_ipa">
                    @error('username') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-[22px]">
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Email <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                    <input type="email" name="email" value="{{ old('email', $teacher->email) }}" class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="guru@smkn1tirtamulya.online">
                    @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Status Akun</label>
                    <select name="status" required class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
                        <option value="active" {{ old('status', $teacher->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $teacher->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="p-4 bg-slate-50 border border-slate-200/70 rounded-[8px] space-y-4">
                <div>
                    <h4 class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Ubah Password</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Biarkan kosong jika tidak ingin mengganti password guru ini.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[22px]">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                        <input type="password" name="password" class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Minimal 6 karakter">
                        @error('password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer flex items-center justify-end gap-3">
            <a href="{{ route('admin.teachers.index') }}" class="px-5 py-2.5 rounded-[8px] border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-[8px] bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                Perbarui Akun Guru
            </button>
        </div>
    </form>
</div>
@endsection
