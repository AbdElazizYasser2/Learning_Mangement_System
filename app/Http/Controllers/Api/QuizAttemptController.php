<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizAttemptRequest;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\QuizAttemptResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizAttemptService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuizAttemptController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected QuizAttemptService $attemptService
    ) {}

    public function start(Request $request, Quiz $quiz): JsonResponse
    {
        try {
            $attempt = $this->attemptService->start($request->user(), $quiz);
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }

        $questions = $quiz->questions()->with('options')->orderBy('order')->get();

        return $this->created(__('messages.attempt_started'), [
            'attempt'   => new QuizAttemptResource($attempt),
            'questions' => QuestionResource::collection($questions)
        ]);
    }

    public function submit(SubmitQuizAttemptRequest $request, QuizAttempt $attempt): JsonResponse
    {
        $this->authorizeOwnership($request, $attempt);

        try {
            $attempt = $this->attemptService->submit($attempt, $request->validated('answers'));
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }

        $attempt->load('answers.question.options');
        return $this->success(__('messages.quiz_submitted'), new QuizAttemptResource($attempt));
    }

    public function show(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->authorizeOwnership($request, $attempt);
        $attempt->load(['quiz', 'answers.question.options']);
        return $this->success(__('messages.attempt_retrieved'), new QuizAttemptResource($attempt));
    }

    public function index(Request $request, Quiz $quiz): JsonResponse
    {
        $attempts = $this->attemptService->getUserAttempts($request->user(), $quiz);
        return $this->success(
            __('messages.attempts_retrieved'),
            QuizAttemptResource::collection($attempts)
        );
    }

    protected function authorizeOwnership(Request $request, QuizAttempt $attempt): void
    {
        if ($request->user()->id !== $attempt->user_id && ! $request->user()->isAdmin()) {
            abort(403, __('messages.attempt_unauthorized'));
        }
    }
}