<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionWithAnswerResource;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuestionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected QuestionService $questionService
    ) {}

    public function index(Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz->course);
        $questions = $this->questionService->getByQuiz($quiz);
        return $this->success(
            __('messages.questions_retrieved'),
            QuestionWithAnswerResource::collection($questions)
        );
    }

    public function store(StoreQuestionRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz->course);
        try {
            $question = $this->questionService->create($quiz, $request->validated());
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }
        return $this->created(__('messages.question_created'), new QuestionWithAnswerResource($question));
    }

    public function update(UpdateQuestionRequest $request, Quiz $quiz, Question $question): JsonResponse
    {
        $this->authorize('update', $quiz->course);
        try {
            $question = $this->questionService->update($question, $request->validated());
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }
        return $this->success(__('messages.question_updated'), new QuestionWithAnswerResource($question));
    }

    public function destroy(Quiz $quiz, Question $question): JsonResponse
    {
        $this->authorize('update', $quiz->course);
        $this->questionService->delete($question);
        return $this->success(__('messages.question_deleted'));
    }
}
