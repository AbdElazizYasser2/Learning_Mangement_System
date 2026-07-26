<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProgressRequest;
use App\Http\Resources\ProgressResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\ProgressService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProgressService $progressService
    ) {}

    public function update(UpdateProgressRequest $request, Lesson $lesson): JsonResponse
    {
        $progress = $this->progressService->markAsWatched(
            $request->user(),
            $lesson,
            $request->boolean('is_completed')
        );
        return $this->success(__('messages.progress_updated'), new ProgressResource($progress));
    }

    public function courseProgress(Request $request, Course $course): JsonResponse
    {
        $progress = $this->progressService->getUserProgressForCourse($request->user(), $course->id);
        return $this->success(
            __('messages.course_progress_retrieved'),
            ProgressResource::collection($progress)
        );
    }
}