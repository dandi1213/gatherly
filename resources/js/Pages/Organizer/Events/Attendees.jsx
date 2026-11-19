import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

function formatDateTime(value) {
    if (!value) return '—';
    return new Date(value).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function statusBadge(status) {
    const map = {
        active: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        registered: 'bg-indigo-100 text-indigo-800',
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
}

const roleBadge = (role) => {
    const map = {
        attendee: 'bg-gray-100 text-gray-700',
        organizer: 'bg-green-100 text-green-800',
        admin: 'bg-indigo-100 text-indigo-800',
    };
    return (
        <span
            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                map[role] ?? 'bg-gray-100 text-gray-700'
            }`}
        >
            {role}
        </span>
    );
};

export default function Attendees({ event, registrations, stats }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState('');

    useEffect(() => {
        setSearch('');
    }, [registrations]);

    const columns = [
        { key: 'name', label: 'Nama' },
        { key: 'email', label: 'Email' },
        { key: 'role', label: 'Peran' },
        { key: 'ticket_type', label: 'Tipe Tiket' },
        { key: 'status_registrasi', label: 'Status Registrasi' },
        { key: 'status_tiket', label: 'Status Tiket' },
        { key: 'registered_at', label: 'Daftar' },
        { key: 'checked_in_at', label: 'Check-in' },
        { key: 'actions', label: 'Aksi' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Peserta: {event.title}
                    </h2>
                    <p className="text-sm text-gray-500">
                        {event.registrations_count} peserta terdaftar
                    </p>
                </div>
            }
        >
            <Head title={`Peserta - ${event.title}`} />

            <div className="py-4 sm:py-6">
                <div className="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
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

                    {/* Pencarian */}
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 className="text-base font-semibold text-gray-900 mb-3">Cari Peserta</h3>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                router.get(
                                    route('organizer.events.attendees.export', event.id),
                                    { q: search },
                                    { preserveScroll: true, only: ['results', 'search'] }
                                );
                            }}
                        >
                            <div className="flex gap-2">
                                <InputLabel htmlFor="search" value="Cari nama/email" />
                                <TextInput
                                    id="search"
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Nama, email, atau nomor registrasi"
                                />
                                <button
                                    type="submit"
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                                >
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>

                    {registrations?.data.length === 0 && (
                        <div className="rounded-lg bg-white p-8 text-center shadow sm:rounded-lg">
                            <p className="font-medium text-gray-800">
                                Tidak ada peserta yang cocok.
                            </p>
                            <p className="mt-1 text-sm text-gray-500">
                                Coba filter atau ekspor seluruh daftar.
                            </p>
                        </div>
                    )}

                    {/* Tabel daftar peserta */}
                    {registrations?.data?.length > 0 && (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        {columns.map((col) => (
                                            <th
                                                key={col.key}
                                                className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                {col.label}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {registrations.data.map((reg) => (
                                        <tr
                                            key={reg.id}
                                            className="hover:bg-gray-50 transition-colors"
                                        >
                                            <td className="px-6 py-4 text-sm font-medium text-gray-900">
                                                {reg.attendee_name}
                                            </td>
                                            <td className="px-6 py-4 text-xs text-gray-500">
                                                {reg.attendee_email}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {roleBadge(reg.user?.role ?? '-')}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {reg.ticket_type?.name ?? '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {statusBadge(
                                                    reg.status === 'registered'
                                                        ? 'registered'
                                                        : reg.status
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {reg.checked_in_at
                                                    ? formatDateTime(reg.checked_in_at)
                                                    : 'Belum check-in'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {formatDateTime(reg.registered_at)}
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm">
                                                <Link
                                                    href={route('profile.edit')}
                                                    className="text-indigo-600 hover:text-indigo-900 text-sm"
                                                >
                                                    Profile
                                                </Link>{' '}
                                                {reg.checked_in_at ? (
                                                    <span className="text-green-600 text-sm">
                                                        {formatDateTime(reg.checked_in_at)}{' '
                                                        }{reg.checkedInBy?.name ?? '-'}
                                                    </span>
                                                ) : (
                                                    <span className="text-gray-400 text-sm">
                                                        belum check-in
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Paginasi */}
                    {registrations?.links?.length > 0 && (
                        <div className="mt-4 flex flex-wrap gap-1">
                            {registrations.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url ?? '#'}
                                    preserveScroll
                                    className={`rounded px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
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