import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

export default function Staff({ event, organizer, staff }) {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('organizer.events.staff.store', event.id), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const remove = (member) => {
        if (!confirm(`Hapus ${member.name} dari staff event ini?`)) {
            return;
        }

        router.delete(
            route('organizer.events.staff.destroy', [event.id, member.id]),
            { preserveScroll: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Staff Event
                        </h2>
                        <p className="text-sm text-gray-500">{event.title}</p>
                    </div>
                    <div className="flex gap-2">
                        <Link
                            href={route('organizer.events.show', event.id)}
                            className="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow hover:bg-gray-50"
                        >
                            Kembali ke Event
                        </Link>
                        {event.status === 'published' && (
                            <Link
                                href={route('checkin.show', event.id)}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                            >
                                Buka Check-in
                            </Link>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`Staff - ${event.title}`} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="rounded-md bg-green-50 p-3 text-sm text-green-700">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">
                            {flash.error}
                        </div>
                    )}

                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                        Staff dapat membuka halaman check-in dan memindai QR tiket
                        untuk event ini. Staff harus memiliki akun Gatherly terlebih
                        dahulu.
                    </div>

                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">
                            Tambah Staff
                        </h3>
                        <form onSubmit={submit} className="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div className="flex-1">
                                <InputLabel htmlFor="email" value="Email akun terdaftar" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="mt-1 block w-full"
                                    placeholder="staff@example.com"
                                    required
                                />
                                <InputError message={errors.email} className="mt-2" />
                            </div>
                            <PrimaryButton disabled={processing}>
                                {processing ? 'Menambahkan...' : 'Tambah Staff'}
                            </PrimaryButton>
                        </form>
                    </div>

                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">
                            Daftar Staff
                        </h3>

                        <div className="mb-3 flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 p-4">
                            <div>
                                <p className="font-medium text-gray-900">
                                    {organizer?.name}{' '}
                                    <span className="ms-2 rounded bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        Organizer
                                    </span>
                                </p>
                                <p className="text-sm text-gray-500">{organizer?.email}</p>
                            </div>
                            <span className="text-xs text-gray-500">Akses penuh</span>
                        </div>

                        {staff.length === 0 ? (
                            <p className="text-sm text-gray-500">
                                Belum ada staff yang ditugaskan. Tambahkan lewat email di atas.
                            </p>
                        ) : (
                            <ul className="divide-y divide-gray-200">
                                {staff.map((member) => (
                                    <li
                                        key={member.id}
                                        className="flex flex-wrap items-center justify-between gap-3 py-3"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900">{member.name}</p>
                                            <p className="text-sm text-gray-500">{member.email}</p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => remove(member)}
                                            className="text-sm font-medium text-red-600 hover:text-red-800"
                                        >
                                            Hapus
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
