import PublicLayout from '@/Layouts/PublicLayout';
import { formatDateRange, statusClasses, statusLabel } from '@/utils/format';
import { Head, Link } from '@inertiajs/react';

export default function Index({ registrations }) {
    return (
        <PublicLayout>
            <Head title="Tiket Saya" />

            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Tiket Saya</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Semua registrasi dan tiket digital milik akun Anda.
                        </p>
                    </div>
                    <Link
                        href={route('events.index')}
                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                    >
                        Cari Event Lain
                    </Link>
                </div>

                {registrations.data.length === 0 ? (
                    <div className="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                        <h2 className="text-lg font-semibold text-gray-800">Belum ada tiket</h2>
                        <p className="mt-2 text-sm text-gray-500">
                            Daftar event pertama Anda dan tiket digital akan muncul di sini.
                        </p>
                        <Link
                            href={route('events.index')}
                            className="mt-5 inline-flex rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            Jelajahi Event
                        </Link>
                    </div>
                ) : (
                    <div className="mt-8 space-y-4">
                        {registrations.data.map((registration) => {
                            const ticket = registration.ticket;
                            const event = registration.event;

                            return (
                                <div
                                    key={registration.id}
                                    className={`overflow-hidden rounded-2xl border bg-white shadow-sm ${
                                        registration.status === 'cancelled'
                                            ? 'border-gray-200 opacity-75'
                                            : 'border-gray-200'
                                    }`}
                                >
                                    <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span
                                                    className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusClasses(
                                                        registration.status,
                                                    )}`}
                                                >
                                                    {statusLabel(registration.status)}
                                                </span>
                                                {ticket && (
                                                    <span
                                                        className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusClasses(
                                                            ticket.status,
                                                        )}`}
                                                    >
                                                        Tiket: {statusLabel(ticket.status)}
                                                    </span>
                                                )}
                                            </div>

                                            <h2 className="mt-2 truncate text-lg font-semibold text-gray-900">
                                                {event?.title}
                                            </h2>

                                            <div className="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500">
                                                {event && (
                                                    <span>{formatDateRange(event.start_at, event.end_at)}</span>
                                                )}
                                                <span>{registration.ticket_type?.name}</span>
                                                <span className="font-mono text-xs text-gray-400">
                                                    {registration.registration_number}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="flex shrink-0 items-center gap-3">
                                            {event && (
                                                <Link
                                                    href={route('events.show', event.slug)}
                                                    className="text-sm font-medium text-gray-500 hover:text-gray-800"
                                                >
                                                    Detail event
                                                </Link>
                                            )}
                                            {ticket && (
                                                <Link
                                                    href={route('tickets.show', ticket.id)}
                                                    className="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black"
                                                >
                                                    Lihat Tiket
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {registrations.links?.length > 3 && (
                    <div className="mt-8 flex flex-wrap justify-center gap-1">
                        {registrations.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url ?? '#'}
                                preserveScroll
                                className={`rounded-md px-3 py-1.5 text-sm ${
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100'
                                } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}
