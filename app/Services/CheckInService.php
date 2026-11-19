<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;

class CheckInService
{
    /**
     * Validasi + check-in tiket secara atomik (PRD 6.3, 8.4).
     *
     * Status hanya diubah lewat UPDATE ... WHERE status = 'active'. Jika
     * affected rows = 0, tiket sudah dipakai/dibatalkan oleh request lain,
     * sehingga check-in ganda tidak mungkin terjadi (R3).
     */
    public function checkIn(Event $event, string $ticketCode, User $staff): CheckInResult
    {
        $stats = $this->stats($event);
        $ticketCode = trim($ticketCode);

        if ($ticketCode === '') {
            return CheckInResult::failure(
                'not_found',
                'Token tiket tidak valid.',
                $stats,
            );
        }

        $ticket = Ticket::query()
            ->where('ticket_code', $ticketCode)
            ->with(['registration', 'ticketType', 'checkedInBy'])
            ->first();

        // Token tidak ditemukan: jangan bocorkan data apa pun (PRD 9.4).
        if (! $ticket) {
            return CheckInResult::failure(
                'not_found',
                'Tiket tidak ditemukan. Periksa kembali QR yang dipindai.',
                $stats,
            );
        }

        if ($ticket->event_id !== $event->id) {
            return CheckInResult::failure(
                'wrong_event',
                'Tiket ini bukan milik event ini.',
                $stats,
            );
        }

        if ($ticket->isCancelled()) {
            return CheckInResult::failure(
                'cancelled',
                'Tiket sudah dibatalkan dan tidak dapat digunakan.',
                $stats,
                $ticket,
            );
        }

        if ($ticket->isCheckedIn()) {
            return CheckInResult::failure(
                'already_checked_in',
                $this->alreadyCheckedInMessage($ticket),
                $stats,
                $ticket,
            );
        }

        if (! $event->isPublished() || $event->is_disabled) {
            return CheckInResult::failure(
                'event_inactive',
                'Event belum dipublikasikan atau sedang dinonaktifkan.',
                $stats,
                $ticket,
            );
        }

        if (! $event->isCheckInWindowOpen()) {
            return CheckInResult::failure(
                'outside_window',
                $this->outsideWindowMessage($event),
                $stats,
                $ticket,
            );
        }

        // Operasi atomik: tidak ada pola baca-lalu-tulis untuk status tiket.
        $affected = Ticket::query()
            ->whereKey($ticket->id)
            ->where('status', TicketStatus::Active->value)
            ->update([
                'status' => TicketStatus::CheckedIn->value,
                'checked_in_at' => now(),
                'checked_in_by' => $staff->id,
            ]);

        if ($affected === 0) {
            $fresh = $ticket->fresh(['registration', 'ticketType', 'checkedInBy']);

            if ($fresh->isCheckedIn()) {
                return CheckInResult::failure(
                    'already_checked_in',
                    $this->alreadyCheckedInMessage($fresh),
                    $this->stats($event),
                    $fresh,
                );
            }

            if ($fresh->isCancelled()) {
                return CheckInResult::failure(
                    'cancelled',
                    'Tiket sudah dibatalkan dan tidak dapat digunakan.',
                    $this->stats($event),
                    $fresh,
                );
            }

            return CheckInResult::failure(
                'invalid',
                'Tiket tidak dapat di-check-in.',
                $this->stats($event),
                $fresh,
            );
        }

        return CheckInResult::success(
            $ticket->fresh(['registration', 'ticketType', 'checkedInBy']),
            $this->stats($event),
        );
    }

    /**
     * Statistik ringan checked-in / total (PRD 7.6).
     *
     * @return array<string, int|float>
     */
    public function stats(Event $event): array
    {
        $counts = $event->tickets()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $active = (int) ($counts[TicketStatus::Active->value] ?? 0);
        $checkedIn = (int) ($counts[TicketStatus::CheckedIn->value] ?? 0);
        $cancelled = (int) ($counts[TicketStatus::Cancelled->value] ?? 0);

        // Attendance rate = checked-in / (active + checked-in) (PRD 7.7).
        $eligible = $active + $checkedIn;

        return [
            'checked_in' => $checkedIn,
            'active' => $active,
            'cancelled' => $cancelled,
            'total' => $eligible,
            'attendance_rate' => $eligible > 0
                ? round($checkedIn / $eligible * 100, 1)
                : 0.0,
        ];
    }

    private function alreadyCheckedInMessage(Ticket $ticket): string
    {
        $time = $ticket->checked_in_at
            ? $ticket->checked_in_at->locale('id')->translatedFormat('d M Y H:i')
            : 'sebelumnya';

        $by = $ticket->checkedInBy?->name ?? 'petugas lain';

        return "Tiket sudah dipakai pada {$time} oleh {$by}.";
    }

    private function outsideWindowMessage(Event $event): string
    {
        $start = $event->checkInWindowStart()
            ->locale('id')
            ->translatedFormat('d M Y H:i');

        $end = $event->end_at
            ->locale('id')
            ->translatedFormat('d M Y H:i');

        return "Di luar jendela check-in. Check-in dibuka mulai {$start} sampai {$end}.";
    }
}
