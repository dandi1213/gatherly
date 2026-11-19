import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';

function formatDateTime(value) {
    if (!value) return '—';
    return new Date(value).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

export default function Index({ events }) {
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Check-in Event
                    </h2>
                    <p className="text-sm text-gray-500">
                        Pilih event yang Anda kelola atau ditugaskan.
                    </p>
                </div>
            }
        >
            <Head title="Check-in" />

            <div className="py-6 sm:py-8">
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

                    {events.length === 0 && (
                        <div className="rounded-lg bg-white p-8 text-center shadow sm:rounded-lg">
                            <p className="font-medium text-gray-800">
                                Belum ada event untuk check-in.
                            </p>
                            <p className="mt-1 text-sm text-gray-500">
                                Anda dapat melakukan check-in pada event Published yang Anda
                                kelola, atau event tempat Anda ditugaskan sebagai staff.
                            </p>
                        </div>
                    )}

                    {events.map((event) => {
                        const percent =
                            event.total > 0
                                ? Math.round((event.checked_in / event.total) * 100)
                                : 0;

                        return (
                            <Link
                                key={event.id}
                                href={route('checkin.show', event.id)}
                                className="block rounded-xl bg-white p-5 shadow transition hover:shadow-md sm:rounded-lg"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p className="text-base font-semibold text-gray-900">
                                            {event.title}
                                        </p>
                                        <p className="mt-1 text-sm text-gray-500">
                                            {formatDateTime(event.start_at)} ·{' '}
                                            {event.location || 'Online'}
                                        </p>
                                    </div>
                                    <div className="flex flex-col items-end gap-1">
                                        <span
                                            className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                event.is_window_open
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-600'
                                            }`}
                                        >
                                            {event.is_window_open
                                                ? 'Check-in dibuka'
                                                : 'Di luar jendela'}
                                        </span>
                                        {event.is_staff && (
                                            <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                                                Staff
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div className="mt-4">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-gray-600">
                                            Check-in: {event.checked_in} / {event.total}
                                        </span>
                                        <span className="font-semibold text-gray-800">{percent}%</span>
                                    </div>
                                    <div className="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                        <div
                                            className="h-2 rounded-full bg-indigo-600"
                                            style={{ width: `${percent}%` }}
                                        />
                                    </div>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
