const variants = {
    primary: 'btn btn-primary',
    secondary: 'btn btn-secondary',
    ghost: 'btn btn-ghost',
    danger: 'btn btn-danger',
    'danger-ghost': 'btn btn-danger-ghost',
};

const sizes = {
    sm: 'btn-sm',
    md: '',
    lg: 'btn-lg',
};

export function buttonClass({ variant = 'primary', size = 'md', className = '' } = {}) {
    return [variants[variant] ?? variants.primary, sizes[size] ?? '', className]
        .filter(Boolean)
        .join(' ');
}

export default function Button({
    variant = 'primary',
    size = 'md',
    type = 'button',
    className = '',
    children,
    ...props
}) {
    return (
        <button
            {...props}
            type={type}
            className={buttonClass({ variant, size, className })}
        >
            {children}
        </button>
    );
}
