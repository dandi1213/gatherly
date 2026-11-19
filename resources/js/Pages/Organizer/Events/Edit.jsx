import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import EventForm from './Partials/EventForm';

export default function Edit({ event, categories }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <div className="mb-2">
                        <Link
                            href={route('organizer.events.show', event.id)}
                            className="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors"
                        >
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Detail Event
                        </Link>
                    </div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Edit Event: {event.title}
                    </h2>
                </div>
            }
        >
            <Head title={`Edit ${event.title}`} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <EventForm
                            event={event}
                            categories={categories}
                            submitRoute={route('organizer.events.update', event.id)}
                            method="patch"
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
