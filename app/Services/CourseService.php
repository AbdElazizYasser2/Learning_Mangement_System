<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseService
{
    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Course::query()
            ->with(['category', 'instructor'])
            ->published()
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->byCategory($categoryId))
            ->when($filters['instructor_id'] ?? null, fn ($q, $instructorId) => $q->byInstructor($instructorId))
            ->when($filters['level'] ?? null, fn ($q, $level) => $q->byLevel($level))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->latest()
            ->paginate($perPage);
    }

    public function getForInstructor(User $instructor, int $perPage = 10): LengthAwarePaginator
    {
        return Course::query()
            ->with(['category'])
            ->byInstructor($instructor->id)
            ->latest()
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): Course
    {
        return Course::query()
            ->with(['category', 'instructor'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function find(int $id): Course
    {
        return Course::query()
            ->with(['category', 'instructor'])
            ->findOrFail($id);
    }

    public function create(array $data, User $instructor): Course
    {
        return DB::transaction(function () use ($data, $instructor) {
            $data['instructor_id'] = $instructor->id;
            $data['slug'] = $this->generateUniqueSlug($data['name']);

        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $data['thumbnail'] = $data['thumbnail']->store('courses/thumbnails', 'public');
        }

        if (isset($data['preview_video']) && $data['preview_video'] instanceof UploadedFile) {
            $data['preview_video'] = $data['preview_video']->store('courses/previews', 'public');
        }

            return Course::create($data);
        });
    }

    public function update(Course $course, array $data): Course
    {
        if (isset($data['name']) && $data['name'] !== $course->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $course->id);
        }

        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $this->deleteFile($course->thumbnail);
            $data['thumbnail'] = $data['thumbnail']->store('courses/thumbnails', 'public');
        }

        if (isset($data['preview_video']) && $data['preview_video'] instanceof UploadedFile) {
            $this->deleteFile($course->preview_video);
            $data['preview_video'] = $data['preview_video']->store('courses/previews', 'public');
        }

        $course->update($data);

        return $course->fresh(['category', 'instructor']);
    }

    public function togglePublish(Course $course): Course
    {
        $course->update(['is_published' => ! $course->is_published]);
        return $course;
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }

    public function forceDelete(Course $course): bool
    {
        $this->deleteFile($course->thumbnail);
        $this->deleteFile($course->preview_video);

        return $course->forceDelete();
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            Course::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    protected function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}