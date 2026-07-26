<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Category::withCount('courses')->latest()->paginate($perPage);
    }

    public function find(string $id): Category
    {
        return Category::withCount('courses')->findOrFail($id);
    }

    public function create(array $data): Category
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        if ($category->courses()->exists()) {
            throw new \Exception(__('messages.category_has_courses'));
        }

        return $category->delete();
    }

    protected function generateUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            Category::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
