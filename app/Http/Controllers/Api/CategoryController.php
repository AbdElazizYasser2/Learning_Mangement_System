<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();
        return $this->success(
            __('messages.categories_retrieved'),
            CategoryResource::collection($categories)
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());
        return $this->created(
            __('messages.category_created'),
            new CategoryResource($category)
        );
    }

    public function show(Category $category): JsonResponse
    {
        return $this->success(
            __('messages.category_retrieved'),
            new CategoryResource($category)
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->update($category, $request->validated());
        return $this->success(
            __('messages.category_updated'),
            new CategoryResource($category)
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);
        return $this->success(__('messages.category_deleted'));
    }
}