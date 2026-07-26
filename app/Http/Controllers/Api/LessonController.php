<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderLessonsRequest;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\Section;
use App\Services\LessonService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LessonService $lessonService
    ) {}

    public function index(Section $section): JsonResponse
    {
        $lessons = $this->lessonService->getBySection($section);
        return $this->success(__('messages.lessons_retrieved'), LessonResource::collection($lessons));
    }

    public function store(StoreLessonRequest $request, Section $section): JsonResponse
    {
        $this->authorize('update', $section->course);
        $lesson = $this->lessonService->create($section, $request->validated());
        return $this->created(__('messages.lesson_created'), new LessonResource($lesson));
    }

    public function update(UpdateLessonRequest $request, Section $section, Lesson $lesson): JsonResponse
    {
        $this->authorize('update', $section->course);
        $lesson = $this->lessonService->update($lesson, $request->validated());
        return $this->success(__('messages.lesson_updated'), new LessonResource($lesson));
    }

    public function reorder(ReorderLessonsRequest $request, Section $section): JsonResponse
    {
        $this->authorize('update', $section->course);
        $this->lessonService->reorder($section, $request->validated('lesson_ids'));
        return $this->success(__('messages.lessons_reordered'));
    }

    public function destroy(Section $section, Lesson $lesson): JsonResponse
    {
        $this->authorize('update', $section->course);
        $this->lessonService->delete($lesson);
        return $this->success(__('messages.lesson_deleted'));
    }
}
