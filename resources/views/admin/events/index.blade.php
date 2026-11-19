@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <div class="bg-white shadow rounded-lg p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Daftar Event</h2>
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Event Saya</h3>
                <a href="{{ route('admin.events.create') }}" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500">
                    + Buat Event
                </a>
            </div>
            
            @if (count($events) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Organizer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $event->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $event->category?->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $event->organizer?->name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ match($event->status) 'draft' => 'bg-gray-100 text-gray-800' 'published' => 'bg-green-100 text-green-800' 'cancelled' => 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.events.edit', $event) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Yakin ingin menghapus event ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-8">Belum ada event. Klik "Buat Event" untuk mulai.</p>
            @endif
            
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection