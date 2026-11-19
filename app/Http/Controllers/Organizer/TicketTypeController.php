<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketTypeRequest;
use App\Http\Requests\UpdateTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    public function store(StoreTicketTypeRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        $data['event_id'] = $event->id;
        $data['is_active'] = $request->boolean('is_active', true);

        $event->ticketTypes()->create($data);

        return back()->with('success', 'Tipe tiket berhasil ditambahkan.');
    }

    public function update(UpdateTicketTypeRequest $request, Event $event, TicketType $ticketType): RedirectResponse
    {
        $this->ensureBelongsToEvent($event, $ticketType);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $ticketType->update($data);

        return back()->with('success', 'Tipe tiket berhasil diperbarui.');
    }

    public function destroy(Request $request, Event $event, TicketType $ticketType): RedirectResponse
    {
        abort_unless($event->isManagedBy($request->user()), 403);
        $this->ensureBelongsToEvent($event, $ticketType);

        // Tipe tiket yang sudah punya tiket tidak boleh dihapus, hanya dinonaktifkan (PRD 7.2)
        if ($ticketType->tickets()->exists()) {
            return back()->with('error', 'Tipe tiket sudah memiliki tiket terbit. Nonaktifkan saja, jangan dihapus.');
        }

        $ticketType->delete();

        return back()->with('success', 'Tipe tiket berhasil dihapus.');
    }

    private function ensureBelongsToEvent(Event $event, TicketType $ticketType): void
    {
        abort_unless($ticketType->event_id === $event->id, 404);
    }
}
