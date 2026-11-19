<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventStaff;
use App\Models\User;

class EventStaffPolicy
{
    /**
     * Determine whether the user can view staff list for an event.
     */
    public function viewAny(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->organizer_id === $user->id;
    }

    /**
     * Determine whether the user can assign staff to an event.
     */
    public function create(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->organizer_id === $user->id;
    }

    /**
     * Determine whether the user can remove staff from an event.
     */
    public function delete(User $user, EventStaff $eventStaff): bool
    {
        return $user->isAdmin() || $eventStaff->event->organizer_id === $user->id;
    }
}
