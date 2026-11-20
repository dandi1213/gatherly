@php
    $inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2 text-sm text-ink placeholder:text-muted/70 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-ink';
    $errorClass = 'mt-1.5 text-sm text-danger-700';
    $formats = [
        'offline' => 'Offline',
        'online' => 'Online',
        'hybrid' => 'Hybrid',
    ];
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="title" class="{{ $labelClass }}">Judul event</label>
        <input id="title" name="title" type="text" value="{{ old('title', $event?->title) }}" required class="{{ $inputClass }}" placeholder="Nama event yang jelas dan singkat">
        @error('title')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="{{ $labelClass }}">Deskripsi</label>
        <textarea id="description" name="description" rows="5" required class="{{ $inputClass }}" placeholder="Jelaskan agenda, peserta yang dituju, dan informasi penting lainnya.">{{ old('description', $event?->description) }}</textarea>
        @error('description')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="category_id" class="{{ $labelClass }}">Kategori</label>
        <select id="category_id" name="category_id" required class="{{ $inputClass }}">
            <option value="" disabled @selected(old('category_id', $event?->category_id) === null)>Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $event?->category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="format" class="{{ $labelClass }}">Format</label>
        <select id="format" name="format" required class="{{ $inputClass }}">
            @foreach ($formats as $value => $label)
                <option value="{{ $value }}" @selected(old('format', $event?->format ?? 'offline') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('format')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="location" class="{{ $labelClass }}">Lokasi</label>
        <input id="location" name="location" type="text" value="{{ old('location', $event?->location) }}" class="{{ $inputClass }}" placeholder="Nama venue dan alamat">
        @error('location')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="online_url" class="{{ $labelClass }}">URL online</label>
        <input id="online_url" name="online_url" type="url" value="{{ old('online_url', $event?->online_url) }}" class="{{ $inputClass }}" placeholder="https://">
        @error('online_url')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="start_at" class="{{ $labelClass }}">Mulai event</label>
        <input id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at', $event?->start_at?->format('Y-m-d\TH:i')) }}" required class="{{ $inputClass }}">
        @error('start_at')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="end_at" class="{{ $labelClass }}">Selesai event</label>
        <input id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at', $event?->end_at?->format('Y-m-d\TH:i')) }}" required class="{{ $inputClass }}">
        @error('end_at')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="registration_start_at" class="{{ $labelClass }}">Registrasi dibuka</label>
        <input id="registration_start_at" name="registration_start_at" type="datetime-local" value="{{ old('registration_start_at', $event?->registration_start_at?->format('Y-m-d\TH:i')) }}" required class="{{ $inputClass }}">
        @error('registration_start_at')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="registration_end_at" class="{{ $labelClass }}">Registrasi ditutup</label>
        <input id="registration_end_at" name="registration_end_at" type="datetime-local" value="{{ old('registration_end_at', $event?->registration_end_at?->format('Y-m-d\TH:i')) }}" required class="{{ $inputClass }}">
        @error('registration_end_at')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
</div>
