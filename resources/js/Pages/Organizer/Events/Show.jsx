import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

function TicketTypeForm({ eventId, onDone, initial = null }) {
    const { data, setData, post, patch, processing, errors, reset } = useForm({
        name: initial?.name ?? '',
        description: initial?.description ?? '',
        quota: initial?.quota ?? 50,
        available_from: initial?.available_from?.substring(0, 16) ?? '',
        available_until: initial?.available_until?.substring(0, 16) ?? '',
        is_active: initial?.is_active ?? true,
    });

    const submit = (e) => {
        e.preventDefault();
        if (initial) {
            patch(route('organizer.ticket-types.update', [eventId, initial.id]), {
                onSuccess: () => {
                    reset();
                    onDone?.();
                },
            });
        } else {
            post(route('organizer.ticket-types.store', eventId), {
                onSuccess: () => {
                    reset();
                    onDone?.();
                },
            });
        }
    };

    return (
        <form onSubmit={submit} className="grid grid-cols-1 gap-3 rounded-md bg-gray-50 p-4 md:grid-cols-2">
            <div>
                <InputLabel value="Nama Tipe" />
                <TextInput
                    value={data.name}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('name', e.target.value)}
                    required
                />
                <InputError message={errors.name} className="mt-1" />
            </div>
            <div>
                <InputLabel value="Kuota" />
                <TextInput
                    type="number"
                    min="1"
                    value={data.quota}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('quota', e.target.value)}
                    required
                />
                <InputError message={errors.quota} className="mt-1" />
            </div>
            <div className="md:col-span-2">
                <InputLabel value="Deskripsi (opsional)" />
                <TextInput
                    value={data.description}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('description', e.target.value)}
                />
            </div>
            <div>
                <InputLabel value="Tersedia Dari (opsional)" />
                <TextInput
                    type="datetime-local"
                    value={data.available_from}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('available_from', e.target.value)}
                />
                <InputError message={errors.available_from} className="mt-1" />
            </div>
            <div>
                <InputLabel value="Tersedia Sampai (opsional)" />
                <TextInput
                    type="datetime-local"
                    value={data.available_until}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('available_until', e.target.value)}
                />
                <InputError message={errors.available_until} className="mt-1" />
            </div>
            <div className="flex items-center gap-2">
                <input
                    type="checkbox"
                    checked={data.is_active}
                    onChange={(e) => setData('is_active', e.target.checked)}
                    className="h-4 w-4 rounded border-gray-300 text-indigo-600"
                />
                <span className="text-sm text-gray-700">Aktif</span>
            </div>
            <div className="flex items-center justify-end gap-2">
                {onDone && (
                    <SecondaryButton type="button" onClick={onDone}>
                        Batal
                    </SecondaryButton>
                )}
                <PrimaryButton disabled={processing}>
                    {initial ? 'Simpan' : 'Tambah'}
                </PrimaryButton>
            </div>
        </form>
    );
}

export default function Show({ event, publishErrors }) {
    const { flash } = usePage().props;
    const [adding, setAdding] = useState(false);
    const [editingId, setEditingId] = useState(null);

    const doAction = (routeName, confirmMessage) => {
        if (confirmMessage && !confirm(confirmMessage)) return;
        router.post(route(routeName, event.id));
    };

    const removeTicketType = (id) => {
        if (!confirm('Hapus tipe tiket ini?')) return;
        router.delete(route('organizer.ticket-types.destroy', [event.id, id]));
    };

    const statusBadge = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    }[event.status];

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <div className="mb-2">
                        <Link
                            href={route('organizer.events.index')}
                            className="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                        >
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Event Saya
                        </Link>
                    </div>
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            {event.title}
                        </h2>
                        <span className={`rounded-full px-3 py-1 text-xs font-semibold ${statusBadge}`}>
                            {event.status}
                        </span>
                    </div>
                </div>
            }
        >
            <Head title={event.title} />

            <div className="py-8">
                <div className="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">{flash.success}</div>
                    )}
                    {flash?.error && (
                        <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">{flash.error}</div>
                    )}

                    {/* Aksi */}
                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={route('organizer.events.edit', event.id)}
                            className="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow hover:bg-gray-50"
                        >
                            Edit Event
                        </Link>

                        <Link
                            href={route('organizer.events.staff.index', event.id)}
                            className="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow hover:bg-gray-50"
                        >
                            Kelola Staff
                        </Link>

                        {event.status === 'published' && (
                            <Link
                                href={route('checkin.show', event.id)}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                            >
                                Buka Check-in
                            </Link>
                        )}

                        {event.status === 'draft' && (
                            <button
                                onClick={() => doAction('organizer.events.publish')}
                                className="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500"
                            >
                                Publish
                            </button>
                        )}

                        {event.status === 'published' && (
                            <button
                                onClick={() =>
                                    doAction(
                                        'organizer.events.cancel',
                                        'Batalkan event ini? Semua tiket aktif akan dibatalkan.',
                                    )
                                }
                                className="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500"
                            >
                                Batalkan Event
                            </button>
                        )}

                        {event.status === 'draft' && (
                            <button
                                onClick={() =>
                                    doAction(
                                        'organizer.events.destroy',
                                        'Hapus event draft ini?',
                                    )
                                }
                                className="rounded-md bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow hover:bg-red-50"
                            >
                                Hapus
                            </button>
                        )}
                    </div>

                    {/* Publish errors */}
                    {event.status === 'draft' && publishErrors?.length > 0 && (
                        <div className="rounded-md border border-amber-200 bg-amber-50 p-4">
                            <p className="mb-2 text-sm font-semibold text-amber-800">
                                Event belum bisa dipublikasikan:
                            </p>
                            <ul className="list-disc space-y-1 ps-5 text-sm text-amber-700">
                                {publishErrors.map((err, i) => (
                                    <li key={i}>{err}</li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {/* Detail */}
                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">Detail Event</h3>
                        <dl className="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                            <div>
                                <dt className="text-gray-500">Kategori</dt>
                                <dd className="font-medium text-gray-900">{event.category?.name}</dd>
                            </div>
                            <div>
                                <dt className="text-gray-500">Format</dt>
                                <dd className="font-medium text-gray-900">{event.format}</dd>
                            </div>
                            <div>
                                <dt className="text-gray-500">Mulai</dt>
                                <dd className="font-medium text-gray-900">{new Date(event.start_at).toLocaleString('id-ID')}</dd>
                            </div>
                            <div>
                                <dt className="text-gray-500">Selesai</dt>
                                <dd className="font-medium text-gray-900">{new Date(event.end_at).toLocaleString('id-ID')}</dd>
                            </div>
                            <div>
                                <dt className="text-gray-500">Registrasi</dt>
                                <dd className="font-medium text-gray-900">
                                    {new Date(event.registration_start_at).toLocaleString('id-ID')} —{' '}
                                    {new Date(event.registration_end_at).toLocaleString('id-ID')}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-gray-500">Lokasi</dt>
                                <dd className="font-medium text-gray-900">{event.location || event.online_url || '—'}</dd>
                            </div>
                        </dl>
                    </div>

                    {/* Ticket Types */}
                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-800">Tipe Tiket</h3>
                            {!adding && (
                                <SecondaryButton onClick={() => setAdding(true)}>
                                    + Tambah Tipe
                                </SecondaryButton>
                            )}
                        </div>

                        {adding && (
                            <div className="mb-4">
                                <TicketTypeForm eventId={event.id} onDone={() => setAdding(false)} />
                            </div>
                        )}

                        <div className="space-y-3">
                            {event.ticket_types?.length === 0 && (
                                <p className="text-sm text-gray-500">Belum ada tipe tiket.</p>
                            )}
                            {event.ticket_types?.map((tt) => (
                                <div key={tt.id} className="rounded-md border border-gray-200 p-4">
                                    {editingId === tt.id ? (
                                        <TicketTypeForm
                                            eventId={event.id}
                                            initial={tt}
                                            onDone={() => setEditingId(null)}
                                        />
                                    ) : (
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    {tt.name}{' '}
                                                    {!tt.is_active && (
                                                        <span className="ms-2 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                                            nonaktif
                                                        </span>
                                                    )}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    Kuota: {tt.quota} · Terbit: {tt.issued_count ?? 0} · Sisa:{' '}
                                                    {tt.remaining_quota ?? tt.quota}
                                                </p>
                                            </div>
                                            <div className="flex gap-2">
                                                <button
                                                    onClick={() => setEditingId(tt.id)}
                                                    className="text-sm text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    onClick={() => removeTicketType(tt.id)}
                                                    className="text-sm text-red-600 hover:text-red-900"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
