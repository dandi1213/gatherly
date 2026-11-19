import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

function formatDateTime(value) {
    if (!value) return '—';
    return new Date(value).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

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
        active: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };

    return (
        <span
            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                map[status] ?? 'bg-gray-100 text-gray-700'
            }`}
        >
            {status === 'checked_in' ? 'Sudah check-in' : status === 'active' ? 'Aktif' : status}
        </span>
    );
};

export default function Show({ event, stats: initialStats, window: checkInWindow, search, results }) {
    const { flash } = usePage().props;
    const [stats, setStats] = useState(initialStats);
    const [scanning, setScanning] = useState(false);
    const [starting, setStarting] = useState(false);
    const [cameraError, setCameraError] = useState('');
    const [result, setResult] = useState(null);
    const [query, setQuery] = useState(search || '');

    const scannerRef = useRef(null);
    const lastScanRef = useRef({ code: '', at: 0 });
    const resumeTimerRef = useRef(null);

    useEffect(() => {
        setStats(initialStats);
    }, [initialStats]);

    useEffect(() => {
        return () => {
            if (resumeTimerRef.current) {
                clearTimeout(resumeTimerRef.current);
            }

            const scanner = scannerRef.current;
            scannerRef.current = null;

            if (scanner) {
                scanner.stop().catch(() => {});
            }
        };
    }, []);

    const stopScanner = async () => {
        const scanner = scannerRef.current;
        scannerRef.current = null;
        setScanning(false);

        if (scanner) {
            try {
                await scanner.stop();
            } catch {
                // Kamera mungkin sudah berhenti.
            }

            try {
                scanner.clear();
            } catch {
                // Elemen sudah dibersihkan.
            }
        }
    };

    const handleScan = async (decodedText) => {
        const now = Date.now();

        // Abaikan scan berulang dalam 1,8 detik (PRD 7.6).
        if (now - lastScanRef.current.at < 1800) {
            return;
        }

        lastScanRef.current = { code: decodedText, at: now };

        const scanner = scannerRef.current;

        if (scanner) {
            try {
                scanner.pause(true);
            } catch {
                // Abaikan bila scanner belum siap.
            }
        }

        try {
            const { data } = await window.axios.post(
                route('checkin.scan', event.id),
                { code: decodedText },
            );

            setResult(data);

            if (data.stats) {
                setStats(data.stats);
            }

            navigator.vibrate?.(data.ok ? 120 : [60, 60, 60]);
        } catch (error) {
            const message =
                error?.response?.data?.message ||
                'Gagal memproses scan. Periksa koneksi lalu coba lagi.';

            setResult({ ok: false, message, ticket: null });
            navigator.vibrate?.([60, 60, 60]);
        }

        resumeTimerRef.current = setTimeout(() => {
            const active = scannerRef.current;

            if (active) {
                try {
                    active.resume();
                } catch {
                    // Abaikan bila scanner sudah dihentikan.
                }
            }
        }, 1600);
    };

    const startScanner = async () => {
        setCameraError('');
        setStarting(true);

        try {
            const { Html5Qrcode } = await import('html5-qrcode');
            const scanner = new Html5Qrcode('qr-reader');
            scannerRef.current = scanner;

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                handleScan,
                () => {},
            );

            setScanning(true);
        } catch (error) {
            setCameraError(
                'Kamera tidak dapat diakses. Pastikan izin kamera diberikan dan halaman dibuka via HTTPS. ' +
                    (error?.message ?? ''),
            );
            await stopScanner();
        } finally {
            setStarting(false);
        }
    };

    const submitSearch = (e) => {
        e.preventDefault();

        router.get(
            route('checkin.search', event.id),
            { q: query },
            { preserveState: true, preserveScroll: true, only: ['results', 'search'] },
        );
    };

    const manualCheckIn = (ticket) => {
        if (!confirm(`Check-in ${ticket.attendee_name} sekarang?`)) {
            return;
        }

        router.post(route('checkin.manual', [event.id, ticket.id]), {}, { preserveScroll: true });
    };

    const attendanceRate =
        stats.total > 0 ? Math.round((stats.checked_in / stats.total) * 100) : 0;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Check-in
                        </h2>
                        <p className="text-sm text-gray-500">{event.title}</p>
                    </div>
                    <Link
                        href={route('checkin.index')}
                        className="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow hover:bg-gray-50"
                    >
                        Ganti Event
                    </Link>
                </div>
            }
        >
            <Head title={`Check-in - ${event.title}`} />

            <div className="py-5 sm:py-8">
                <div className="mx-auto max-w-xl space-y-4 px-4 sm:px-6">
                    <div className="rounded-lg bg-white p-4 text-sm text-gray-600 shadow-sm">
                        <p className="font-medium text-gray-900">
                            {formatDateTime(event.start_at)} — {formatDateTime(event.end_at)}
                        </p>
                        <p>{event.location || 'Event online'}</p>
                    </div>

                    {flash?.success && (
                        <div className="rounded-md bg-green-50 p-3 text-sm font-medium text-green-800">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="rounded-md bg-red-50 p-3 text-sm font-medium text-red-800">
                            {flash.error}
                        </div>
                    )}

                    <div className="grid grid-cols-3 gap-3">
                        <StatBox
                            label="Check-in"
                            value={stats.checked_in}
                            hint={`dari ${stats.total} tiket`}
                            accent="text-green-600"
                        />
                        <StatBox label="Hadir" value={`${attendanceRate}%`} hint="attendance" />
                        <StatBox label="Belum" value={stats.active} hint="tiket aktif" />
                    </div>

                    {!checkInWindow.open && (
                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <p className="font-semibold">Check-in sedang ditutup.</p>
                            <p className="mt-1">
                                Jendela check-in dibuka mulai {formatDateTime(checkInWindow.start)}{' '}
                                sampai {formatDateTime(checkInWindow.end)}.
                            </p>
                        </div>
                    )}

                    {/* Hasil scan */}
                    {result && (
                        <div
                            className={`rounded-xl border-2 p-5 shadow-sm ${
                                result.ok
                                    ? 'border-green-500 bg-green-50'
                                    : 'border-red-500 bg-red-50'
                            }`}
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        className={`text-lg font-bold ${
                                            result.ok ? 'text-green-800' : 'text-red-800'
                                        }`}
                                    >
                                        {result.ok ? '✓ Check-in berhasil' : '✕ Check-in ditolak'}
                                    </p>
                                    <p
                                        className={`mt-1 text-sm ${
                                            result.ok ? 'text-green-700' : 'text-red-700'
                                        }`}
                                    >
                                        {result.message}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setResult(null)}
                                    className="text-sm font-medium text-gray-500 hover:text-gray-700"
                                >
                                    Tutup
                                </button>
                            </div>

                            {result.ticket && (
                                <div className="mt-3 rounded-lg bg-white/70 p-3 text-sm">
                                    <p className="text-base font-semibold text-gray-900">
                                        {result.ticket.attendee_name}
                                    </p>
                                    <p className="text-gray-600">{result.ticket.attendee_email}</p>
                                    <p className="mt-1 text-gray-600">
                                        {result.ticket.ticket_type} ·{' '}
                                        {result.ticket.registration_number}
                                    </p>
                                    {result.ticket.checked_in_at && (
                                        <p className="mt-1 text-gray-500">
                                            Check-in: {formatDateTime(result.ticket.checked_in_at)}
                                            {result.ticket.checked_in_by
                                                ? ` oleh ${result.ticket.checked_in_by}`
                                                : ''}
                                        </p>
                                    )}
                                </div>
                            )}
                        </div>
                    )}

                    {/* Scanner */}
                    <div className="rounded-xl bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between gap-3">
                            <h3 className="text-base font-semibold text-gray-900">Scan QR Tiket</h3>
                            {scanning ? (
                                <button
                                    type="button"
                                    onClick={stopScanner}
                                    className="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                                >
                                    Hentikan
                                </button>
                            ) : (
                                <button
                                    type="button"
                                    onClick={startScanner}
                                    disabled={starting || !checkInWindow.open}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {starting ? 'Menyiapkan...' : 'Mulai Kamera'}
                                </button>
                            )}
                        </div>

                        <div className="mt-3 overflow-hidden rounded-lg bg-gray-900">
                            <div id="qr-reader" className="min-h-[220px] w-full" />
                            {!scanning && (
                                <div className="-mt-[220px] flex h-[220px] items-center justify-center px-6 text-center text-sm text-gray-300">
                                    Kamera belum aktif. Tekan “Mulai Kamera” untuk memindai QR
                                    tiket.
                                </div>
                            )}
                        </div>

                        {cameraError && (
                            <p className="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-700">
                                {cameraError}
                            </p>
                        )}

                        <p className="mt-3 text-xs text-gray-500">
                            Gunakan kamera belakang. Setelah scan, kamera berhenti sebentar
                            untuk mencegah check-in ganda.
                        </p>
                    </div>

                    {/* Pencarian manual */}
                    <div className="rounded-xl bg-white p-4 shadow-sm">
                        <h3 className="text-base font-semibold text-gray-900">
                            Pencarian Manual
                        </h3>
                        <p className="mt-1 text-xs text-gray-500">
                            Cadangan bila kamera tidak tersedia. Cari berdasarkan nama, email,
                            atau nomor registrasi.
                        </p>

                        <form onSubmit={submitSearch} className="mt-3 flex gap-2">
                            <input
                                type="search"
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                placeholder="Nama / email / nomor registrasi"
                                className="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button
                                type="submit"
                                className="shrink-0 rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                            >
                                Cari
                            </button>
                        </form>

                        {results && (
                            <div className="mt-4 space-y-3">
                                {results.data.length === 0 && (
                                    <p className="text-sm text-gray-500">
                                        Tidak ada peserta yang cocok dengan “{search}”.
                                    </p>
                                )}

                                {results.data.map((ticket) => (
                                    <div
                                        key={ticket.id}
                                        className="rounded-lg border border-gray-200 p-3"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-2">
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    {ticket.attendee_name}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {ticket.attendee_email}
                                                </p>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    {ticket.registration_number} ·{' '}
                                                    {ticket.ticket_type}
                                                </p>
                                            </div>
                                            <div className="flex flex-col items-end gap-1">
                                                {statusBadge(ticket.status)}
                                                {ticket.status === 'active' ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => manualCheckIn(ticket)}
                                                        className="rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-500"
                                                    >
                                                        Check-in
                                                    </button>
                                                ) : (
                                                    ticket.checked_in_at && (
                                                        <span className="text-right text-xs text-gray-500">
                                                            {formatDateTime(ticket.checked_in_at)}
                                                            {ticket.checked_in_by
                                                                ? ` · ${ticket.checked_in_by}`
                                                                : ''}
                                                        </span>
                                                    )
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {results.links?.length > 3 && (
                                    <div className="flex flex-wrap gap-1">
                                        {results.links.map((link, index) => (
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
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
