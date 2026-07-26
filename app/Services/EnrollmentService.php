<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EnrollmentService
{
    public function enroll(User $user, Course $course): Enrollment
    {
        return Enrollment::create([
            'user_id'     => $user->id,
            'course_id'   => $course->id,
            'enrolled_at' => now(),
            'progress'    => 0,
        ]);
    }

    public function getUserEnrollments(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Enrollment::query()
            ->with('course.category', 'course.instructor')
            ->where('user_id', $user->id)
            ->latest('enrolled_at')
            ->paginate($perPage);
    }

    public function getCourseEnrollments(Course $course, int $perPage = 10): LengthAwarePaginator
    {
        return Enrollment::query()
            ->with('user')
            ->where('course_id', $course->id)
            ->latest('enrolled_at')
            ->paginate($perPage);
    }

    public function find(User $user, string $id): Enrollment
    {
        return Enrollment::query()
            ->with('course')
            ->where('user_id', $user->id)
            ->findOrFail($id);
    }

    public function unenroll(Enrollment $enrollment): bool
    {
        return $enrollment->delete();
    }

    public function isEnrolled(User $user, Course $course): bool
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();
    }
}