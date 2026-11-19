export const formatDate = (value) =>
    new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

export const formatDateTime = (value) =>
    new Date(value).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

export const formatDateRange = (start, end) => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const sameDay = startDate.toDateString() === endDate.toDateString();

    return sameDay
        ? `${formatDate(start)} · ${startDate.toLocaleTimeString('id-ID', {
              hour: '2-digit',
              minute: '2-digit',
          })}–${endDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`
        : `${formatDateTime(start)} – ${formatDateTime(end)}`;
};

export const statusClasses = (status) => {
    const map = {
        active: 'bg-blue-100 text-blue-800',
        registered: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        published: 'bg-green-100 text-green-800',
        draft: 'bg-gray-100 text-gray-800',
    };

    return map[status] ?? 'bg-gray-100 text-gray-800';
};

export const statusLabel = (status) => {
    const map = {
        active: 'Aktif',
        registered: 'Terdaftar',
        checked_in: 'Sudah Check-in',
        cancelled: 'Dibatalkan',
    };

    return map[status] ?? status;
};
