<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\User;

class ProgressService
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    public function markAsWatched(User $user, Lesson $lesson, bool $isCompleted = false): Progress
    {
        $progress = Progress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['is_completed' => $isCompleted, 'last_watched_at' => now()]
        );

        $this->recalculateEnrollmentProgress($user, $lesson);
        return $progress;
    }

    public function getUserProgressForCourse(User $user, string $courseId)
    {
        return Progress::query()
            ->with('lesson')
            ->where('user_id', $user->id)
            ->whereHas('lesson.section', fn ($q) => $q->where('course_id', $courseId))
            ->get();
    }

    protected function recalculateEnrollmentProgress(User $user, Lesson $lesson): void
    {
        $course = $lesson->section->course;

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return;
        }

        $totalLessons = Lesson::query()
            ->whereHas('section', fn ($q) => $q->where('course_id', $course->id))
            ->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = Progress::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereHas('lesson.section', fn ($q) => $q->where('course_id', $course->id))
            ->count();

        $percentage = (int) round(($completedLessons / $totalLessons) * 100);
        $data = ['progress' => $percentage];

        if ($percentage >= 100 && ! $enrollment->isCompleted()) {
            $data['completed_at'] = now();
        }

        if ($percentage < 100 && $enrollment->isCompleted()) {
            $data['completed_at'] = null;
        }
        $enrollment->update($data);

        if ($percentage >= 100) {
            $this->certificateService->issueForEnrollment($enrollment->fresh());
        }
    }
}