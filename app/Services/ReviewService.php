<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function getByCourse(Course $course, int $perPage = 10): LengthAwarePaginator
    {
        return Review::query()
            ->with('user')
            ->forCourse($course->id)
            ->latest()
            ->paginate($perPage);
    }

    public function find(User $user, string $id): Review
    {
        return Review::query()->where('user_id', $user->id)->findOrFail($id);
    }

    public function create(User $user, Course $course, array $data): Review
    {
        return Review::create([
            'user_id'   => $user->id,
            'course_id' => $course->id,
            'rating'    => $data['rating'],
            'comment'   => $data['comment'] ?? null,
        ]);
    }

    public function update(Review $review, array $data): Review
    {
        $review->update($data);
        return $review;
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    public function getCourseRatingSummary(Course $course): array
    {
        $result = Review::query()
            ->forCourse($course->id)
            ->selectRaw('AVG(rating) as average, COUNT(*) as total')
            ->first();

        return [
            'average_rating' => $result->average ? round((float) $result->average, 2) : 0,
            'reviews_count'  => (int) $result->total,
        ];
    }
}
