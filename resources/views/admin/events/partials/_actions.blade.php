<div class="inline-flex items-center gap-2">
    <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex items-center rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-semibold text-ink transition-colors hover:bg-canvas">
        Edit
    </a>
    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus event ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center rounded-lg border border-danger-100 bg-danger-50 px-2.5 py-1 text-xs font-semibold text-danger-700 transition-colors hover:bg-danger-100">
            Hapus
        </button>
    </form>
</div>
