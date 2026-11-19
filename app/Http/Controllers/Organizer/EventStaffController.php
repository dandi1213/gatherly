<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventStaffRequest;
use App\Models\Event;
use App\Models\EventStaff;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EventStaffController extends Controller
{
    /**
     * Daftar staff sebuah event (organizer pemilik / admin).
     */
    public function index(Request $request, Event $event): Response
    {
        $this->authorize('viewAny', [EventStaff::class, $event]);

        return Inertia::render('Organizer/Events/Staff', [
            'event' => $event->only([
                'id', 'title', 'slug', 'status', 'start_at', 'end_at',
            ]),
            'organizer' => $event->organizer()->first(['id', 'name', 'email']),
            'staff' => $event->staff()
                ->withPivot('id')
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email'])
                ->map(fn (User $user) => [
                    'id' => $user->pivot->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
        ]);
    }

    /**
     * Tambah staff lewat email akun yang sudah terdaftar (D4, PRD 6.5).
     */
    public function store(StoreEventStaffRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('create', [EventStaff::class, $event]);

        $email = strtolower(trim($request->validated('email')));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Pengguna belum terdaftar. Minta pengguna mendaftar akun terlebih dahulu.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun pengguna tersebut sedang nonaktif.',
            ]);
        }

        if ($user->id === $event->organizer_id) {
            throw ValidationException::withMessages([
                'email' => 'Organizer sudah otomatis memiliki akses ke event ini.',
            ]);
        }

        if ($event->staff()->whereKey($user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Pengguna sudah menjadi staff pada event ini.',
            ]);
        }

        try {
            DB::transaction(function () use ($event, $user, $request) {
                $event->staff()->attach($user->id, [
                    'assigned_by' => $request->user()->id,
                ]);
            });
        } catch (QueryException $exception) {
            // Unique (event_id, user_id) menangkap dua request bersamaan.
            if ($this->isUniqueViolation($exception)) {
                throw ValidationException::withMessages([
                    'email' => 'Pengguna sudah menjadi staff pada event ini.',
                ]);
            }

            throw $exception;
        }

        return back()->with('success', "{$user->name} berhasil ditambahkan sebagai staff.");
    }

    /**
     * Hapus penugasan staff.
     */
    public function destroy(Request $request, Event $event, EventStaff $staff): RedirectResponse
    {
        abort_unless($staff->event_id === $event->id, 404);

        $this->authorize('delete', $staff);

        $name = $staff->user?->name ?? 'Staff';

        $staff->delete();

        return back()->with('success', "{$name} berhasil dihapus dari staff event.");
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23505', '23000'], true)
            || str_contains(strtolower($exception->getMessage()), 'unique constraint');
    }
}
