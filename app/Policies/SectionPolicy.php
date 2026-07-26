<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    /**
     * Determine whether the user can view the section.
     */
    public function view(User $user, Section $section): bool
    {
        return $user->id === $section->course->instructor_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the section.
     */
    public function update(User $user, Section $section): bool
    {
        return $user->id === $section->course->instructor_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the section.
     */
    public function delete(User $user, Section $section): bool
    {
        return $user->id === $section->course->instructor_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the section.
     */
    public function restore(User $user, Section $section): bool
    {
        return $user->id === $section->course->instructor_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the section.
     */
    public function forceDelete(User $user, Section $section): bool
    {
        return $user->isAdmin();
    }
}
