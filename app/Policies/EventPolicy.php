<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any events (public listing).
     */
    public function viewAny(User $user): bool
    {
        return true; // Semua user (termasuk guest) bisa lihat daftar event publik
    }

    /**
     * Determine whether the user can view the event detail.
     */
    public function view(User $user, Event $event): bool
    {
        // Published event bisa dilihat semua, draft hanya organizer/admin
        return $event->status === 'published' || $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return $user->isOrganizer() || $user->isAdmin();
    }

    /**
     * Determine whether the user can check in attendees for this event.
     * Hanya organizer pemilik dan staff yang ditugaskan (matriks 3.2).
     */
    public function checkIn(User $user, Event $event): bool
    {
        return $event->canBeCheckedInBy($user);
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        // Hanya bisa hapus jika draft dan belum pernah dipublish
        return $this->isOwnerOrAdmin($user, $event) && $event->status === 'draft';
    }

    /**
     * Determine whether the user can publish the event.
     */
    public function publish(User $user, Event $event): bool
    {
        return $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can cancel the event.
     */
    public function cancel(User $user, Event $event): bool
    {
        return $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can view attendees of the event.
     */
    public function viewAttendees(User $user, Event $event): bool
    {
        return $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can manage staff of the event.
     */
    public function manageStaff(User $user, Event $event): bool
    {
        return $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Determine whether the user can view analytics of the event.
     */
    public function viewAnalytics(User $user, Event $event): bool
    {
        return $this->isOwnerOrAdmin($user, $event);
    }

    /**
     * Helper: Check if user is owner of event or admin
     */
    protected function isOwnerOrAdmin(User $user, Event $event): bool
    {
        return $user->isAdmin() || $event->organizer_id === $user->id;
    }
}
