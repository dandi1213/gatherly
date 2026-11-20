const tones = {
    neutral: 'badge badge-neutral',
    primary: 'badge badge-primary',
    success: 'badge badge-success',
    warning: 'badge badge-warning',
    danger: 'badge badge-danger',
};

export default function Badge({ tone = 'neutral', dot = false, className = '', children }) {
    return (
        <span className={`${tones[tone] ?? tones.neutral} ${className}`.trim()}>
            {dot && (
                <span
                    aria-hidden="true"
                    className="h-1.5 w-1.5 shrink-0 rounded-full bg-current"
                />
            )}
            {children}
        </span>
    );
}
