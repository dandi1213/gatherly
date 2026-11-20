import { Link } from '@inertiajs/react';

export function BrandMark({ className = 'h-9 w-9' }) {
    return (
        <span
            className={`inline-flex shrink-0 items-center justify-center rounded-lg bg-primary-500 text-white ${className}`}
            aria-hidden="true"
        >
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

export default function Brand({ href = '/', wordmark = true, className = '' }) {
    return (
        <Link
            href={href}
            className={`inline-flex items-center gap-2.5 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 ${className}`.trim()}
        >
            <BrandMark />
            {wordmark && (
                <span className="text-lg font-bold tracking-tight text-ink">Gatherly</span>
            )}
        </Link>
    );
}
