@php
    $inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2 text-sm text-ink placeholder:text-muted/70 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-ink';
    $errorClass = 'mt-1.5 text-sm text-danger-700';
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="{{ $labelClass }}">Nama kategori</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required class="{{ $inputClass }}" placeholder="Contoh: Workshop">
        @error('name')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="{{ $labelClass }}">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $category?->slug) }}" required class="{{ $inputClass }}" placeholder="workshop">
        <p class="mt-1.5 text-xs text-muted">Dipakai pada URL dan filter. Gunakan huruf kecil dan tanda hubung.</p>
        @error('slug')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
</div>
