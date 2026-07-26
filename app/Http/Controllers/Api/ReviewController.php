<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Course;
use App\Models\Review;
use App\Services\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ReviewService $reviewService
    ) {}

    public function index(Request $request, Course $course): JsonResponse
    {
        $reviews = $this->reviewService->getByCourse($course, $request->integer('per_page', 10));
        return $this->paginated(
            ReviewResource::collection($reviews),
            __('messages.reviews_retrieved')
        );
    }

    public function summary(Course $course): JsonResponse
    {
        $summary = $this->reviewService->getCourseRatingSummary($course);
        return $this->success(__('messages.rating_summary_retrieved'), $summary);
    }

    public function store(StoreReviewRequest $request, Course $course): JsonResponse
    {
        $review = $this->reviewService->create($request->user(), $course, $request->validated());
        return $this->created(__('messages.review_created'), new ReviewResource($review));
    }

    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorizeOwnership($request, $review);
        $review = $this->reviewService->update($review, $request->validated());
        return $this->success(__('messages.review_updated'), new ReviewResource($review));
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorizeOwnership($request, $review);
        $this->reviewService->delete($review);
        return $this->success(__('messages.review_deleted'));
    }

    protected function authorizeOwnership(Request $request, Review $review): void
    {
        if ($request->user()->id !== $review->user_id && ! $request->user()->isAdmin()) {
            abort(403, __('messages.review_unauthorized'));
        }
    }
}