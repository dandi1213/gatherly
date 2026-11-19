@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <div class="bg-white shadow rounded-lg p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Dashboard Admin</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <p class="text-xs font-medium text-gray-500">Total User</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalUsers }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Total Organizer</p>
                    <p class="text-3xl font-bold text-green-600">{{ $totalOrganizers }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Total Event</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $totalEvents }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Dipublikasi</p>
                    <p class="text-3xl font-bold text-gray-600">{{ $totalPublished }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500">Draft</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $totalDraft }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Dibatalkan</p>
                    <p class="text-3xl font-bold text-red-600">{{ $totalCancelled }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Total Registrasi</p>
                    <p class="text-3xl font-bold text-gray-700">{{ $totalRegistrations }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Aktivitas Terbaru</p>
                </div>
            </div>
            
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Event Terbaru</h3>
                <div class="space-y-3">
                    @foreach ($recentEvents as $event)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-white shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-medium">
                                {{ Str::substr($event->title, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 truncate">{{ $event->title }}</p>
                                <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::date($event->created_at, 'd M Y') }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium {{ match($event->status) 'draft' => 'text-indigo-600' 'published' => 'text-green-600' 'cancelled' => 'text-red-600' }}">{{ ucfirst($event->status) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">User Terbaru</h3>
                <div class="space-y-3">
                    @foreach ($recentUsers as $user)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-white shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ $user->role === 'admin' ? 'bg-indigo-100' : ($user->role === 'organizer' ? 'bg-green-100' : 'bg-gray-100') }} text-{{ $user->role === 'admin' ? 'indigo' : ($user->role === 'organizer' ? 'green' : 'gray') }} font-medium text-sm">
                                {{ Str::substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium {{ $user->role === 'admin' ? 'text-indigo-600' : ($user->role === 'organizer' ? 'text-green-600' : 'text-gray-500') }}">{{ ucfirst($user->role) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection