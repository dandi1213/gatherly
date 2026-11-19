<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\CheckInService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckInController extends Controller
{
    public function __construct(private readonly CheckInService $checkInService) {}

    /**
     * Daftar event yang bisa di-check-in oleh user (organizer pemilik atau
     * staff yang ditugaskan).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $events = Event::query()
            ->published()
            ->where(function (Builder $query) use ($user) {
                $query->where('organizer_id', $user->id)
                    ->orWhereHas('staff', fn (Builder $staff) => $staff->whereKey($user->id));
            })
            ->with('category')
            ->withCount([
                'tickets as checked_in_count' => fn (Builder $query) => $query
                    ->where('status', TicketStatus::CheckedIn->value),
                'tickets as active_count' => fn (Builder $query) => $query
                    ->where('status', TicketStatus::Active->value),
            ])
            ->orderBy('start_at')
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'start_at' => $event->start_at,
                'end_at' => $event->end_at,
                'location' => $event->location,
                'format' => $event->format,
                'category' => $event->category?->name,
                'is_window_open' => $event->isCheckInWindowOpen(),
                'checked_in' => (int) $event->checked_in_count,
                'total' => (int) $event->active_count + (int) $event->checked_in_count,
                'is_staff' => $event->organizer_id !== $user->id,
            ]);

        return Inertia::render('CheckIn/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Halaman check-in mobile-first (scan + pencarian manual).
     */
    public function show(Request $request, Event $event): Response
    {
        $this->authorize('checkIn', $event);

        return $this->renderPage($event, '');
    }

    /**
     * Pencarian manual peserta berdasarkan nama/email/nomor registrasi.
     */
    public function search(Request $request, Event $event): Response
    {
        $this->authorize('checkIn', $event);

        $search = trim((string) $request->query('q', ''));

        return $this->renderPage($event, $search);
    }

    /**
     * Endpoint JSON untuk scanner QR (satu-satunya endpoint JSON, PRD 9.1).
     */
    public function scan(Request $request, Event $event): JsonResponse
    {
        $this->authorize('checkIn', $event);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ], [
            'code.required' => 'Token tiket tidak valid.',
        ]);

        $result = $this->checkInService->checkIn(
            $event,
            $validated['code'],
            $request->user(),
        );

        return response()->json($result->toArray());
    }

    /**
     * Manual check-in dari hasil pencarian. Memanggil service yang sama
     * dengan scan agar aturannya identik (PRD 8.4).
     */
    public function manual(Request $request, Event $event, Ticket $ticket): RedirectResponse
    {
        $this->authorize('checkIn', $event);

        abort_unless($ticket->event_id === $event->id, 404);

        $result = $this->checkInService->checkIn(
            $event,
            $ticket->ticket_code,
            $request->user(),
        );

        if (! $result->ok) {
            return back()->with('error', $result->message);
        }

        $name = $result->ticket['attendee_name'] ?? 'Peserta';

        return back()->with('success', "Check-in berhasil: {$name}.");
    }

    private function renderPage(Event $event, string $search): Response
    {
        $results = null;

        if ($search !== '') {
            $like = '%'.$search.'%';

            $results = Ticket::query()
                ->where('event_id', $event->id)
                ->whereHas('registration', function (Builder $query) {
                    $query->where('status', RegistrationStatus::Registered->value);
                })
                ->whereHas('registration', function (Builder $query) use ($like) {
                    $query->where('attendee_name', 'like', $like)
                        ->orWhere('attendee_email', 'like', $like)
                        ->orWhere('registration_number', 'like', $like);
                })
                ->with(['registration', 'ticketType', 'checkedInBy'])
                ->orderBy('id')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Ticket $ticket) => [
                    'id' => $ticket->id,
                    'attendee_name' => $ticket->registration?->attendee_name,
                    'attendee_email' => $ticket->registration?->attendee_email,
                    'registration_number' => $ticket->registration?->registration_number,
                    'ticket_type' => $ticket->ticketType?->name,
                    'status' => $ticket->status->value,
                    'status_label' => $ticket->status->label(),
                    'checked_in_at' => $ticket->checked_in_at,
                    'checked_in_by' => $ticket->checkedInBy?->name,
                ]);
        }

        return Inertia::render('CheckIn/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'start_at' => $event->start_at,
                'end_at' => $event->end_at,
                'location' => $event->location,
                'format' => $event->format,
            ],
            'stats' => $this->checkInService->stats($event),
            'window' => [
                'open' => $event->isCheckInWindowOpen(),
                'start' => $event->checkInWindowStart(),
                'end' => $event->end_at,
            ],
            'search' => $search,
            'results' => $results,
        ]);
    }
}
