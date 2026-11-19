import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-50 px-4 py-8 sm:justify-center sm:px-6 sm:py-12">
            <div>
                <Link href="/" className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                        <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M4 8.5V6.75A1.75 1.75 0 0 1 5.75 5h12.5A1.75 1.75 0 0 1 20 6.75V8.5a2.5 2.5 0 0 0 0 5v3.75A1.75 1.75 0 0 1 18.25 19H5.75A1.75 1.75 0 0 1 4 17.25V13.5a2.5 2.5 0 0 0 0-5Z"
                            />
                            <path strokeLinecap="round" d="M9 9.5h6M9 12h6M9 14.5h3.5" />
                        </svg>
                    </span>
                    <span className="text-2xl font-bold tracking-tight text-gray-900">Gatherly</span>
                </Link>
            </div>

            <div className="mt-6 w-full sm:max-w-md">
                <div className="mb-2">
                    <Link
                        href={route('home')}
                        className="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors"
                    >
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Beranda
                    </Link>
                </div>

                <div className="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    {children}
                </div>
            </div>
        </div>
    );
}
