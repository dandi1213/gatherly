import PublicLayout from '@/Layouts/PublicLayout';
import { formatDate } from '@/utils/format';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

const formatLabels = {
    online: 'Online',
    offline: 'Offline',
    hybrid: 'Hybrid',
};

function EventCard({ event }) {
    return (
        <Link
            href={route('events.show', event.slug)}
            className="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <div className="relative aspect-[16/9] w-full overflow-hidden bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600">
                {event.poster_url ? (
                    <img
                        src={event.poster_url}
                        alt={event.title}
                        className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center">
                        <span className="px-6 text-center text-2xl font-bold text-white/90">{event.title}</span>
                    </div>
                )}
                <span className="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                    {event.category?.name ?? 'Event'}
                </span>
            </div>

            <div className="flex flex-1 flex-col p-5">
                <div className="mb-2 flex items-center gap-2 text-xs font-medium text-indigo-600">
                    <span>{formatDate(event.start_at)}</span>
                    <span className="text-gray-300">•</span>
                    <span>{formatLabels[event.format] ?? event.format}</span>
                </div>
                <h3 className="line-clamp-2 text-lg font-semibold text-gray-900 group-hover:text-indigo-700">
                    {event.title}
                </h3>
                <p className="mt-2 line-clamp-2 text-sm text-gray-500">{event.description}</p>

                <div className="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
                    <span className="truncate text-gray-600">
                        {event.format === 'online' ? 'Online event' : event.location || 'Lokasi menyusul'}
                    </span>
                    <span className="shrink-0 font-semibold text-indigo-600">Detail →</span>
                </div>
            </div>
        </Link>
    );
}

export default function Index({ events, categories, filters }) {
    const [q, setQ] = useState(filters.q ?? '');

    const applyFilters = (overrides = {}) => {
        const next = { ...filters, ...overrides };
        const params = Object.fromEntries(
            Object.entries(next).filter(([, value]) => value !== null && value !== undefined && value !== ''),
        );

        router.get(route('events.index'), params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const submitSearch = (event) => {
        event.preventDefault();
        applyFilters({ q });
    };

    return (
        <PublicLayout>
            <Head title="Jelajahi Event" />

            <section className="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-600 text-white">
                <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                    <div className="max-w-2xl">
                        <span className="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                            Event management &amp; tiket digital gratis
                        </span>
                        <h1 className="mt-4 text-3xl font-bold tracking-tight sm:text-5xl">
                            Temukan event berikutnya, dapatkan tiketnya dalam satu klik.
                        </h1>
                        <p className="mt-4 text-base text-indigo-100 sm:text-lg">
                            Jelajahi event online maupun offline. Daftar dengan akun Gatherly dan tiket digital
                            ber-QR langsung tersedia di halaman Tiket Saya.
                        </p>

                        <form onSubmit={submitSearch} className="mt-8 flex flex-col gap-2 sm:flex-row">
                            <input
                                type="search"
                                value={q}
                                onChange={(event) => setQ(event.target.value)}
                                placeholder="Cari nama event atau lokasi..."
                                className="w-full rounded-xl border-0 bg-white px-4 py-3 text-gray-900 shadow-lg placeholder:text-gray-400 focus:ring-2 focus:ring-white/80"
                            />
                            <button
                                type="submit"
                                className="rounded-xl bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-black"
                            >
                                Cari Event
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="flex flex-col gap-4 border-b border-gray-200 pb-6 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex flex-wrap items-center gap-2">
                        {[
                            ['upcoming', 'Akan Datang'],
                            ['past', 'Selesai'],
                            ['all', 'Semua'],
                        ].map(([value, label]) => (
                            <button
                                key={value}
                                type="button"
                                onClick={() => applyFilters({ period: value })}
                                className={`rounded-full px-4 py-2 text-sm font-medium transition ${
                                    filters.period === value
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-100'
                                }`}
                            >
                                {label}
                            </button>
                        ))}
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <select
                            value={filters.format ?? ''}
                            onChange={(event) => applyFilters({ format: event.target.value })}
                            className="rounded-lg border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Semua format</option>
                            <option value="offline">Offline</option>
                            <option value="online">Online</option>
                            <option value="hybrid">Hybrid</option>
                        </select>

                        <select
                            value={filters.category ?? ''}
                            onChange={(event) => applyFilters({ category: event.target.value })}
                            className="rounded-lg border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Semua kategori</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>

                        {(filters.q || filters.format || filters.category) && (
                            <button
                                type="button"
                                onClick={() => {
                                    setQ('');
                                    router.get(route('events.index'), { period: filters.period }, { preserveScroll: true, replace: true });
                                }}
                                className="rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-800"
                            >
                                Reset
                            </button>
                        )}
                    </div>
                </div>

                {filters.q && (
                    <p className="mt-4 text-sm text-gray-500">
                        Hasil pencarian untuk <span className="font-semibold text-gray-800">“{filters.q}”</span>
                    </p>
                )}

                <div className="mt-6">
                    {events.data.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                            <h2 className="text-lg font-semibold text-gray-800">Belum ada event yang cocok</h2>
                            <p className="mt-2 text-sm text-gray-500">
                                Coba kata kunci lain, ubah filter, atau kembali lagi nanti.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {events.data.map((event) => (
                                <EventCard key={event.id} event={event} />
                            ))}
                        </div>
                    )}
                </div>

                {events.links?.length > 3 && (
                    <div className="mt-8 flex flex-wrap justify-center gap-1">
                        {events.links.map((link, index) => (
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
            </section>
        </PublicLayout>
    );
}
