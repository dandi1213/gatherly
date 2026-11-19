<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * Daftarkan peserta untuk sebuah tipe tiket secara atomik (PRD 6.2, 8.4).
     *
     * Seluruh validasi + pembuatan registrasi + tiket terjadi dalam satu transaksi.
     * Baris ticket_types dikunci (SELECT ... FOR UPDATE) sehingga dua permintaan
     * bersamaan untuk tipe tiket yang sama akan diserialisasi -> anti-oversell.
     *
     * @throws ValidationException
     */
    public function register(User $user, Event $event, TicketType $ticketType, array $data = []): Registration
    {
        return DB::transaction(function () use ($user, $event, $ticketType, $data) {
            // Kunci baris tipe tiket untuk mencegah race condition kuota.
            $lockedTicketType = TicketType::whereKey($ticketType->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Pastikan tipe tiket memang milik event ini.
            if ($lockedTicketType->event_id !== $event->id) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'Tipe tiket tidak valid untuk event ini.',
                ]);
            }

            // Event harus Published dan registrasi dibuka.
            if ($event->status !== EventStatus::Published || $event->is_disabled) {
                throw ValidationException::withMessages([
                    'event' => 'Event ini belum dibuka untuk registrasi.',
                ]);
            }

            if (! $event->isRegistrationOpen()) {
                throw ValidationException::withMessages([
                    'event' => 'Periode registrasi event ini sudah ditutup atau belum dibuka.',
                ]);
            }

            // Tipe tiket aktif dan dalam periode ketersediaan.
            if (! $lockedTicketType->isCurrentlyAvailable()) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'Tipe tiket ini sedang tidak tersedia.',
                ]);
            }

            // Satu user maksimal satu registrasi aktif per event (PRD 8.2).
            $existing = Registration::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->where('status', RegistrationStatus::Registered->value)
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages([
                    'event' => 'Anda sudah terdaftar pada event ini.',
                ]);
            }

            // Anti-oversell: hitung tiket terbit (Active + Checked-in) di dalam lock.
            $issued = $lockedTicketType->tickets()
                ->whereIn('status', [TicketStatus::Active->value, TicketStatus::CheckedIn->value])
                ->count();

            if ($issued >= $lockedTicketType->quota) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'Maaf, kuota tipe tiket ini sudah habis.',
                ]);
            }

            try {
                // Buat registrasi.
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'ticket_type_id' => $lockedTicketType->id,
                    'registration_number' => $this->generateRegistrationNumber(),
                    'attendee_name' => $data['attendee_name'] ?? $user->name,
                    'attendee_email' => $data['attendee_email'] ?? $user->email,
                    'attendee_phone' => $data['attendee_phone'] ?? null,
                    'status' => RegistrationStatus::Registered,
                ]);
            } catch (QueryException $exception) {
                // Partial unique index bisa terpicu oleh dua request bersamaan.
                if ($this->isUniqueViolation($exception)) {
                    throw ValidationException::withMessages([
                        'event' => 'Anda sudah terdaftar pada event ini.',
                    ]);
                }

                throw $exception;
            }

            // Terbitkan tepat satu tiket (1 registrasi = 1 tiket).
            $registration->ticket()->create([
                'event_id' => $event->id,
                'ticket_type_id' => $lockedTicketType->id,
                'ticket_code' => $this->generateTicketCode(),
                'status' => TicketStatus::Active,
            ]);

            return $registration->load('ticket', 'event', 'ticketType');
        });
    }

    /**
     * Pembatalan oleh peserta (PRD 7.4 - Should): registrasi dan tiket menjadi
     * Cancelled sehingga kuota otomatis kembali.
     *
     * @throws ValidationException
     */
    public function cancel(Registration $registration): Registration
    {
        return DB::transaction(function () use ($registration) {
            $locked = Registration::whereKey($registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isRegistered()) {
                throw ValidationException::withMessages([
                    'registration' => 'Registrasi ini sudah dibatalkan.',
                ]);
            }

            $event = $locked->event;

            if (! $event->isPublished() || $event->is_disabled) {
                throw ValidationException::withMessages([
                    'registration' => 'Registrasi tidak dapat dibatalkan karena event sudah tidak aktif.',
                ]);
            }

            if (now()->gte($event->start_at)) {
                throw ValidationException::withMessages([
                    'registration' => 'Registrasi tidak dapat dibatalkan setelah event dimulai.',
                ]);
            }

            $locked->update([
                'status' => RegistrationStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $locked->ticket()
                ->where('status', TicketStatus::Active->value)
                ->update(['status' => TicketStatus::Cancelled->value]);

            return $locked->fresh(['ticket', 'event']);
        });
    }

    /**
     * Nomor registrasi berformat GTH-{YYMM}-{6 karakter acak} (PRD 7.4).
     */
    private function generateRegistrationNumber(): string
    {
        do {
            $number = sprintf(
                'GTH-%s-%s',
                now()->format('ym'),
                strtoupper(substr(bin2hex(random_bytes(4)), 0, 6))
            );
        } while (Registration::where('registration_number', $number)->exists());

        return $number;
    }

    /**
     * Token QR kriptografis acak (>= 32 karakter). Tidak memuat data pribadi (PRD 7.5).
     */
    private function generateTicketCode(): string
    {
        return bin2hex(random_bytes(32)); // 64 karakter hex
    }

    /**
     * Deteksi pelanggaran unique index pada PostgreSQL maupun SQLite.
     */
    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23505', '23000'], true)
            || str_contains($exception->getMessage(), 'registrations_event_user_active_unique')
            || str_contains(strtolower($exception->getMessage()), 'unique constraint');
    }
}
