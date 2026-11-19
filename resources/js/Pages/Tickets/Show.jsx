import PublicLayout from '@/Layouts/PublicLayout';
import { formatDateRange, formatDateTime, statusClasses, statusLabel } from '@/utils/format';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ ticket, qrSvg, canCancel }) {
    const event = ticket.event;
    const registration = ticket.registration;
    const [copied, setCopied] = useState(false);

    const cancelRegistration = () => {
        if (!window.confirm('Batalkan registrasi ini? Tiket akan hangus dan kuota dikembalikan.')) {
            return;
        }

        router.post(route('registrations.cancel', registration.id));
    };

    const copyRegistrationNumber = () => {
        if (registration?.registration_number) {
            navigator.clipboard.writeText(registration.registration_number);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    return (
        <PublicLayout>
            <Head>
                <title>{`Tiket · ${event.title}`}</title>
                <style>{`
                    @page {
                        size: A4 portrait;
                        margin: 8mm;
                    }
                    @media print {
                        html, body, #app, .min-h-screen {
                            min-height: auto !important;
                            height: auto !important;
                            background: white !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            overflow: visible !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .ticket-print-wrapper {
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                            page-break-after: avoid !important;
                            break-after: avoid !important;
                            max-width: 100% !important;
                            margin: 0 auto !important;
                            padding: 0 !important;
                        }
                    }
                `}</style>
            </Head>

            <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 ticket-print-wrapper print:p-0 print:m-0 print:max-w-none">
                {/* Navigation and Actions */}
                <div className="flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <Link
                        href={route('tickets.index')}
                        className="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors"
                    >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Tiket Saya
                    </Link>
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => window.print()}
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                        >
                            <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Tiket
                        </button>
                        {canCancel && (
                            <button
                                type="button"
                                onClick={cancelRegistration}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 transition-colors"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batalkan Registrasi
                            </button>
                        )}
                    </div>
                </div>

                {/* Status Banners */}
                {ticket.status === 'cancelled' && (
                    <div className="mt-6 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:mt-2 print:p-2.5 print:text-xs">
                        <svg className="w-5 h-5 shrink-0 text-red-600 print:w-4 print:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Tiket ini telah dibatalkan dan tidak dapat digunakan untuk check-in.</span>
                    </div>
                )}
                {ticket.status === 'checked_in' && (
                    <div className="mt-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 print:mt-2 print:p-2.5 print:text-xs">
                        <svg className="w-5 h-5 shrink-0 text-emerald-600 print:w-4 print:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            Tiket ini sudah digunakan untuk check-in
                            {ticket.checked_in_at ? ` pada ${formatDateTime(ticket.checked_in_at)}` : ''}.
                        </span>
                    </div>
                )}

                {/* Main Ticket Card */}
                <div className="mt-6 overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-xl shadow-gray-200/50 print:mt-0 print:rounded-2xl print:shadow-none print:border print:border-gray-300">
                    {/* Header Banner */}
                    <div className="relative bg-indigo-700 p-6 text-white sm:p-8 print:p-4 print:bg-indigo-800">
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <div className="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider backdrop-blur-sm print:px-2.5 print:py-0.5 print:text-[10px]">
                                <span className="h-2 w-2 rounded-full bg-emerald-400 print:hidden animate-pulse"></span>
                                Tiket Digital Resmi
                            </div>
                            <span className={`rounded-full px-3.5 py-1 text-xs font-bold shadow-sm print:px-2.5 print:py-0.5 print:text-[10px] ${statusClasses(ticket.status)}`}>
                                {statusLabel(ticket.status)}
                            </span>
                        </div>

                        <h1 className="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl text-white print:mt-2 print:text-xl">
                            {event.title}
                        </h1>

                        <div className="mt-3 flex flex-wrap gap-x-6 gap-y-1.5 text-sm text-indigo-100 print:mt-1.5 print:text-xs">
                            <div className="flex items-center gap-2">
                                <svg className="w-4 h-4 text-indigo-200 shrink-0 print:w-3.5 print:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{formatDateRange(event.start_at, event.end_at)}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <svg className="w-4 h-4 text-indigo-200 shrink-0 print:w-3.5 print:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{event.format === 'online' ? 'Online Event' : event.location || 'Lokasi menyusul'}</span>
                            </div>
                        </div>
                    </div>

                    {/* Perforated Divider (Notches) */}
                    <div className="relative flex items-center justify-between bg-white px-2 py-0">
                        <div className="h-6 w-3 rounded-r-full bg-gray-100 border-r border-y border-gray-200/80 -ml-2 print:border-gray-300"></div>
                        <div className="w-full border-t-2 border-dashed border-gray-200 mx-3 print:border-gray-300"></div>
                        <div className="h-6 w-3 rounded-l-full bg-gray-100 border-l border-y border-gray-200/80 -mr-2 print:border-gray-300"></div>
                    </div>

                    {/* Ticket Body: Two-column layout */}
                    <div className="grid grid-cols-1 md:grid-cols-12 bg-white print:grid-cols-12">
                        {/* Left / Info Section */}
                        <div className="p-6 sm:p-8 md:col-span-7 lg:col-span-8 flex flex-col justify-between print:col-span-7 print:p-4">
                            <div>
                                <h2 className="text-xs font-bold uppercase tracking-wider text-indigo-600 print:text-[10px]">
                                    Informasi Peserta & Pendaftaran
                                </h2>

                                <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-5 print:mt-2.5 print:grid-cols-2 print:gap-y-2.5 print:gap-x-4">
                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-wide text-gray-400 print:text-[9px]">
                                            Nama Lengkap
                                        </div>
                                        <div className="mt-0.5 text-base font-bold text-gray-900 break-words print:text-sm">
                                            {registration.attendee_name}
                                        </div>
                                        <div className="text-xs text-gray-500 break-all print:text-[10px]">
                                            {registration.attendee_email}
                                        </div>
                                    </div>

                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-wide text-gray-400 print:text-[9px]">
                                            Tipe Tiket
                                        </div>
                                        <div className="mt-0.5">
                                            <span className="inline-flex items-center rounded-lg bg-indigo-50 px-2 py-0.5 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 print:text-xs">
                                                {ticket.ticket_type?.name || 'Standard Pass'}
                                            </span>
                                        </div>
                                        {ticket.ticket_type?.description && (
                                            <div className="mt-0.5 text-xs text-gray-500 line-clamp-2 print:text-[9px]">
                                                {ticket.ticket_type.description}
                                            </div>
                                        )}
                                    </div>

                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-wide text-gray-400 print:text-[9px]">
                                            Nomor Registrasi
                                        </div>
                                        <div className="mt-0.5 flex items-center gap-2">
                                            <span className="font-mono text-sm font-bold tracking-wider text-gray-900 bg-gray-100 px-2 py-0.5 rounded border border-gray-200 print:text-xs">
                                                {registration.registration_number}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={copyRegistrationNumber}
                                                className="text-xs text-gray-500 hover:text-indigo-600 transition-colors p-1 print:hidden"
                                                title="Salin nomor registrasi"
                                            >
                                                {copied ? (
                                                    <span className="text-emerald-600 font-medium">Tersalin!</span>
                                                ) : (
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                )}
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <div className="text-xs font-semibold uppercase tracking-wide text-gray-400 print:text-[9px]">
                                            Waktu Terdaftar
                                        </div>
                                        <div className="mt-0.5 text-sm font-medium text-gray-800 print:text-xs">
                                            {formatDateTime(registration.created_at)}
                                        </div>
                                    </div>

                                    {registration.attendee_phone && (
                                        <div>
                                            <div className="text-xs font-semibold uppercase tracking-wide text-gray-400 print:text-[9px]">
                                                Nomor Telepon
                                            </div>
                                            <div className="mt-0.5 text-sm font-medium text-gray-800 print:text-xs">
                                                {registration.attendee_phone}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="mt-6 pt-3 border-t border-gray-100 text-xs text-gray-500 flex items-center gap-2 print:mt-3 print:pt-2 print:text-[9px]">
                                <svg className="w-4 h-4 text-emerald-600 shrink-0 print:w-3.5 print:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Tiket sah dan terhubung dengan akun Anda. Registrasi 1 orang = 1 tiket.</span>
                            </div>
                        </div>

                        {/* Right / QR Code Stub Section */}
                        <div className="p-6 sm:p-8 md:col-span-5 lg:col-span-4 bg-gray-50/70 border-t md:border-t-0 md:border-l border-dashed border-gray-200 flex flex-col items-center justify-center text-center print:col-span-5 print:border-t-0 print:border-l print:border-gray-300 print:p-4 print:bg-gray-50">
                            <span className="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2.5 print:text-[9px] print:mb-1.5">
                                QR Masuk Event
                            </span>

                            {/* Safe Container for SVG in Print & Screen */}
                            <div className="relative w-48 max-w-full aspect-square rounded-2xl bg-white p-3 shadow-sm border border-gray-200 flex items-center justify-center overflow-hidden print:w-36 print:h-36 print:p-2 print:rounded-xl print:border-gray-300">
                                <div
                                    className={`w-full h-full flex items-center justify-center [&_svg]:w-full [&_svg]:h-full [&_svg]:max-w-full [&_svg]:max-h-full [&_svg]:block ${
                                        ticket.status === 'active' ? '' : 'opacity-30 grayscale'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: qrSvg }}
                                />
                                {ticket.status === 'checked_in' && (
                                    <div className="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex flex-col items-center justify-center p-2">
                                        <div className="rounded-full bg-emerald-100 p-1.5 text-emerald-600 print:p-1">
                                            <svg className="w-5 h-5 print:w-4 print:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span className="mt-1 text-xs font-bold text-emerald-800 print:text-[9px]">CHECKED IN</span>
                                    </div>
                                )}
                                {ticket.status === 'cancelled' && (
                                    <div className="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex flex-col items-center justify-center p-2">
                                        <div className="rounded-full bg-red-100 p-1.5 text-red-600 print:p-1">
                                            <svg className="w-5 h-5 print:w-4 print:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        <span className="mt-1 text-xs font-bold text-red-800 print:text-[9px]">DIBATALKAN</span>
                                    </div>
                                )}
                            </div>

                            <p className="mt-2.5 text-xs font-medium text-gray-600 max-w-[200px] print:mt-1.5 print:text-[9px]">
                                Tunjukkan QR ini kepada staff di pintu masuk saat check-in.
                            </p>

                            <div className="mt-2.5 pt-2.5 border-t border-gray-200/80 w-full print:mt-1.5 print:pt-1.5 print:border-gray-300">
                                <div className="text-[10px] uppercase font-mono tracking-wider text-gray-400 print:text-[8px]">
                                    Kode Tiket
                                </div>
                                <div className="font-mono text-xs text-gray-600 truncate max-w-[180px] mx-auto mt-0.5 print:text-[9px]">
                                    {ticket.ticket_code ? `${ticket.ticket_code.slice(0, 16)}...` : '-'}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Footer Notice */}
                    <div className="border-t border-gray-100 bg-gray-50 px-6 py-3 text-center text-xs text-gray-500 print:py-1.5 print:text-[9px] print:border-gray-200">
                        Harap simpan tiket ini atau buka langsung melalui browser smartphone Anda saat acara berlangsung.
                    </div>
                </div>

                <p className="mt-6 text-center text-xs text-gray-400 print:hidden">
                    Mengalami kendala pada tiket ini? Hubungi panitia penyelenggara melalui email akun Anda.
                </p>
            </div>
        </PublicLayout>
    );
}
