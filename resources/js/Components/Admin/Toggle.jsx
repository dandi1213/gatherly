import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

function roleBadge(role) {
    const map = {
        attendee: 'bg-gray-100 text-gray-700',
        organizer: 'bg-green-100 text-green-800',
        admin: 'bg-indigo-100 text-indigo-800',
    };
    return (
        <span
            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                map[role] ?? 'bg-gray-100 text-gray-700'
            }`}
        >
            {role}
        </span>
    );
}

export default function Toggle({ userId, currentStatus, onDone }) {
    const { post } = useForm();

    const handleToggle = () => {
        post(route('admin.users.toggle', userId), {
            onSuccess: () => onDone(currentStatus === 'active'),
            preserveScroll: true,
        });
    };

    return (
        <button
            type="button"
            onClick={handleToggle}
            className={`rounded-md px-3 py-1.5 text-xs font-medium ${
                currentStatus === 'active'
                    ? 'bg-red-100 text-red-800 hover:bg-red-200'
                    : 'bg-green-100 text-green-800 hover:bg-green-200'
            } transition-colors`}
        >
            {currentStatus === 'active' ? 'Nonaktifkan' : 'Aktifkan'}
        </button>
}