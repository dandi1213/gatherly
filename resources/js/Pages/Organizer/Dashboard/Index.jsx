import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

function StatBox({ label, value, hint, accent = 'text-gray-900' }) {
    return (
        <div className="rounded-lg bg-white p-4 shadow-sm">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{label}</p>
            <p className={`mt-1 text-2xl font-bold ${accent}`}>{value}</p>
            {hint && <p className="mt-0.5 text-xs text-gray-500">{hint}</p>}
        </div>
    );
}

const statusBadge = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        completed: 'bg-indigo-100 text-indigo-800',
    };
    return (
        <span
            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                map[status] ?? 'bg-gray-100 text-gray-700'
            }`}
        >
            {status}
        </span>
    );
};

export default function Dashboard({ events, stats }) {
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Dashboard
                    </h2>
                    <p className="text-sm text-gray-500">Selamat datang kembali</p>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-6 sm:py-8">
                <div className="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">
                            {flash.error}
                        </div>
                    )}

                    {/* Statistik utama */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <StatBox
                            label="Total Event"
                            value={events?.length ?? 0}
                            accent="text-blue-600"
                        />
                        <StatBox
                            label="Dipublikasi"
                            value={events?.filter((e) => e.status === 'published').length ?? 0}
                            accent="text-green-600"
                        />
                        <StatBox
                            label="Draft"
                            value={events?.filter((e) => e.status === 'draft').length ?? 0}
                            accent="text-indigo-600"
                        />
                        <StatBox
                            label="Dibatalkan"
                            value={events?.filter((e) => e.status === 'cancelled').length ?? 0}
                            accent="text-red-600"
                        />
                    </div>

                    {/* CTA tombol */}
                    <div className="flex gap-3 flex-wrap">
                        <Link
                            href={route('organizer.events.create')}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            + Buat Event
                        </Link>
                        {stats.total_registrations > 0 && (
                            <Link
                                href={route('organizer.events.export', stats.export_event_id)}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                            >
                                Ekspor CSV
                            </Link>
                        )}
                    </div>

                    {/* Daftar event */}
                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">
                            Event Saya
                        </h3>

                        {events?.length === 0 && (
                            <p className="text-sm text-gray-500">
                                Belum ada event. Klik "Buat Event" untuk mulai.
                            </p>
                        )}

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            Judul
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            Kategori
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            Periode
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {events?.map((event) => (
                                        <tr key={event.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 text-sm font-medium text-gray-900">
                                                {event.title}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {event.category?.name}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {new Date(event.registration_start_at).toLocaleString(
                                                    'id-ID'
                                                )} —{' '}
                                                {new Date(event.registration_end_at).toLocaleString(
                                                    'id-ID'
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {statusBadge(event.status)}
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm">
                                                <Link
                                                    href={route('organizer.events.show', event.id)}
                                                    className="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Kelola
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                    {events?.length === 0 && (
                                        <tr>
                                            <td colSpan={6} className="px-6 py-8 text-center text-sm text-gray-500">
                                                Belum ada event. Klik "Buat Event" untuk mulai.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}