@extends('layouts.admin')
@section('title', 'Edit Kelas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-[22px] flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Kelas</h2>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi kelas.</p>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="inline-flex items-center gap-[6px] text-sm font-medium text-slate-500 hover:text-[#142C5C] transition-colors whitespace-nowrap">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.classes.update', $class->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-body space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Kelas</label>
                <input type="text" name="name" value="{{ old('name', $class->name) }}" required class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#142C5C] focus:ring-3 focus:ring-[#142C5C]/10 shadow-2xs transition-all">
                @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-2">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" class="w-full bg-white border border-slate-200 rounded-[8px] px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-[#142C5C] focus:ring-3 focus:ring-[#142C5C]/10 shadow-2xs transition-all">{{ old('description', $class->description) }}</textarea>
            </div>
        </div>

        <div class="card-footer flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-[#142C5C] hover:bg-[#142C5C] text-white rounded-[8px] text-sm font-medium shadow-sm shadow-slate-900/10 hover:shadow-md hover:shadow-slate-900/15 transition-all duration-150 active:scale-[0.98] cursor-pointer">
                Perbarui Kelas
            </button>
        </div>
    </form>
</div>
@endsection
