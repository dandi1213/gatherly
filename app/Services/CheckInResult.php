<?php

namespace App\Services;

use App\Models\Ticket;

/**
 * Hasil validasi check-in. Dipakai baik oleh scan QR (JSON) maupun
 * manual check-in agar pesan dan aturan tetap identik (PRD 8.4).
 */
final class CheckInResult
{
    /**
     * @param  array<string, mixed>|null  $ticket
     * @param  array<string, int|float>  $stats
     */
    private function __construct(
        public readonly bool $ok,
        public readonly string $reason,
        public readonly string $message,
        public readonly ?array $ticket,
        public readonly array $stats,
    ) {}

    /**
     * @param  array<string, int|float>  $stats
     */
    public static function success(Ticket $ticket, array $stats): self
    {
        return new self(
            true,
            'ok',
            'Check-in berhasil.',
            self::ticketPayload($ticket),
            $stats,
        );
    }

    /**
     * @param  array<string, int|float>  $stats
     */
    public static function failure(string $reason, string $message, array $stats, ?Ticket $ticket = null): self
    {
        return new self(
            false,
            $reason,
            $message,
            $ticket ? self::ticketPayload($ticket) : null,
            $stats,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'reason' => $this->reason,
            'message' => $this->message,
            'ticket' => $this->ticket,
            'stats' => $this->stats,
        ];
    }

    /**
     * Data tiket yang aman ditampilkan ke petugas pada event yang benar.
     *
     * @return array<string, mixed>
     */
    private static function ticketPayload(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'attendee_name' => $ticket->registration?->attendee_name,
            'attendee_email' => $ticket->registration?->attendee_email,
            'registration_number' => $ticket->registration?->registration_number,
            'ticket_type' => $ticket->ticketType?->name,
            'status' => $ticket->status->value,
            'checked_in_at' => $ticket->checked_in_at?->toIso8601String(),
            'checked_in_by' => $ticket->checkedInBy?->name,
        ];
    }
}
