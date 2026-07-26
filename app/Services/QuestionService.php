<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionService
{
    public function getByQuiz(Quiz $quiz): Collection
    {
        return $quiz->questions()->with('options')->ordered()->get();
    }

    public function find(Quiz $quiz, string $id): Question
    {
        return $quiz->questions()->with('options')->findOrFail($id);
    }

    public function create(Quiz $quiz, array $data): Question
    {
        $this->validateOptions($data['options']);

        return DB::transaction(function () use ($quiz, $data) {
            $question = $quiz->questions()->create([
                'text'  => $data['text'],
                'marks' => $data['marks'],
                'order' => $data['order'] ?? $this->nextOrder($quiz),
            ]);

            foreach ($data['options'] as $index => $optionData) {
                $question->options()->create([
                    'text'       => $optionData['text'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'order'      => $index + 1,
                ]);
            }

            return $question->load('options');
        });
    }

    public function update(Question $question, array $data): Question
    {
        return DB::transaction(function () use ($question, $data) {
            $question->update([
                'text'  => $data['text'] ?? $question->text,
                'marks' => $data['marks'] ?? $question->marks,
                'order' => $data['order'] ?? $question->order,
            ]);

            if (isset($data['options'])) {
                $this->validateOptions($data['options']);

                $question->options()->delete();

                foreach ($data['options'] as $index => $optionData) {
                    $question->options()->create([
                        'text'       => $optionData['text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order'      => $index + 1,
                    ]);
                }
            }

            return $question->load('options');
        });
    }

    public function delete(Question $question): bool
    {
        return DB::transaction(function () use ($question) {
            $question->options()->delete();

            return $question->delete();
        });
    }

    protected function validateOptions(array $options): void
    {
        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => __('messages.question_min_options'),
            ]);
        }

        $correctCount = collect($options)->where('is_correct', true)->count();

        if ($correctCount !== 1) {
            throw ValidationException::withMessages([
                'options' => __('messages.question_single_correct_answer'),
            ]);
        }
    }

    protected function nextOrder(Quiz $quiz): int
    {
        return ($quiz->questions()->max('order') ?? 0) + 1;
    }
}
