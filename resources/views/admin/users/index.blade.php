@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <div class="bg-white shadow rounded-lg p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Daftar User</h2>
            
            <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Peran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : ($user->role === 'organizer' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->is_active)
                                    <span class="text-green-600 font-medium">Aktif</span>
                                @else
                                    <span class="text-red-600 font-medium">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button 
                                    class="rounded-md bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200 transition-colors"
                                    onclick="toggleUser({{$user->id}}, {{ $user->is_active ? 'true' : 'false' }})"
                                >
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $users->links() }}
        </div>
    </div>
</div>

<script>
function toggleUser(userId, currentStatus) {
    if (confirm('Yakin ingin mengubah status user ini?')) {
        axios.post('/admin/users/' + userId + '/toggle')
            .then(() => {
                location.reload();
            })
            .catch(() => {
                alert('Gagal mengubah status user.');
            });
    }
}
</script>
@endsection