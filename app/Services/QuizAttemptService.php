<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizAttemptService
{
    public function __construct(
        protected QuizService $quizService
    ) {}

    public function start(User $user, Quiz $quiz): QuizAttempt
    {
        if (! $this->quizService->canAccessSection($user, $quiz->section)) {
            throw ValidationException::withMessages([
                'quiz' => 'You must pass the previous section quiz first.',
            ]);
        }

        if (! $this->quizService->hasAttemptsLeft($user, $quiz)) {
            throw ValidationException::withMessages([
                'quiz' => 'You have reached the maximum number of allowed attempts for this quiz.',
            ]);
        }

        return QuizAttempt::create([
            'user_id'    => $user->id,
            'quiz_id'    => $quiz->id,
            'started_at' => now(),
        ]);
    }

    public function submit(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        if ($attempt->isSubmitted()) {
            throw ValidationException::withMessages([
                'attempt' => 'This quiz attempt has already been submitted.',
            ]);
        }

        return DB::transaction(function () use ($attempt, $answers) {
            $quiz = $attempt->quiz()->with('questions.options')->first();
            $totalScore = 0;

            foreach ($answers as $answer) {
                $question = $quiz->questions->firstWhere('id', $answer['question_id']);

                if (! $question) {
                    continue;
                }

                $selectedOption = $question->options->firstWhere('id', $answer['question_option_id'] ?? null);
                $isCorrect = $selectedOption?->is_correct ?? false;

                if ($isCorrect) {
                    $totalScore += $question->marks;
                }

                $attempt->answers()->create([
                    'question_id'        => $question->id,
                    'question_option_id' => $selectedOption?->id,
                    'is_correct'         => $isCorrect,
                ]);
            }

            $percentage = $quiz->total_marks > 0
                ? (int) round(($totalScore / $quiz->total_marks) * 100)
                : 0;

            $attempt->update([
                'score'        => $totalScore,
                'is_passed'    => $percentage >= $quiz->passing_score,
                'submitted_at' => now(),
            ]);
            return $attempt->fresh('answers');
        });
    }


    public function getUserAttempts(User $user, Quiz $quiz)
    {
        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->latest('started_at')
            ->get();
    }
}