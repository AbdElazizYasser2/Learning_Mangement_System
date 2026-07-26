<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CourseService $courseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category_id', 'instructor_id', 'level', 'search']);
        $courses = $this->courseService->getAll($filters, $request->integer('per_page', 10));

        return $this->paginated(
            CourseResource::collection($courses),
            __('messages.courses_retrieved')
        );
    }

    public function myCourses(Request $request): JsonResponse
    {
        $courses = $this->courseService->getForInstructor($request->user(), $request->integer('per_page', 10));
        return $this->paginated(
            CourseResource::collection($courses),
            __('messages.courses_retrieved')
        );
    }

    public function show(string $slug): JsonResponse
    {
        $course = $this->courseService->findBySlug($slug);
        return $this->success(__('messages.course_retrieved'), new CourseResource($course));
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->create($request->validated(), $request->user());
        return $this->created(__('messages.course_created'), new CourseResource($course));
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $course = $this->courseService->update($course, $request->validated());
        return $this->success(__('messages.course_updated'), new CourseResource($course));
    }

    public function togglePublish(Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $course = $this->courseService->togglePublish($course);
        return $this->success(
            $course->is_published ? __('messages.course_published') : __('messages.course_unpublished'),
            new CourseResource($course)
        );
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);
        $this->courseService->delete($course);
        return $this->success(__('messages.course_deleted'));
    }
}
