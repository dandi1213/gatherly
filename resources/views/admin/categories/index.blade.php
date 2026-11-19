@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <div class="bg-white shadow rounded-lg p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Daftar Kategori</h2>
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Kategori Event</h3>
                <a href="{{ route('admin.categories.create') }}" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500">
                    + Tambah Kategori
                </a>
            </div>
            
            @if (count($categories) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Jumlah Event</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $category->events->count() }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-8">Belum ada kategori. Klik "Tambah Kategori" untuk mulai.</p>
            @endif
            
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection