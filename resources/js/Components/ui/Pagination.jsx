import { Link } from '@inertiajs/react';

export default function Pagination({ links, className = '' }) {
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <nav
            aria-label="Navigasi halaman"
            className={`flex flex-wrap items-center justify-center gap-1.5 ${className}`.trim()}
        >
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        aria-current={link.active ? 'page' : undefined}
                        className={`inline-flex min-w-9 items-center justify-center rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 ${
                            link.active
                                ? 'border-primary-500 bg-primary-500 text-white'
                                : 'border-line bg-surface text-muted hover:border-zinc-300 hover:text-ink'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span
                        key={index}
                        aria-disabled="true"
                        className="inline-flex min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-line bg-canvas px-3 py-1.5 text-sm font-medium text-zinc-400"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ),
            )}
        </nav>
    );
}
