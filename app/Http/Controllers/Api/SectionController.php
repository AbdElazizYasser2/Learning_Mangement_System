<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderSectionsRequest;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Course;
use App\Models\Section;
use App\Services\SectionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SectionService $sectionService
    ) {}

    public function index(Course $course): JsonResponse
    {
        $sections = $this->sectionService->getByCourse($course);
        return $this->success(__('messages.sections_retrieved'), SectionResource::collection($sections));
    }

    public function store(StoreSectionRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $section = $this->sectionService->create($course, $request->validated());
        return $this->created(__('messages.section_created'), new SectionResource($section));
    }

    public function update(UpdateSectionRequest $request, Course $course, Section $section): JsonResponse
    {
        $this->authorize('update', $course);
        $section = $this->sectionService->update($section, $request->validated());
        return $this->success(__('messages.section_updated'), new SectionResource($section));
    }

    public function reorder(ReorderSectionsRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $this->sectionService->reorder($course, $request->validated('section_ids'));
        return $this->success(__('messages.sections_reordered'));
    }

    public function destroy(Course $course, Section $section): JsonResponse
    {
        $this->authorize('update', $course);
        $this->sectionService->delete($section);
        return $this->success(__('messages.section_deleted'));
    }
}
