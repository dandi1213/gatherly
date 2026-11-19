<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventService
{
    /**
     * Validasi kelengkapan sebelum publish (PRD 6.1 langkah 6).
     * Mengembalikan daftar kekurangan; kosong berarti lolos.
     */
    public function publishValidationErrors(Event $event): array
    {
        $errors = [];

        if (blank($event->title)) {
            $errors[] = 'Judul event wajib diisi.';
        }

        if (blank($event->description)) {
            $errors[] = 'Deskripsi event wajib diisi.';
        }

        if (blank($event->category_id)) {
            $errors[] = 'Kategori wajib dipilih.';
        }

        if (! $event->start_at || ! $event->end_at) {
            $errors[] = 'Tanggal mulai dan selesai wajib diisi.';
        }

        if ($event->format === 'offline' && blank($event->location)) {
            $errors[] = 'Lokasi wajib diisi untuk event offline.';
        }

        if ($event->format === 'online' && blank($event->online_url)) {
            $errors[] = 'URL online wajib diisi untuk event online.';
        }

        $activeTicketTypes = $event->ticketTypes()->where('is_active', true)->get();

        if ($activeTicketTypes->isEmpty()) {
            $errors[] = 'Minimal satu tipe tiket aktif dengan kuota lebih dari 0.';
        } elseif ($activeTicketTypes->sum('quota') <= 0) {
            $errors[] = 'Total kuota tiket harus lebih dari 0.';
        }

        return $errors;
    }

    /**
     * Publish event (Draft -> Published).
     *
     * @throws ValidationException
     */
    public function publish(Event $event): Event
    {
        if (! $event->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Hanya event berstatus Draft yang dapat dipublikasikan.',
            ]);
        }

        $errors = $this->publishValidationErrors($event);

        if (! empty($errors)) {
            throw ValidationException::withMessages([
                'publish' => $errors,
            ]);
        }

        $event->update([
            'status' => EventStatus::Published,
            'published_at' => now(),
        ]);

        return $event->fresh();
    }

    /**
     * Export data peserta ke CSV (PRD 5.3).
     */
    public function exportCsv(Event $event, string $q = ''): Response
    {
        $query = $event->registrations()
            ->with(['user', 'ticketType'])
            ->when($q, fn ($query) => $query->where(function ($qry) {
                $qry->where('attendee_name', 'like', '%'.$q.'%')
                    ->orWhere('attendee_email', 'like', '%'.$q.'%')
                    ->orWhere('registrations.registration_number', 'like', '%'.$q.'%');
            }))
            ->orderBy('registrations.created_at', 'desc');

        $registrations = $query->get();

        $callback = function () use ($registrations) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'Nomor Registrasi',
                'Nama Peserta',
                'Email',
                'Tipe Tiket',
                'Peran',
                'Status Registrasi',
                'Status Tiket',
                'Waktu Daftar',
                'Waktu Check-in',
            ]);

            // Data
            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->registration_number,
                    $reg->attendee_name,
                    $reg->attendee_email,
                    $reg->ticketType?->name ?? '—',
                    $reg->user?->role ?? '—',
                    $reg->status,
                    $reg->ticket?->status ?? '—',
                    $reg->registered_at?->toString('Y-m-d H:i:s') ?? '—',
                    $reg->checked_in_at?->toString('Y-m-d H:i:s') ?? '—',
                ]);
            }

            fclose($file);
        };

        $filename = 'peserta-'.str_replace(['/', ' ', '+'], '-', $event->slug).'-'.now()->format('Ymd').'.csv';

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Cancel event (Published -> Cancelled) + batalkan semua tiket Active (PRD 6.4).
     */
    public function cancel(Event $event): Event
    {
        if ($event->isCancelled()) {
            return $event;
        }

        return DB::transaction(function () use ($event) {
            $event->update([
                'status' => EventStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            // Batalkan semua tiket Active dan registrasi aktif
            $event->tickets()
                ->where('status', TicketStatus::Active->value)
                ->update(['status' => TicketStatus::Cancelled->value]);

            $event->registrations()
                ->where('status', 'registered')
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            return $event->fresh();
        });
    }
}
