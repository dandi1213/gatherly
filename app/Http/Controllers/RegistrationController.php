<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Http\Requests\StoreRegistrationRequest;
use App\Mail\TicketIssuedMail;
use App\Models\Event;
use App\Models\Registration;
use App\Models\TicketType;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $registrationService) {}

    /**
     * Form registrasi (terisi dari profil). User yang belum login akan
     * diarahkan middleware auth ke login lalu kembali ke halaman ini.
     */
    public function create(Request $request, Event $event): Response|RedirectResponse
    {
        $this->ensureEventIsPublic($event);

        $existing = $this->activeRegistration($request, $event);

        if ($existing) {
            return redirect()
                ->route('tickets.show', $existing->ticket)
                ->with('success', 'Anda sudah terdaftar pada event ini.');
        }

        if (! $event->isRegistrationOpen()) {
            return redirect()
                ->route('events.show', $event->slug)
                ->with('error', 'Periode registrasi event ini sudah ditutup atau belum dibuka.');
        }

        $ticketTypes = $event->ticketTypes()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->filter(fn (TicketType $ticketType) => $ticketType->isCurrentlyAvailable() && $ticketType->remaining_quota > 0)
            ->values();

        if ($ticketTypes->isEmpty()) {
            return redirect()
                ->route('events.show', $event->slug)
                ->with('error', 'Maaf, belum ada tipe tiket yang tersedia saat ini.');
        }

        $selectedTicketTypeId = (int) $request->query('ticket_type');
        $selectedTicketTypeId = $ticketTypes->contains('id', $selectedTicketTypeId)
            ? $selectedTicketTypeId
            : $ticketTypes->first()->id;

        $user = $request->user();

        return Inertia::render('Public/Events/Register', [
            'event' => $event->load('category:id,name'),
            'ticketTypes' => $ticketTypes,
            'selectedTicketTypeId' => $selectedTicketTypeId,
            'defaults' => [
                'attendee_name' => $user->name,
                'attendee_email' => $user->email,
                'attendee_phone' => null,
            ],
        ]);
    }

    /**
     * Proses registrasi atomik: registrasi + tiket + email setelah commit.
     */
    public function store(StoreRegistrationRequest $request, Event $event): RedirectResponse
    {
        // Status event divalidasi di service agar pengguna mendapat pesan yang jelas
        // (mis. event Draft/Cancelled atau periode registrasi sudah lewat).
        $ticketType = TicketType::findOrFail($request->validated('ticket_type_id'));

        $registration = $this->registrationService->register(
            $request->user(),
            $event,
            $ticketType,
            $request->validated(),
        );

        // Email dikirim setelah transaksi commit, melalui queue (PRD 8.4).
        // Kegagalan email tidak boleh menggagalkan registrasi yang sudah tersimpan.
        try {
            Mail::to($registration->attendee_email)->queue(new TicketIssuedMail($registration));
        } catch (\Throwable $exception) {
            Log::error('Gagal mengantrekan email tiket.', [
                'registration_id' => $registration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('tickets.show', $registration->ticket)
            ->with('success', 'Registrasi berhasil! Tiket Anda sudah terbit.');
    }

    /**
     * Pembatalan registrasi oleh peserta (Should, PRD 7.4).
     */
    public function cancel(Registration $registration): RedirectResponse
    {
        $this->authorize('cancel', $registration);

        $this->registrationService->cancel($registration);

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Registrasi dibatalkan. Kuota tiket telah dikembalikan.');
    }

    private function activeRegistration(Request $request, Event $event): ?Registration
    {
        return Registration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->where('status', RegistrationStatus::Registered->value)
            ->with('ticket:id,registration_id,status')
            ->first();
    }

    private function ensureEventIsPublic(Event $event): void
    {
        abort_unless($event->isPublished() && ! $event->is_disabled, 404);
    }
}
