<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCodeService) {}

    /**
     * My Tickets: daftar registrasi + tiket milik user yang login.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ticket::class);

        $registrations = $request->user()
            ->registrations()
            ->with([
                'event:id,title,slug,start_at,end_at,location,format,poster_path,status',
                'ticketType:id,name',
                'ticket:id,registration_id,status',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Tickets/Index', [
            'registrations' => $registrations,
        ]);
    }

    /**
     * Detail tiket + QR (digenerate saat dibutuhkan dari token, tidak disimpan).
     */
    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'registration',
            'event:id,title,slug,start_at,end_at,location,format,status,is_disabled,organizer_id',
            'ticketType:id,name,description',
        ]);

        $event = $ticket->event;
        $registration = $ticket->registration;

        $canCancel = $registration->isRegistered()
            && $ticket->isActive()
            && $event->isPublished()
            && ! $event->is_disabled
            && now()->lt($event->start_at);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'qrSvg' => $this->qrCodeService->svg($ticket->ticket_code),
            'canCancel' => $canCancel,
        ]);
    }
}
