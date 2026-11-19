import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

const statusBadge = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return (
        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${map[status] ?? map.draft}`}>
            {status}
        </span>
    );
};

export default function Index({ events }) {
    const { flash } = usePage().props;
    const [mobileView, setMobileView] = useState('cards');

    const formatDateTime = (dateStr) => {
        if (!dateStr) return '—';
        return new Date(dateStr).toLocaleString('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Event Saya
                    </h2>
                    <Link
                        href={route('organizer.events.create')}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                    >
                        + Buat Event
                    </Link>
                </div>
            }
        >
            <Head title="Event Saya" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                            {flash.error}
                        </div>
                    )}

                    {/* Kontrol Tampilan Khusus Mobile */}
                    <div className="mb-4 flex items-center justify-between md:hidden">
                        <span className="text-xs font-medium text-gray-500">
                            {mobileView === 'table' ? 'Geser tabel untuk melihat semua kolom' : 'Tampilan mobile'}
                        </span>
                        <div className="inline-flex rounded-lg border border-gray-200 bg-gray-100 p-0.5 text-xs">
                            <button
                                type="button"
                                onClick={() => setMobileView('cards')}
                                className={`rounded-md px-2.5 py-1 font-medium transition ${
                                    mobileView === 'cards'
                                        ? 'bg-white text-indigo-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                Kartu
                            </button>
                            <button
                                type="button"
                                onClick={() => setMobileView('table')}
                                className={`rounded-md px-2.5 py-1 font-medium transition ${
                                    mobileView === 'table'
                                        ? 'bg-white text-indigo-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                Tabel
                            </button>
                        </div>
                    </div>

                    {/* Tampilan Kartu (Mobile) */}
                    <div className={`space-y-4 md:hidden ${mobileView === 'table' ? 'hidden' : 'block'}`}>
                        {events.data.length === 0 ? (
                            <div className="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 shadow-sm">
                                Belum ada event. Klik "Buat Event" untuk mulai.
                            </div>
                        ) : (
                            events.data.map((event) => (
                                <div
                                    key={event.id}
                                    className="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0 flex-1">
                                            <Link
                                                href={route('organizer.events.show', event.id)}
                                                className="block text-base font-semibold text-gray-900 hover:text-indigo-600"
                                            >
                                                {event.title}
                                            </Link>
                                            {event.category?.name && (
                                                <span className="mt-1 inline-block rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                                    {event.category.name}
                                                </span>
                                            )}
                                        </div>
                                        <div className="shrink-0">
                                            {statusBadge(event.status)}
                                        </div>
                                    </div>

                                    <div className="mt-3.5 grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 text-xs">
                                        <div>
                                            <span className="block text-gray-400">Mulai</span>
                                            <span className="mt-0.5 block font-medium text-gray-800">
                                                {formatDateTime(event.start_at)}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="block text-gray-400">Registrasi</span>
                                            <span className="mt-0.5 block font-medium text-gray-800">
                                                {event.registrations_count} Peserta
                                            </span>
                                        </div>
                                    </div>

                                    <div className="mt-3.5 border-t border-gray-100 pt-3">
                                        <Link
                                            href={route('organizer.events.show', event.id)}
                                            className="inline-flex w-full items-center justify-center rounded-lg bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100"
                                        >
                                            Kelola Event →
                                        </Link>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    {/* Tampilan Tabel (Desktop & saat mode Tabel dipilih di Mobile) */}
                    <div
                        className={`overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ${
                            mobileView === 'cards' ? 'hidden md:block' : 'block'
                        }`}
                    >
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6 sm:py-3.5 whitespace-nowrap">
                                            Judul
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6 sm:py-3.5 whitespace-nowrap">
                                            Kategori
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6 sm:py-3.5 whitespace-nowrap">
                                            Mulai
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6 sm:py-3.5 whitespace-nowrap">
                                            Status
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6 sm:py-3.5 whitespace-nowrap">
                                            Registrasi
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6 sm:py-3.5 whitespace-nowrap">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {events.data.length === 0 && (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-10 text-center text-sm text-gray-500">
                                                Belum ada event. Klik "Buat Event" untuk mulai.
                                            </td>
                                        </tr>
                                    )}
                                    {events.data.map((event) => (
                                        <tr key={event.id} className="hover:bg-gray-50/75 transition">
                                            <td className="px-4 py-4 text-sm font-medium text-gray-900 sm:px-6 whitespace-nowrap">
                                                {event.title}
                                            </td>
                                            <td className="px-4 py-4 text-sm text-gray-600 sm:px-6 whitespace-nowrap">
                                                {event.category?.name || '—'}
                                            </td>
                                            <td className="px-4 py-4 text-sm text-gray-600 sm:px-6 whitespace-nowrap">
                                                {formatDateTime(event.start_at)}
                                            </td>
                                            <td className="px-4 py-4 text-sm sm:px-6 whitespace-nowrap">
                                                {statusBadge(event.status)}
                                            </td>
                                            <td className="px-4 py-4 text-sm text-gray-600 sm:px-6 whitespace-nowrap">
                                                <span className="font-semibold text-gray-900">{event.registrations_count}</span> peserta
                                            </td>
                                            <td className="px-4 py-4 text-right text-sm sm:px-6 whitespace-nowrap">
                                                <Link
                                                    href={route('organizer.events.show', event.id)}
                                                    className="font-semibold text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Kelola
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {events.links?.length > 3 && (
                        <div className="mt-4 flex flex-wrap gap-1">
                            {events.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    className={`rounded px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100'
                                    } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
