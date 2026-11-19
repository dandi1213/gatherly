<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users (admin panel).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can deactivate the user (admin only).
     */
    public function deactivate(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can view any organizers (admin panel).
     */
    public function viewOrganizers(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view any events (admin panel).
     */
    public function viewAllEvents(User $user): bool
    {
        return $user->isAdmin();
    }
}
