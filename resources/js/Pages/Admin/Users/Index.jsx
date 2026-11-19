import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ users, stats }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState('');

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Daftar User
                    </h2>
                    <p className="text-sm text-gray-500">{stats.total_users} user terdaftar</p>
                </div>
            }
        >
            <Head title="Daftar User" />

            <div className="py-4 sm:py-6">
                <div className="mx-auto max-w-2xl space-y-4 px-4 sm:px-6 lg:px-8">
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

                    {/* Pencarian */}
                    <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 className="text-base font-semibold text-gray-900 mb-3">Cari User</h3>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                // Redirect ke halaman user dengan filter
                                router.get(route('admin.users.index'), { q: search });
                            }}
                        >
                            <div className="flex gap-2">
                                <InputLabel htmlFor="search" value="Cari nama/email" />
                                <TextInput
                                    id="search"
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Nama atau email"
                                />
                                <button
                                    type="submit"
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                                >
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>

                    {users?.data.length === 0 && (
                        <div className="rounded-lg bg-white p-8 text-center shadow sm:rounded-lg">
                            <p className="font-medium text-gray-800">
                                Tidak ada user yang cocok.
                            </p>
                            <p className="mt-1 text-sm text-gray-500">
                                Coba filter peran atau buat user baru.
                            </p>
                        </div>
                    )}

                    {/* Tabel daftar user */}
                    {users?.data?.length > 0 && (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Peran</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Aktif</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {users.data.map((user) => (
                                        <tr key={user.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 text-sm font-medium text-gray-900">
                                                {user.name}
                                            </td>
                                            <td className="px-6 py-4 text-xs text-gray-500">
                                                {user.email}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {roleBadge(user.role ?? '-')}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                {user.is_active
                                                    ? <span className="text-green-600">Aktif</span>
                                                    : <span className="text-red-600">Nonaktif</span>}
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm">
                                                <button
                                                    onClick={() =>
                                                        router.put(
                                                            route('admin.users.toggle', user.id),
                                                            {},
                                                            { preserveScroll: true }
                                                        )
                                                    }
                                                    className="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200 transition-colors"
                                                >
                                                    {user.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Paginasi */}
                    {users?.links?.length > 0 && (
                        <div className="mt-4 flex flex-wrap gap-1">
                            {users.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url ?? '#'}
                                    preserveScroll
                                    className={`rounded px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}