export default function Card({ as: Tag = 'div', className = '', children, ...props }) {
    return (
        <Tag {...props} className={`card ${className}`.trim()}>
            {children}
        </Tag>
    );
}

export function CardHeader({ className = '', children, ...props }) {
    return (
        <div
            {...props}
            className={`flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6 ${className}`.trim()}
        >
            {children}
        </div>
    );
}

export function CardBody({ className = '', children, ...props }) {
    return (
        <div {...props} className={`p-5 sm:p-6 ${className}`.trim()}>
            {children}
        </div>
    );
}
