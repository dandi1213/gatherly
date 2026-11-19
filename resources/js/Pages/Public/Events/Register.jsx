import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import PublicLayout from '@/Layouts/PublicLayout';
import { formatDateRange } from '@/utils/format';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register({ event, ticketTypes, selectedTicketTypeId, defaults }) {
    const { data, setData, post, processing, errors } = useForm({
        ticket_type_id: selectedTicketTypeId,
        attendee_name: defaults.attendee_name ?? '',
        attendee_email: defaults.attendee_email ?? '',
        attendee_phone: defaults.attendee_phone ?? '',
    });

    const submit = (submitEvent) => {
        submitEvent.preventDefault();
        post(route('events.register.store', { event: event.slug }));
    };

    const selectedTicketType = ticketTypes.find(
        (ticketType) => Number(ticketType.id) === Number(data.ticket_type_id),
    );

    return (
        <PublicLayout>
            <Head title={`Daftar · ${event.title}`} />

            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <Link
                    href={route('events.show', event.slug)}
                    className="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                >
                    ← Kembali ke detail event
                </Link>

                <div className="mt-4 grid gap-8 lg:grid-cols-[1fr_340px]">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                            Form Registrasi
                        </h1>
                        <p className="mt-2 text-sm text-gray-500">
                            Data di bawah terisi otomatis dari profil Anda. Periksa kembali sebelum mendaftar.
                        </p>

                        {errors.event && (
                            <div className="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {errors.event}
                            </div>
                        )}

                        <form onSubmit={submit} className="mt-6 space-y-6">
                            <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 className="text-base font-semibold text-gray-900">Pilih Tipe Tiket</h2>

                                <div className="mt-4 space-y-3">
                                    {ticketTypes.map((ticketType) => (
                                        <label
                                            key={ticketType.id}
                                            className={`flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition ${
                                                Number(data.ticket_type_id) === Number(ticketType.id)
                                                    ? 'border-indigo-500 bg-indigo-50/60 ring-1 ring-indigo-500'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                name="ticket_type_id"
                                                value={ticketType.id}
                                                checked={Number(data.ticket_type_id) === Number(ticketType.id)}
                                                onChange={() => setData('ticket_type_id', ticketType.id)}
                                                className="mt-1 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span className="flex-1">
                                                <span className="flex flex-wrap items-center justify-between gap-2">
                                                    <span className="font-semibold text-gray-900">{ticketType.name}</span>
                                                    <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                                        Sisa {ticketType.remaining_quota} tiket
                                                    </span>
                                                </span>
                                                {ticketType.description && (
                                                    <span className="mt-1 block text-sm text-gray-500">
                                                        {ticketType.description}
                                                    </span>
                                                )}
                                            </span>
                                        </label>
                                    ))}
                                </div>

                                <InputError message={errors.ticket_type_id} className="mt-3" />
                            </div>

                            <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 className="text-base font-semibold text-gray-900">Data Peserta</h2>

                                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div className="sm:col-span-2">
                                        <InputLabel htmlFor="attendee_name" value="Nama lengkap" />
                                        <TextInput
                                            id="attendee_name"
                                            value={data.attendee_name}
                                            onChange={(inputEvent) => setData('attendee_name', inputEvent.target.value)}
                                            className="mt-1 block w-full"
                                            required
                                            autoComplete="name"
                                        />
                                        <InputError message={errors.attendee_name} className="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="attendee_email" value="Email" />
                                        <TextInput
                                            id="attendee_email"
                                            type="email"
                                            value={data.attendee_email}
                                            onChange={(inputEvent) => setData('attendee_email', inputEvent.target.value)}
                                            className="mt-1 block w-full"
                                            required
                                            autoComplete="email"
                                        />
                                        <InputError message={errors.attendee_email} className="mt-2" />
                                        <p className="mt-1 text-xs text-gray-500">
                                            Tiket dan email konfirmasi dikirim ke alamat ini.
                                        </p>
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="attendee_phone" value="Nomor telepon (opsional)" />
                                        <TextInput
                                            id="attendee_phone"
                                            value={data.attendee_phone ?? ''}
                                            onChange={(inputEvent) => setData('attendee_phone', inputEvent.target.value)}
                                            className="mt-1 block w-full"
                                            autoComplete="tel"
                                        />
                                        <InputError message={errors.attendee_phone} className="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div className="flex items-center justify-between gap-4">
                                <Link
                                    href={route('events.show', event.slug)}
                                    className="text-sm font-medium text-gray-500 hover:text-gray-800"
                                >
                                    Batal
                                </Link>
                                <PrimaryButton disabled={processing}>
                                    {processing ? 'Memproses...' : 'Konfirmasi Registrasi'}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    <aside className="lg:pt-14">
                        <div className="sticky top-24 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Ringkasan Event
                            </h2>
                            <h3 className="mt-2 text-lg font-semibold text-gray-900">{event.title}</h3>
                            <dl className="mt-4 space-y-3 text-sm">
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-gray-400">Waktu</dt>
                                    <dd className="mt-0.5 text-gray-700">
                                        {formatDateRange(event.start_at, event.end_at)}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-gray-400">Lokasi</dt>
                                    <dd className="mt-0.5 text-gray-700">
                                        {event.format === 'online' ? 'Online' : event.location || 'Lokasi menyusul'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-medium uppercase tracking-wide text-gray-400">Tipe dipilih</dt>
                                    <dd className="mt-0.5 font-semibold text-gray-900">
                                        {selectedTicketType?.name ?? '—'}
                                    </dd>
                                </div>
                            </dl>

                            <p className="mt-4 border-t border-gray-100 pt-4 text-xs leading-5 text-gray-500">
                                Satu akun hanya dapat memiliki satu registrasi aktif per event. Tiket digital
                                ber-QR akan langsung terbit setelah registrasi berhasil.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </PublicLayout>
    );
}
