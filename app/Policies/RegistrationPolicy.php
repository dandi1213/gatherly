<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    /**
     * Determine whether the user can view any registrations (for an event).
     */
    public function viewAny(User $user, $event): bool
    {
        // Only organizer/admin of the event can view all registrations
        if ($event instanceof Event) {
            return $user->isAdmin() || $event->organizer_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can view the registration.
     */
    public function view(User $user, Registration $registration): bool
    {
        // Owner of registration or organizer/admin of event
        return $user->id === $registration->user_id
            || $user->isAdmin()
            || $registration->event->organizer_id === $user->id;
    }

    /**
     * Determine whether the user can create registrations.
     */
    public function create(User $user, $event): bool
    {
        // Any authenticated user can register for published events
        if ($event instanceof Event) {
            return $event->status === 'published';
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the registration.
     */
    public function cancel(User $user, Registration $registration): bool
    {
        // Only the attendee who owns the registration can cancel (if before event start)
        return $user->id === $registration->user_id;
    }
}
