<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\Models\Section;
use App\Services\QuizService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected QuizService $quizService
    ) {}

    public function show(Request $request, Section $section): JsonResponse
    {
        if (! $this->quizService->canAccessSection($request->user(), $section)) {
            return $this->forbidden(__('messages.quiz_section_locked'));
        }

        $quiz = $this->quizService->findBySection($section);

        if (! $quiz) {
            return $this->notFound(__('messages.quiz_not_found'));
        }

        return $this->success(__('messages.quiz_retrieved'), new QuizResource($quiz));
    }

    public function store(StoreQuizRequest $request, Section $section): JsonResponse
    {
        $this->authorize('update', $section->course);
        $quiz = $this->quizService->create($section, $request->validated());
        return $this->success(__('messages.quiz_created'), new QuizResource($quiz));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz->course);
        $quiz = $this->quizService->update($quiz, $request->validated());
        return $this->success(__('messages.quiz_updated'), new QuizResource($quiz));
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('update', $quiz->course);
        $this->quizService->delete($quiz);
        return $this->success(__('messages.quiz_deleted'));
    }
}