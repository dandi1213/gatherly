<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Owner of ticket or organizer/admin of event
        return $user->id === $ticket->registration->user_id
            || $user->isAdmin()
            || $ticket->event->organizer_id === $user->id;
    }

    /**
     * Determine whether the user can view any tickets (my tickets).
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own tickets
    }

    /**
     * Determine whether the user can check-in the ticket (scan).
     */
    public function checkIn(User $user, Ticket $ticket): bool
    {
        // Staff assigned to event or organizer/admin can check-in
        $event = $ticket->event;

        if ($user->isAdmin() || $event->organizer_id === $user->id) {
            return true;
        }

        // Check if user is staff for this event
        return $event->staff()->where('user_id', $user->id)->exists();
    }
}
