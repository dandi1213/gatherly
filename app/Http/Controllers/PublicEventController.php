<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicEventController extends Controller
{
    /**
     * Halaman publik: daftar event Published dengan pencarian & filter (EV-12).
     */
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => $request->query('category'),
            'format' => $request->query('format'),
            'period' => in_array($request->query('period'), ['upcoming', 'past', 'all'], true)
                ? $request->query('period')
                : 'upcoming',
        ];

        $events = Event::query()
            ->published()
            ->with(['category:id,name', 'organizer:id,name,org_name'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $term = '%'.mb_strtolower($filters['q']).'%';

                $query->where(function ($query) use ($term) {
                    $query->whereRaw('LOWER(title) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(COALESCE(location, \'\')) LIKE ?', [$term]);
                });
            })
            ->when($filters['category'], fn ($query, $category) => $query->where('category_id', $category))
            ->when(
                in_array($filters['format'], ['online', 'offline', 'hybrid'], true),
                fn ($query) => $query->where('format', $filters['format'])
            )
            ->when(
                $filters['period'] === 'past',
                fn ($query) => $query->where('end_at', '<', now())->latest('start_at'),
                fn ($query) => $filters['period'] === 'upcoming'
                    ? $query->where('end_at', '>=', now())->orderBy('start_at')
                    : $query->latest('start_at')
            )
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Public/Events/Index', [
            'events' => $events,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    /**
     * Detail event publik. Draft/Cancelled hanya bisa dipreview oleh pengelolanya.
     */
    public function show(Request $request, string $slug): Response
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        $isPreview = false;

        if (! $event->isPublished() || $event->is_disabled) {
            if (! $user || ! $event->isManagedBy($user)) {
                abort(404);
            }

            $isPreview = true;
        }

        $event->load([
            'category:id,name',
            'organizer:id,name,org_name,org_description,org_logo',
        ]);

        $ticketTypes = $event->ticketTypes()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $myRegistration = null;

        if ($user) {
            $myRegistration = Registration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->where('status', RegistrationStatus::Registered->value)
                ->with('ticket:id,registration_id,status')
                ->first();
        }

        return Inertia::render('Public/Events/Show', [
            'event' => $event,
            'ticketTypes' => $ticketTypes,
            'myRegistration' => $myRegistration ? [
                'id' => $myRegistration->id,
                'registration_number' => $myRegistration->registration_number,
                'ticket' => $myRegistration->ticket,
            ] : null,
            'registrationOpen' => $event->isRegistrationOpen(),
            'isPreview' => $isPreview,
        ]);
    }
}
