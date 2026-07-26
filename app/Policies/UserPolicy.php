<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        return $authUser->id === $targetUser->id || $authUser->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->id === $targetUser->id || $authUser->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        if ($targetUser->role === 'admin' && $authUser->id !== $targetUser->id) {
            return false;
        }
        
        return $authUser->id === $targetUser->id || $authUser->role === 'admin';
    }
}
