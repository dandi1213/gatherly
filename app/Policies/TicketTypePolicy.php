<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;

class TicketTypePolicy
{
    /**
     * Determine whether the user can view ticket types of an event.
     */
    public function viewAny(User $user, TicketType $ticketType): bool
    {
        // Public event ticket types can be viewed by anyone
        // Private/Draft only by organizer/admin
        $event = $ticketType->event;

        return $event->status === 'published' || $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can create ticket types.
     */
    public function create(User $user, $event): bool
    {
        // $event can be Event model or event_id
        if ($event instanceof Event) {
            return $this->isOwnerOrAdmin($user, $event);
        }
        // If passed event_id, we need to load the event
        $eventModel = Event::find($event);

        return $eventModel && $this->isOwnerOrAdmin($user, $eventModel);
    }

    /**
     * Determine whether the user can update the ticket type.
     */
    public function update(User $user, TicketType $ticketType): bool
    {
        return $this->isOwnerOrAdmin($user, $ticketType->event);
    }

    /**
     * Determine whether the user can delete the ticket type.
     */
    public function delete(User $user, TicketType $ticketType): bool
    {
        // Can only delete if no tickets have been issued
        return $this->isOwnerOrAdmin($user, $ticketType->event) && $ticketType->tickets()->count() === 0;
    }

    protected function isOwnerOrAdmin(User $user, $event): bool
    {
        if ($event instanceof Event) {
            return $user->isAdmin() || $event->organizer_id === $user->id;
        }

        return false;
    }
}
