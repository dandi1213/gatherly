import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

function BrandMark() {
    return (
        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M4 8.5V6.75A1.75 1.75 0 0 1 5.75 5h12.5A1.75 1.75 0 0 1 20 6.75V8.5a2.5 2.5 0 0 0 0 5v3.75A1.75 1.75 0 0 1 18.25 19H5.75A1.75 1.75 0 0 1 4 17.25V13.5a2.5 2.5 0 0 0 0-5Z"
                />
                <path strokeLinecap="round" d="M9 9.5h6M9 12h6M9 14.5h3.5" />
            </svg>
        </span>
    );
}

export default function PublicLayout({ children }) {
    const { auth, flash } = usePage().props;
    const user = auth?.user;
    const canCheckIn = auth?.can_check_in;
    const [open, setOpen] = useState(false);

    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur print:hidden">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link href={route('events.index')} className="flex items-center gap-3">
                        <BrandMark />
                        <span className="text-lg font-bold tracking-tight text-gray-900">Gatherly</span>
                    </Link>

                    <div className="hidden items-center gap-1 md:flex">
                        <Link
                            href={route('events.index')}
                            className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                        >
                            Jelajahi Event
                        </Link>
                        {user && (
                            <Link
                                href={route('tickets.index')}
                                className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                            >
                                Tiket Saya
                            </Link>
                        )}
                        {user && canCheckIn && (
                            <Link
                                href={route('checkin.index')}
                                className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                            >
                                Check-in
                            </Link>
                        )}
                        {user ? (
                            <>
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Dashboard
                                </Link>
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="ml-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Keluar
                                </Link>
                            </>
                        ) : (
                            <div className="ml-2 flex items-center gap-2">
                                <Link
                                    href={route('login')}
                                    className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Masuk
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                                >
                                    Daftar
                                </Link>
                            </div>
                        )}
                    </div>

                    <button
                        type="button"
                        onClick={() => setOpen((value) => !value)}
                        className="rounded-md p-2 text-gray-500 hover:bg-gray-100 md:hidden"
                        aria-label="Buka menu"
                    >
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                            {open ? (
                                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
                            ) : (
                                <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            )}
                        </svg>
                    </button>
                </div>

                {open && (
                    <div className="border-t border-gray-200 bg-white px-4 pb-4 pt-2 md:hidden">
                        <Link href={route('events.index')} className="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            Jelajahi Event
                        </Link>
                        {user && (
                            <Link href={route('tickets.index')} className="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                Tiket Saya
                            </Link>
                        )}
                        {user && canCheckIn && (
                            <Link href={route('checkin.index')} className="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                Check-in
                            </Link>
                        )}
                        {user ? (
                            <>
                                <Link href={route('dashboard')} className="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                    Dashboard
                                </Link>
                                <Link href={route('logout')} method="post" as="button" className="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-100">
                                    Keluar
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link href={route('login')} className="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                    Masuk
                                </Link>
                                <Link href={route('register')} className="mt-2 block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white">
                                    Daftar
                                </Link>
                            </>
                        )}
                    </div>
                )}
            </nav>

            <main>
                {flash?.success && (
                    <div className="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                            {flash.success}
                        </div>
                    </div>
                )}
                {flash?.error && (
                    <div className="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {flash.error}
                        </div>
                    </div>
                )}
                {children}
            </main>

            <footer className="mt-16 border-t border-gray-200 bg-white py-8 print:hidden">
                <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 text-sm text-gray-500 sm:flex-row sm:px-6 lg:px-8">
                    <p>&copy; {new Date().getFullYear()} Gatherly. Event management &amp; tiket digital gratis.</p>
                    <p>Dibuat dengan Laravel, Inertia, dan React.</p>
                </div>
            </footer>
        </div>
    );
}
