export default function EmptyState({ title, description, action, icon, className = '' }) {
    return (
        <div
            className={`rounded-xl border border-dashed border-line bg-surface px-6 py-14 text-center ${className}`.trim()}
        >
            {icon && (
                <div className="mx-auto mb-4 flex h-11 w-11 items-center justify-center rounded-full border border-line bg-canvas text-muted">
                    {icon}
                </div>
            )}
            <h2 className="text-base font-semibold text-ink">{title}</h2>
            {description && (
                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">
                    {description}
                </p>
            )}
            {action && <div className="mt-5 flex justify-center">{action}</div>}
        </div>
    );
}
