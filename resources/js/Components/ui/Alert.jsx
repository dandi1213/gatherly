const toneStyles = {
    success: 'alert-success',
    warning: 'alert-warning',
    error: 'alert-error',
    info: 'alert-info',
};

const icons = {
    success: (
        <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="m4.5 12.75 6 6 9-13.5"
        />
    ),
    warning: (
        <>
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
            />
        </>
    ),
    error: (
        <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M12 9v3.75m0 3h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
    ),
    info: (
        <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"
        />
    ),
};

export default function Alert({ tone = 'info', title, children, className = '' }) {
    return (
        <div
            role={tone === 'error' ? 'alert' : 'status'}
            className={`alert ${toneStyles[tone] ?? toneStyles.info} flex items-start gap-3 ${className}`.trim()}
        >
            <svg
                aria-hidden="true"
                className="mt-0.5 h-5 w-5 shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth="1.8"
            >
                {icons[tone] ?? icons.info}
            </svg>
            <div className="min-w-0">
                {title && <p className="font-semibold">{title}</p>}
                {children && <div className={title ? 'mt-1' : ''}>{children}</div>}
            </div>
        </div>
    );
}
