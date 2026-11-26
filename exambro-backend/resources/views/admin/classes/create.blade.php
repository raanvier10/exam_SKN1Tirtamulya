@extends('layouts.admin')
@section('title', 'Tambah Kelas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-[22px] flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Kelas Baru</h2>
            <p class="text-sm text-slate-500 mt-1">Buat kelas baru untuk pengelompokan siswa.</p>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="inline-flex items-center gap-[6px] text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors whitespace-nowrap">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.classes.store') }}" method="POST" class="card">
        @csrf
        <div class="card-body space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Kelas</label>
                <input type="text" name="name" required class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Contoh: XII IPA 1">
                @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all" placeholder="Contoh: Kelas untuk jurusan IPA angkatan 2023"></textarea>
            </div>
        </div>

        <div class="card-footer flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[8px] text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                Simpan Kelas
            </button>
        </div>
    </form>
</div>
@endsection
