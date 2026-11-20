<form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin mengubah status user ini?')">
    @csrf
    @method('PUT')
    <button
        type="submit"
        class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold transition-colors {{ $user->is_active ? 'border-danger-100 bg-danger-50 text-danger-700 hover:bg-danger-100' : 'border-line bg-white text-ink hover:bg-canvas' }}"
    >
        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
    </button>
</form>
