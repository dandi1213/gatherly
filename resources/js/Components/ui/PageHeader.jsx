export default function PageHeader({
    eyebrow,
    title,
    description,
    actions,
    className = '',
}) {
    return (
        <div
            className={`flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between ${className}`.trim()}
        >
            <div className="min-w-0">
                {eyebrow && (
                    <p className="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">
                        {eyebrow}
                    </p>
                )}
                <h1 className="text-2xl font-bold tracking-editorial text-ink sm:text-3xl">
                    {title}
                </h1>
                {description && (
                    <p className="mt-1.5 max-w-2xl text-sm leading-6 text-muted">
                        {description}
                    </p>
                )}
            </div>
            {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
