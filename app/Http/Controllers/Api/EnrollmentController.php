<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $enrollments = $this->enrollmentService->getUserEnrollments(
            $request->user(),
            $request->integer('per_page', 10)
        );
        return $this->paginated(
            EnrollmentResource::collection($enrollments),
            __('messages.enrollments_retrieved')
        );
    }

    public function courseEnrollments(Request $request, Course $course): JsonResponse
    {
        $this->authorize('view', $course);
        $enrollments = $this->enrollmentService->getCourseEnrollments(
            $course,
            $request->integer('per_page', 10)
        );
        return $this->paginated(
            EnrollmentResource::collection($enrollments),
            __('messages.course_enrollments_retrieved')
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $enrollment = $this->enrollmentService->find($request->user(), $id);
        return $this->success(__('messages.enrollment_retrieved'), new EnrollmentResource($enrollment));
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $course = Course::findOrFail($request->validated('course_id'));
        $enrollment = $this->enrollmentService->enroll($request->user(), $course);
        return $this->created(__('messages.enrolled_successfully'), new EnrollmentResource($enrollment));
    }

    public function destroy(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorizeOwnership($request, $enrollment);
        $this->enrollmentService->unenroll($enrollment);
        return $this->success(__('messages.unenrolled_successfully'));
    }

    protected function authorizeOwnership(Request $request, Enrollment $enrollment): void
    {
        if ($request->user()->id !== $enrollment->user_id && ! $request->user()->isAdmin()) {
            abort(403, __('messages.enrollment_unauthorized'));
        }
    }
}