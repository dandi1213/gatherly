import PublicLayout from '@/Layouts/PublicLayout';
import { formatDateRange, formatDateTime } from '@/utils/format';
import { Head, Link, usePage } from '@inertiajs/react';

const formatLabels = {
    online: 'Online',
    offline: 'Offline',
    hybrid: 'Hybrid',
};

export default function Show({ event, ticketTypes, myRegistration, registrationOpen, isPreview }) {
    const user = usePage().props.auth.user;
    const isPast = new Date(event.end_at) < new Date();
    const availableTicketTypes = ticketTypes.filter(
        (ticketType) => ticketType.is_available_now && ticketType.remaining_quota > 0,
    );
    const canRegister = registrationOpen && !isPast && !myRegistration && availableTicketTypes.length > 0 && !isPreview;

    return (
        <PublicLayout>
            <Head title={event.title} />

            {isPreview && (
                <div className="border-b border-amber-200 bg-amber-50">
                    <div className="mx-auto max-w-7xl px-4 py-3 text-sm text-amber-800 sm:px-6 lg:px-8">
                        Mode preview: event ini belum dipublikasikan sehingga tidak tampil di halaman publik.
                    </div>
                </div>
            )}

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <Link href={route('events.index')} className="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    ← Kembali ke daftar event
                </Link>

                <div className="mt-4 grid gap-8 lg:grid-cols-[380px_1fr]">
                    <div>
                        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div className="aspect-[4/5] w-full bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600">
                                {event.poster_url ? (
                                    <img src={event.poster_url} alt={event.title} className="h-full w-full object-cover" />
                                ) : (
                                    <div className="flex h-full items-center justify-center p-8">
                                        <span className="text-center text-3xl font-bold text-white/90">{event.title}</span>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="mt-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-500">Penyelenggara</h2>
                            <p className="mt-2 text-base font-semibold text-gray-900">
                                {event.organizer?.org_name || event.organizer?.name}
                            </p>
                            {event.organizer?.org_description && (
                                <p className="mt-1 text-sm text-gray-500">{event.organizer.org_description}</p>
                            )}
                        </div>
                    </div>

                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {event.category?.name ?? 'Event'}
                            </span>
                            <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                {formatLabels[event.format] ?? event.format}
                            </span>
                            {isPast && (
                                <span className="rounded-full bg-gray-800 px-3 py-1 text-xs font-semibold text-white">
                                    Selesai
                                </span>
                            )}
                        </div>

                        <h1 className="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            {event.title}
                        </h1>

                        <div className="mt-6 grid gap-4 sm:grid-cols-2">
                            <div className="rounded-xl border border-gray-200 bg-white p-4">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu</div>
                                <div className="mt-1 text-sm font-medium text-gray-900">
                                    {formatDateRange(event.start_at, event.end_at)}
                                </div>
                            </div>
                            <div className="rounded-xl border border-gray-200 bg-white p-4">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">Lokasi</div>
                                <div className="mt-1 text-sm font-medium text-gray-900">
                                    {event.format === 'online' ? 'Online' : event.location || 'Lokasi menyusul'}
                                </div>
                                {event.online_url && (
                                    <a
                                        href={event.online_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-1 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-800"
                                    >
                                        Buka tautan event →
                                    </a>
                                )}
                            </div>
                            <div className="rounded-xl border border-gray-200 bg-white p-4 sm:col-span-2">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Periode Registrasi
                                </div>
                                <div className="mt-1 text-sm font-medium text-gray-900">
                                    {formatDateTime(event.registration_start_at)} –{' '}
                                    {formatDateTime(event.registration_end_at)}
                                </div>
                                {!registrationOpen && !isPast && (
                                    <div className="mt-1 text-xs font-medium text-amber-700">
                                        Registrasi sedang ditutup.
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="mt-8">
                            <h2 className="text-lg font-semibold text-gray-900">Tentang event</h2>
                            <p className="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600">{event.description}</p>
                        </div>

                        <div className="mt-8">
                            <div className="flex items-center justify-between">
                                <h2 className="text-lg font-semibold text-gray-900">Tipe Tiket</h2>
                                <span className="text-sm text-gray-500">
                                    {ticketTypes.length} tipe tersedia
                                </span>
                            </div>

                            <div className="mt-3 space-y-3">
                                {ticketTypes.length === 0 && (
                                    <div className="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-500">
                                        Belum ada tipe tiket untuk event ini.
                                    </div>
                                )}

                                {ticketTypes.map((ticketType) => {
                                    const soldOut = ticketType.remaining_quota <= 0;
                                    const unavailable = !ticketType.is_available_now;
                                    const disabled = soldOut || unavailable || !canRegister;

                                    return (
                                        <div
                                            key={ticketType.id}
                                            className="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <div>
                                                <h3 className="text-base font-semibold text-gray-900">{ticketType.name}</h3>
                                                {ticketType.description && (
                                                    <p className="mt-1 text-sm text-gray-500">{ticketType.description}</p>
                                                )}
                                                <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                                    <span className="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">
                                                        Sisa {ticketType.remaining_quota} dari {ticketType.quota}
                                                    </span>
                                                    {soldOut && (
                                                        <span className="rounded-full bg-red-100 px-2 py-0.5 font-semibold text-red-700">
                                                            Kuota habis
                                                        </span>
                                                    )}
                                                    {!soldOut && unavailable && (
                                                        <span className="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700">
                                                            Di luar periode
                                                        </span>
                                                    )}
                                                </div>
                                            </div>

                                            {myRegistration ? (
                                                <span className="shrink-0 text-sm font-medium text-green-700">
                                                    Sudah terdaftar
                                                </span>
                                            ) : disabled ? (
                                                <span className="shrink-0 rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400">
                                                    {soldOut
                                                        ? 'Kuota Habis'
                                                        : unavailable
                                                          ? 'Tidak Tersedia'
                                                          : 'Registrasi Ditutup'}
                                                </span>
                                            ) : (
                                                <Link
                                                    href={route('events.register', {
                                                        event: event.slug,
                                                        ticket_type: ticketType.id,
                                                    })}
                                                    className="shrink-0 rounded-lg bg-indigo-600 px-5 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500"
                                                >
                                                    {user ? 'Daftar' : 'Masuk untuk Daftar'}
                                                </Link>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>

                            {myRegistration?.ticket && (
                                <div className="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                                    Anda sudah terdaftar pada event ini.{' '}
                                    <Link
                                        href={route('tickets.show', myRegistration.ticket.id)}
                                        className="font-semibold underline"
                                    >
                                        Buka tiket Anda
                                    </Link>
                                    .
                                </div>
                            )}

                            {!user && canRegister && (
                                <p className="mt-4 text-sm text-gray-500">
                                    Anda perlu{' '}
                                    <Link href={route('login')} className="font-semibold text-indigo-600 hover:text-indigo-800">
                                        masuk
                                    </Link>{' '}
                                    atau{' '}
                                    <Link href={route('register')} className="font-semibold text-indigo-600 hover:text-indigo-800">
                                        membuat akun
                                    </Link>{' '}
                                    untuk mendaftar. Setelah masuk, Anda akan kembali ke halaman ini.
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
