@extends('layouts.admin')
@section('title', 'Edit Kelas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Kelas</h2>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi kelas.</p>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('admin.classes.update', $class->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Kelas</label>
            <input type="text" name="name" value="{{ old('name', $class->name) }}" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">
            @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-800 mb-2">Deskripsi (Opsional)</label>
            <textarea name="description" rows="3" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 shadow-2xs transition-all">{{ old('description', $class->description) }}</textarea>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 transition-all duration-150 active:scale-[0.98]">
                Perbarui Kelas
            </button>
        </div>
    </form>
</div>
@endsection
