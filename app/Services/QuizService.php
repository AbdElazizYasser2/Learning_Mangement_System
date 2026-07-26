<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Collection;

class QuizService
{
    public function findBySection(Section $section): ?Quiz
    {
        return $section->quizzes()->published()->first();
    }

    public function find(string $id): Quiz
    {
        return Quiz::query()->with('section')->findOrFail($id);
    }

    public function create(Section $section, array $data): Quiz
    {
        $data['section_id'] = $section->id;
        return Quiz::create($data);
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        $quiz->update($data);
        return $quiz;
    }

    public function delete(Quiz $quiz): bool
    {
        return $quiz->delete();
    }

    public function canAccessSection(User $user, Section $section): bool
    {
        if ($user->id === $section->course->instructor_id || $user->isAdmin()) {
            return true;
        }

        $previousSection = Section::query()
            ->where('course_id', $section->course_id)
            ->where('order', '<', $section->order)
            ->orderByDesc('order')
            ->first();

        if (! $previousSection) {
            return true;
        }

        $previousQuiz = $previousSection->quizzes()->published()->first();

        if (! $previousQuiz) {
            return true;
        }
        return $this->hasPassedQuiz($user, $previousQuiz);
    }

    public function hasPassedQuiz(User $user, Quiz $quiz): bool
    {
        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('is_passed', true)
            ->exists();
    }

    public function attemptsUsed(User $user, Quiz $quiz): int
    {
        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();
    }
    
    public function hasAttemptsLeft(User $user, Quiz $quiz): bool
    {
        return $this->attemptsUsed($user, $quiz) < $quiz->attempts_allowed;
    }

    public function getSectionsAccessibility(User $user, Collection $sections): Collection
    {
        return $sections->map(function (Section $section) use ($user) {
            return [
                'section'      => $section,
                'is_accessible' => $this->canAccessSection($user, $section),
            ];
        });
    }
}