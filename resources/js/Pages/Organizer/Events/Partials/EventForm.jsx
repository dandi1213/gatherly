import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { Link, useForm } from '@inertiajs/react';

export default function EventForm({ event = null, categories, submitRoute, method = 'post' }) {
    const { data, setData, post, processing, errors, progress } = useForm({
        title: event?.title ?? '',
        description: event?.description ?? '',
        category_id: event?.category_id ?? '',
        format: event?.format ?? 'offline',
        location: event?.location ?? '',
        online_url: event?.online_url ?? '',
        start_at: event?.start_at ? event.start_at.substring(0, 16) : '',
        end_at: event?.end_at ? event.end_at.substring(0, 16) : '',
        registration_start_at: event?.registration_start_at
            ? event.registration_start_at.substring(0, 16)
            : '',
        registration_end_at: event?.registration_end_at
            ? event.registration_end_at.substring(0, 16)
            : '',
        poster: null,
        _method: method === 'patch' ? 'patch' : 'post',
    });

    const submit = (e) => {
        e.preventDefault();

        // PHP hanya mem-parsing multipart/form-data untuk request POST.
        // Gunakan POST + field _method untuk spoofing PATCH agar file dan
        // seluruh field teks tetap terbaca saat ada upload poster.
        post(submitRoute, { forceFormData: true });
    };

    const field = (label, name, type = 'text', extra = {}) => (
        <div>
            <InputLabel htmlFor={name} value={label} />
            <TextInput
                id={name}
                type={type}
                name={name}
                value={data[name]}
                className="mt-1 block w-full"
                onChange={(e) => setData(name, e.target.value)}
                {...extra}
            />
            <InputError message={errors[name]} className="mt-2" />
        </div>
    );

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div className="md:col-span-2">
                    <InputLabel htmlFor="title" value="Judul Event" />
                    <TextInput
                        id="title"
                        name="title"
                        value={data.title}
                        className="mt-1 block w-full"
                        onChange={(e) => setData('title', e.target.value)}
                        required
                    />
                    <InputError message={errors.title} className="mt-2" />
                </div>

                <div className="md:col-span-2">
                    <InputLabel htmlFor="description" value="Deskripsi" />
                    <textarea
                        id="description"
                        name="description"
                        value={data.description}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        rows={5}
                        onChange={(e) => setData('description', e.target.value)}
                        required
                    />
                    <InputError message={errors.description} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="category_id" value="Kategori" />
                    <select
                        id="category_id"
                        value={data.category_id}
                        onChange={(e) => setData('category_id', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="">— Pilih kategori —</option>
                        {categories.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.category_id} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="format" value="Format" />
                    <select
                        id="format"
                        value={data.format}
                        onChange={(e) => setData('format', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="offline">Offline</option>
                        <option value="online">Online</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                    <InputError message={errors.format} className="mt-2" />
                </div>

                {(data.format === 'offline' || data.format === 'hybrid') &&
                    field('Lokasi', 'location')}

                {(data.format === 'online' || data.format === 'hybrid') &&
                    field('URL Online', 'online_url', 'url')}

                {field('Mulai Event', 'start_at', 'datetime-local')}
                {field('Selesai Event', 'end_at', 'datetime-local')}
                {field('Mulai Registrasi', 'registration_start_at', 'datetime-local')}
                {field('Akhir Registrasi', 'registration_end_at', 'datetime-local')}

                <div className="md:col-span-2">
                    <InputLabel htmlFor="poster" value="Poster (JPG/PNG/WebP, maks. 2 MB)" />
                    <input
                        id="poster"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        onChange={(e) => setData('poster', e.target.files[0])}
                        className="mt-1 block w-full text-sm text-gray-700"
                    />
                    <InputError message={errors.poster} className="mt-2" />
                    {progress && (
                        <progress value={progress.percentage} max="100" className="mt-2 w-full">
                            {progress.percentage}%
                        </progress>
                    )}
                </div>
            </div>

            <div className="flex items-center gap-3">
                <PrimaryButton disabled={processing}>
                    {event ? 'Simpan Perubahan' : 'Simpan Draft'}
                </PrimaryButton>
                <Link href={route('organizer.events.index')}>
                    <SecondaryButton type="button">Batal</SecondaryButton>
                </Link>
            </div>
        </form>
    );
}
