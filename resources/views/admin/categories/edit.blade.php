@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1.5 rounded-lg text-sm font-medium text-muted transition-colors hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke kategori
            </a>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-primary-700">Admin</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-ink sm:text-3xl">Edit kategori</h1>
            <p class="mt-1 text-sm text-muted">Perbarui nama atau slug kategori.</p>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="mt-6 rounded-xl border border-line bg-surface p-5 sm:p-6">
            @csrf
            @method('PATCH')

            @include('admin.categories.partials.form', ['category' => $category])

            <div class="mt-6 flex flex-col-reverse gap-2 border-t border-line pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-canvas focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
